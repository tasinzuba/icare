<?php

namespace App\Traits;

use App\Models\StudentAttempt;
use Carbon\Carbon;

/**
 * Shared server-side test time-limit enforcement (fixes H17 + M36/M42).
 *
 * The client countdown is advisory only — a student who suppresses/edits the JS timer, replays
 * the submit endpoint, or keeps saving work past the limit must not gain usable extra time. This
 * trait is the single source of truth for: resolving the per-attempt allowed duration, deciding
 * whether an attempt is over the deadline, and computing a non-negative elapsed time.
 *
 * Design notes:
 * - resolveAllowedMinutes() mirrors TestSet::getEffectiveTimeLimitMinutes() BUT returns null (not a
 *   hardcoded fallback) when nothing is configured, so "no limit set" => do NOT enforce.
 * - A grace window absorbs clock skew / network + upload latency so on-time work is never rejected.
 * - Listening gets a legitimate +120s post-audio review phase, so its window is extended.
 * - elapsedMinutes() uses the correct Carbon-3 receiver order plus a max(0,...) floor, so the stored
 *   time_taken_minutes can never be negative (Carbon 3.13 diffIn* is signed by default).
 */
trait EnforcesTestTimeLimit
{
    /** Grace window (seconds) added to the deadline to absorb clock skew / network latency. */
    protected int $timeLimitGraceSeconds = 30;

    /**
     * Allowed test duration in MINUTES for this attempt, or null when no limit is configured
     * (null => enforcement is skipped entirely).
     */
    protected function resolveAllowedMinutes(StudentAttempt $attempt): ?int
    {
        $testSet = $attempt->testSet;
        if (!$testSet) {
            return null;
        }

        // Per-test-set admin override wins (this also fixes the old bug where the clock honored
        // time_limit_minutes but the stored allowed_minutes read only the section default).
        if ($testSet->time_limit_minutes) {
            return (int) $testSet->time_limit_minutes;
        }

        // Writing task-type defaults.
        if ($testSet->writing_task_type === 'task1') {
            return 20;
        }
        if ($testSet->writing_task_type === 'task2') {
            return 40;
        }

        // Per-section default (nullsafe: a missing section relation resolves to "no limit").
        $sectionLimit = $testSet->section?->time_limit;

        return $sectionLimit ? (int) $sectionLimit : null;
    }

    /**
     * Section-specific legitimate extra time (seconds) beyond the main countdown.
     * Listening has a 2-minute post-audio review phase that must not be flagged as overtime.
     */
    protected function extraAllowedSeconds(string $section): int
    {
        return $section === 'listening' ? 120 : 0;
    }

    /**
     * Total allowed wall-clock window in SECONDS (main limit + section extra), or null if no limit.
     */
    protected function allowedSeconds(StudentAttempt $attempt, string $section): ?int
    {
        $minutes = $this->resolveAllowedMinutes($attempt);
        if ($minutes === null) {
            return null;
        }

        return ($minutes * 60) + $this->extraAllowedSeconds($section);
    }

    /**
     * True only when the attempt is strictly past start_time + allowed + grace.
     * Returns false (no enforcement) when no limit is configured or start_time is missing.
     */
    protected function isTimeExceeded(StudentAttempt $attempt, string $section): bool
    {
        $seconds = $this->allowedSeconds($attempt, $section);
        if ($seconds === null || !$attempt->start_time) {
            return false;
        }

        $deadline = Carbon::parse($attempt->start_time)->addSeconds($seconds + $this->timeLimitGraceSeconds);

        return now()->greaterThan($deadline);
    }

    /**
     * Non-negative elapsed minutes for the attempt (safe for the time_taken_minutes column).
     * Correct Carbon-3 receiver order (start before now => positive) plus a hard max(0,...) floor
     * against any backward clock adjustment.
     */
    protected function elapsedMinutes(StudentAttempt $attempt): int
    {
        if (!$attempt->start_time) {
            return 0;
        }

        return max(0, (int) $attempt->start_time->diffInMinutes(now()));
    }
}
