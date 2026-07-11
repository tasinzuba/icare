<?php

namespace App\Services;

use App\Helpers\ScoreCalculator;
use App\Http\Controllers\Traits\ResultDataTrait;
use App\Models\StudentAttempt;

/**
 * Recomputes a StudentAttempt's listening/reading band_score using the same
 * logic as the individual result page (Student\ResultController::show).
 *
 * Keeps All Results listing + Dashboard cards in sync with the value shown
 * in the red band-score box on the individual result page.
 */
class BandScoreRecalculator
{
    use ResultDataTrait;

    public function __construct(protected AnswerValidator $answerValidator) {}

    protected function getAnswerValidator(): AnswerValidator
    {
        return $this->answerValidator;
    }

    /**
     * Recalculate band_score for one attempt and persist if it differs.
     * Returns the canonical band score (or the existing value for non-objective sections).
     */
    public function recalculate(StudentAttempt $attempt): ?float
    {
        $attempt->loadMissing(['testSet.section', 'answers.question.options', 'answers.selectedOption']);

        $section = $attempt->testSet?->section?->name;
        if (!in_array($section, ['listening', 'reading'], true)) {
            return $attempt->band_score !== null ? (float) $attempt->band_score : null;
        }

        $questions = $attempt->testSet->questions()
            ->with('options')
            ->where('question_type', '!=', 'passage')
            ->orderBy('part_number')
            ->orderBy('order_number')
            ->get();

        $totalQuestions = $this->calculateTotalQuestions($questions);
        $counts = $this->calculateAnswersAndCorrections($questions, $attempt);

        $scoreData = ScoreCalculator::calculatePartialTestScore(
            $counts['correct'],
            $counts['attempted'],
            $totalQuestions,
            $section,
            $attempt->testSet->test_type ?? 'academic'
        );

        $bandScore = $scoreData['band_score'] ?? null;

        $updates = [
            'band_score' => $bandScore,
            'total_questions' => $totalQuestions,
            'answered_questions' => $counts['attempted'],
            'correct_answers' => $counts['correct'],
        ];

        $dirty = collect($updates)->contains(fn($val, $col) => (string)($attempt->{$col} ?? '') !== (string)($val ?? ''));
        if ($dirty) {
            $attempt->forceFill($updates)->saveQuietly();
        }

        return $bandScore !== null ? (float) $bandScore : null;
    }

    /**
     * Recalculate a collection of attempts (no-op for attempts whose section
     * is not listening/reading). Intended for listing/dashboard pages so the
     * displayed band score matches the individual result page.
     */
    public function recalculateMany($attempts): void
    {
        foreach ($attempts as $attempt) {
            if ($attempt instanceof StudentAttempt && $attempt->status === 'completed') {
                $this->recalculate($attempt);
            }
        }
    }
}
