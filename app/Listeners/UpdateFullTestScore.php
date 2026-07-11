<?php

namespace App\Listeners;

use App\Events\TestCompleted;
use App\Models\FullTestAttempt;
use Illuminate\Support\Facades\Log;

/**
 * Listener to update full test scores when a section is completed
 *
 * Handles:
 * - Updating section score in full test attempt
 * - Calculating overall band score when all sections complete
 */
class UpdateFullTestScore
{
    /**
     * Handle the event.
     */
    public function handle(TestCompleted $event): void
    {
        // Only process if this is part of a full test
        if (!$event->isPartOfFullTest || !$event->fullTestAttemptId) {
            return;
        }

        try {
            $fullTestAttempt = FullTestAttempt::find($event->fullTestAttemptId);

            if (!$fullTestAttempt) {
                Log::warning('Full test attempt not found', [
                    'full_test_attempt_id' => $event->fullTestAttemptId,
                ]);
                return;
            }

            // Get band score from event - this comes from ScoreCalculator
            $bandScore = $event->getBandScore();

            // Log all score data for debugging
            Log::info('UpdateFullTestScore: Processing section completion', [
                'full_test_attempt_id' => $event->fullTestAttemptId,
                'section' => $event->section,
                'event_band_score' => $bandScore,
                'event_correct_answers' => $event->getCorrectAnswers(),
                'event_total_questions' => $event->getTotalQuestions(),
                'student_attempt_id' => $event->attempt->id,
                'student_attempt_band_score' => $event->attempt->band_score,
                'student_attempt_correct_answers' => $event->attempt->correct_answers,
            ]);

            // IMPORTANT: Prefer student attempt's band_score as it's calculated by ScoreCalculator
            // The event's scoreData might be stale if there was a timing issue
            $finalBandScore = $bandScore;

            // Double-check with student attempt's band_score
            if ($event->attempt->band_score !== null && $event->attempt->band_score != $bandScore) {
                Log::warning('UpdateFullTestScore: Score mismatch detected, using StudentAttempt value', [
                    'event_score' => $bandScore,
                    'student_attempt_score' => $event->attempt->band_score,
                ]);
                $finalBandScore = $event->attempt->band_score;
            }

            // Update section score if we have a valid band score
            if (is_numeric($finalBandScore)) {
                $fullTestAttempt->updateSectionScore($event->section, (float) $finalBandScore);

                // Refresh and log the updated values
                $fullTestAttempt->refresh();

                Log::info('UpdateFullTestScore: Section score updated successfully', [
                    'full_test_attempt_id' => $event->fullTestAttemptId,
                    'section' => $event->section,
                    'final_band_score' => $finalBandScore,
                    'listening_score' => $fullTestAttempt->listening_score,
                    'reading_score' => $fullTestAttempt->reading_score,
                    'writing_score' => $fullTestAttempt->writing_score,
                    'speaking_score' => $fullTestAttempt->speaking_score,
                    'overall_band_score' => $fullTestAttempt->overall_band_score,
                ]);
            } else {
                Log::warning('UpdateFullTestScore: No valid band score to update', [
                    'full_test_attempt_id' => $event->fullTestAttemptId,
                    'section' => $event->section,
                    'band_score_received' => $bandScore,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('UpdateFullTestScore: Failed to update score', [
                'full_test_attempt_id' => $event->fullTestAttemptId,
                'section' => $event->section,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
