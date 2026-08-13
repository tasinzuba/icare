<?php

namespace App\Services;

use App\Models\StudentAttempt;
use Illuminate\Support\Collection;

/**
 * Builds the per-student, per-section result cards shown on the branch, teacher
 * and admin dashboards.
 *
 * Each entry is one student together with their LATEST completed attempt per
 * section, so staff can read a student's Reading / Listening / Writing results
 * at a glance instead of scanning a flat list of attempts.
 */
class StudentSectionResultService
{
    /** Sections rendered as cards, in display order. */
    public const SECTIONS = ['reading', 'listening', 'writing'];

    /** Sections that are auto-scored and therefore have correct/total counts. */
    public const OBJECTIVE_SECTIONS = ['reading', 'listening'];

    /**
     * Most recently active students with their latest completed attempt per section.
     *
     * @param  callable|null  $scope  Optional extra constraint on the attempt query
     *                                (e.g. limit to one branch's students).
     * @param  int  $limit  How many students to return.
     * @return Collection<int, array<string, mixed>>
     */
    public function recentStudents(?callable $scope = null, int $limit = 6): Collection
    {
        // 1) Find the most recently active students (one row per student, newest first).
        $recent = StudentAttempt::query()
            ->where('status', 'completed')
            ->examOnly()
            ->whereHas('testSet.section', fn ($q) => $q->whereIn('name', self::SECTIONS));

        if ($scope) {
            $scope($recent);
        }

        $userIds = $recent
            ->selectRaw('user_id, MAX(created_at) as last_activity')
            ->groupBy('user_id')
            ->orderByDesc('last_activity')
            ->limit($limit)
            ->pluck('user_id');

        if ($userIds->isEmpty()) {
            return collect();
        }

        // 2) Pull every completed section attempt for just those students, newest first.
        $attempts = StudentAttempt::query()
            ->with(['user', 'testSet.section', 'humanEvaluationRequest.humanEvaluation'])
            ->whereIn('user_id', $userIds)
            ->where('status', 'completed')
            ->examOnly()
            ->whereHas('testSet.section', fn ($q) => $q->whereIn('name', self::SECTIONS))
            ->latest()
            ->get();

        // 3) Keep the latest attempt per (student, section). Student order comes from step 1.
        return $userIds->map(function ($userId) use ($attempts) {
            $forUser = $attempts->where('user_id', $userId);
            $newest = $forUser->first();

            if (!$newest || !$newest->user) {
                return null;
            }

            $sections = [];
            foreach (self::SECTIONS as $section) {
                // $forUser is ordered newest-first, so first() is the latest for that section.
                $sections[$section] = $forUser->first(
                    fn ($attempt) => optional(optional($attempt->testSet)->section)->name === $section
                );
            }

            return [
                'student' => $newest->user,
                'sections' => $sections,
                'last_activity' => $newest->created_at,
            ];
        })->filter()->values();
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
