<?php

namespace App\Services;

use App\Models\StudentAttempt;
use Illuminate\Support\Collection;

/**
 * Builds the result cards shown on the branch, teacher and admin panels.
 *
 * Results are grouped per TEST ATTEMPT, not per student:
 *   - a full test  -> one block holding a card for each of its sections
 *   - a single-section test -> one block holding that single card
 * A student who attempted several tests therefore gets a separate block per
 * attempt, newest first, so staff can see each sitting on its own.
 */
class StudentSectionResultService
{
    /** Sections rendered as cards, in display order. */
    public const SECTIONS = ['reading', 'listening', 'writing'];

    /** Sections that are auto-scored and therefore have correct/total counts. */
    public const OBJECTIVE_SECTIONS = ['reading', 'listening'];

    /**
     * Most recent test attempts, grouped into blocks.
     *
     * @param  callable|null  $scope  Optional extra constraint on the attempt query
     *                                (e.g. limit to one branch's students).
     * @param  int  $limit  How many test blocks to return.
     * @return Collection<int, array<string, mixed>>
     */
    public function recentTestGroups(?callable $scope = null, int $limit = 8): Collection
    {
        $query = StudentAttempt::query()
            ->with([
                'user',
                'testSet.section',
                'humanEvaluationRequest.humanEvaluation',
                'fullTestSectionAttempt.fullTestAttempt.fullTest.testSets',
                'fullTestSectionAttempt.fullTestAttempt.sectionAttempts.studentAttempt',
            ])
            ->where('status', 'completed')
            ->examOnly()
            ->whereHas('testSet.section', fn ($q) => $q->whereIn('name', self::SECTIONS));

        if ($scope) {
            $scope($query);
        }

        // Pull a generous window of recent attempts, then fold them into blocks. A full test
        // contributes several attempts, so the window is a multiple of the block limit.
        $attempts = $query->latest()->take(max($limit * 8, 40))->get();

        $groups = [];

        foreach ($attempts as $attempt) {
            $section = optional(optional($attempt->testSet)->section)->name;

            if (!$section || !$attempt->user) {
                continue;
            }

            $fullTestAttempt = optional($attempt->fullTestSectionAttempt)->fullTestAttempt;

            if ($fullTestAttempt) {
                // All sections of one full-test sitting belong to a single block.
                $key = 'full-' . $fullTestAttempt->id;

                if (!isset($groups[$key])) {
                    $groups[$key] = [
                        'key' => $key,
                        'kind' => 'full',
                        'kind_label' => 'Full Test',
                        'student' => $attempt->user,
                        'title' => optional($fullTestAttempt->fullTest)->title ?: 'Full Test',
                        'taken_at' => $attempt->created_at,
                        'overall' => self::effectiveOverall($fullTestAttempt),
                        'sections' => [],
                    ];
                }

                // $attempts is newest-first, so the first hit per section is the newest.
                if (!isset($groups[$key]['sections'][$section])) {
                    $groups[$key]['sections'][$section] = $attempt;
                }

                if ($attempt->created_at > $groups[$key]['taken_at']) {
                    $groups[$key]['taken_at'] = $attempt->created_at;
                }
            } else {
                // A standalone single-section attempt is its own block.
                $groups['single-' . $attempt->id] = [
                    'key' => 'single-' . $attempt->id,
                    'kind' => 'single',
                    'kind_label' => 'Single Section',
                    'student' => $attempt->user,
                    'title' => optional($attempt->testSet)->title ?: 'Test',
                    'taken_at' => $attempt->created_at,
                    'overall' => null,
                    'sections' => [$section => $attempt],
                ];
            }
        }

        return collect($groups)
            ->sortByDesc('taken_at')
            ->take($limit)
            ->values();
    }

    /**
     * The overall band for a full-test sitting, recomputed from effective section scores.
     *
     * The stored overall_band_score column cannot be trusted: markAsCompleted() only writes it when
     * every section has a stored score, AI evaluation updates the attempt's ai_band_score without
     * touching the full-test section scores, and markAsExpired() stores an average of whatever
     * partial results exist. Reading it raw therefore showed "Pending" for sittings the student's
     * own results page scores, and could present a single section's band as a whole-test overall.
     *
     * Per section the effective score is: the stored section score, else the attempt's band_score
     * (auto-scored reading/listening, or a teacher's writing band), else its ai_band_score.
     * Returns null unless EVERY section of that full test has one, so a partial sitting shows no
     * overall rather than a misleading one. Uses official IELTS .25/.75 rounding.
     */
    public static function effectiveOverall($fullTestAttempt): ?float
    {
        if (!$fullTestAttempt || !$fullTestAttempt->fullTest) {
            return null;
        }

        $available = $fullTestAttempt->fullTest->getAvailableSections();
        if (empty($available)) {
            return null;
        }

        $scores = [];

        foreach ($available as $type) {
            $score = $fullTestAttempt->{$type . '_score'} ?? null;

            if ($score === null) {
                $sectionAttempt = $fullTestAttempt->sectionAttempts->firstWhere('section_type', $type);
                $attempt = $sectionAttempt ? $sectionAttempt->studentAttempt : null;

                if ($attempt) {
                    $score = $attempt->band_score ?? $attempt->ai_band_score;
                }
            }

            if ($score === null) {
                return null; // section not scored yet — no honest overall to show
            }

            $scores[] = (float) $score;
        }

        $average = array_sum($scores) / count($scores);
        $decimal = $average - floor($average);

        if ($decimal < 0.25) {
            return (float) floor($average);
        }

        if ($decimal < 0.75) {
            return floor($average) + 0.5;
        }

        return (float) ceil($average);
    }

    /**
     * Order a block's sections for display (reading, listening, writing).
     *
     * @param  array<string, StudentAttempt>  $sections
     * @return array<string, StudentAttempt>
     */
    public static function orderSections(array $sections): array
    {
        $ordered = [];

        foreach (self::SECTIONS as $section) {
            if (isset($sections[$section])) {
                $ordered[$section] = $sections[$section];
            }
        }

        return $ordered;
    }

    /**
     * Resolve what a single section card should display.
     *
     * Reading/Listening are auto-scored, so they always have a band plus correct/total.
     * Writing is human (or AI) evaluated, so its band is null until an evaluation lands.
     *
     * @return array<string, mixed>
     */
    public static function cardData(?StudentAttempt $attempt, string $section): array
    {
        if (!$attempt) {
            return ['state' => 'not_taken'];
        }

        $isObjective = in_array($section, self::OBJECTIVE_SECTIONS, true);
        $band = $attempt->band_score;

        // Fall back to the teacher's evaluation record if the attempt band was not copied over.
        if ($band === null) {
            $band = optional(optional($attempt->humanEvaluationRequest)->humanEvaluation)->overall_band_score;
        }

        if ($band !== null) {
            return [
                'state' => 'scored',
                'band' => (float) $band,
                'correct' => $isObjective ? $attempt->correct_answers : null,
                'total' => $isObjective ? $attempt->total_questions : null,
                'attempt' => $attempt,
            ];
        }

        if ($attempt->ai_band_score !== null) {
            return [
                'state' => 'ai',
                'band' => (float) $attempt->ai_band_score,
                'attempt' => $attempt,
            ];
        }

        return ['state' => 'pending', 'attempt' => $attempt];
    }
}
