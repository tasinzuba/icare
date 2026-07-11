<?php

namespace App\Listeners;

use App\Events\TestCompleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

/**
 * Listener to update user statistics after test completion
 *
 * Handles:
 * - Incrementing user's test count
 * - Updating user's average scores
 * - Tracking section-specific progress
 */
class UpdateUserTestStats
{
    /**
     * Handle the event.
     */
    public function handle(TestCompleted $event): void
    {
        try {
            $user = $event->user;

            // IMPORTANT: Only increment test count for standalone section tests
            // Full test counts are incremented once in FullTestController::start()
            // when the full test begins, NOT per section completion
            if (!$event->isPartOfFullTest) {
                $user->incrementTestCount();

                Log::info('User test stats updated (standalone section test)', [
                    'user_id' => $user->id,
                    'section' => $event->section,
                    'attempt_id' => $event->attempt->id,
                    'band_score' => $event->getBandScore(),
                ]);
            } else {
                Log::info('User test stats skipped (part of full test - counted at start)', [
                    'user_id' => $user->id,
                    'section' => $event->section,
                    'attempt_id' => $event->attempt->id,
                    'full_test_attempt_id' => $event->fullTestAttemptId,
                    'band_score' => $event->getBandScore(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to update user test stats', [
                'user_id' => $event->user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
