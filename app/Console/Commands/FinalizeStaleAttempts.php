<?php

namespace App\Console\Commands;

use App\Models\StudentAttempt;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Score attempts the student walked away from.
 *
 * A test finalises itself in two situations: the page is open when the timer runs out, or the
 * student opens that test again after the deadline. Neither happens if they close the browser and
 * never come back — the attempt then sits in_progress for good, and the work autosaved into
 * draft_answers is never scored, so the student sees no result for a test they actually sat.
 *
 * This closes those out on a schedule using exactly the path a late return would have taken: the
 * section controller's own submit(), with the draft answers, flagged as an auto-submit. Attempts
 * holding no answers are left alone unless --include-empty is passed; scoring an empty attempt
 * would invent a band 0 result for a test nobody sat.
 *
 * Reports what it would do and changes nothing until --apply is given.
 */
class FinalizeStaleAttempts extends Command
{
    protected $signature = 'attempts:finalize-stale
                            {--apply : Actually finalize. Without this the command only reports.}
                            {--include-empty : Also close attempts that hold no answers, as abandoned.}
                            {--grace=60 : Minutes past the time limit before an attempt counts as abandoned.}
                            {--limit=0 : Stop after this many attempts (0 = no limit).}';

    protected $description = 'Score in-progress attempts the student never came back to submit';

    /** Section controllers own the scoring; this maps a section to the one that does it. */
    private const CONTROLLERS = [
        'listening' => \App\Http\Controllers\Student\ListeningTestController::class,
        'reading' => \App\Http\Controllers\Student\ReadingTestController::class,
        'writing' => \App\Http\Controllers\Student\WritingTestController::class,
        'speaking' => \App\Http\Controllers\Student\SpeakingTestController::class,
    ];

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        $includeEmpty = (bool) $this->option('include-empty');
        $grace = max(0, (int) $this->option('grace'));
        $limit = max(0, (int) $this->option('limit'));

        $candidates = StudentAttempt::with(['testSet.section', 'user'])
            ->where('status', 'in_progress')
            ->get()
            ->filter(fn ($a) => $this->isPastDeadline($a, $grace));

        $withWork = $candidates->filter(fn ($a) => $this->answeredCount($a) > 0);
        $empty = $candidates->reject(fn ($a) => $this->answeredCount($a) > 0);

        $this->info(sprintf(
            'Past deadline: %d  (with answers: %d, empty: %d)',
            $candidates->count(),
            $withWork->count(),
            $empty->count()
        ));

        if (!$apply) {
            $this->warn('Dry run — nothing changed. Re-run with --apply to finalize.');
        }

        $targets = $includeEmpty ? $candidates : $withWork;
        if ($limit > 0) {
            $targets = $targets->take($limit);
        }

        $scored = 0;
        $abandoned = 0;
        $failed = 0;

        foreach ($targets as $attempt) {
            $section = $attempt->testSet?->section?->name;

            // No answers: closing it as abandoned records that it happened without inventing a score.
            if ($this->answeredCount($attempt) === 0) {
                $this->line(sprintf('  #%-6s %-10s no answers -> abandoned', $attempt->id, $section ?? '?'));
                if ($apply) {
                    $attempt->update(['status' => 'abandoned']);
                }
                $abandoned++;
                continue;
            }

            if (!isset(self::CONTROLLERS[$section])) {
                $this->line(sprintf('  #%-6s %-10s unknown section, skipped', $attempt->id, $section ?? '?'));
                $failed++;
                continue;
            }

            $this->line(sprintf(
                '  #%-6s %-10s %d answered -> score',
                $attempt->id,
                $section,
                $this->answeredCount($attempt)
            ));

            if (!$apply) {
                $scored++;
                continue;
            }

            try {
                $this->finalize($attempt, self::CONTROLLERS[$section]);
                $scored++;
            } catch (\Throwable $e) {
                $failed++;
                $this->error(sprintf('    failed: %s', $e->getMessage()));
                Log::warning('FinalizeStaleAttempts: could not finalize attempt', [
                    'attempt_id' => $attempt->id,
                    'section' => $section,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->newLine();
        $this->info(sprintf('scored: %d   abandoned: %d   failed: %d', $scored, $abandoned, $failed));

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * How many questions the student actually answered.
     *
     * Counting keys is not enough: a draft often holds an entry per question the student touched,
     * with an empty value where they typed nothing and moved on. Treating that as work produced a
     * band 0 "result" for a test where nothing was written — worse than leaving it unfinished.
     */
    private function answeredCount(StudentAttempt $attempt): int
    {
        $draft = $attempt->draft_answers;
        if (!is_array($draft)) {
            return empty($draft) ? 0 : 1;
        }

        return collect($draft)
            ->reject(function ($value) {
                if (is_string($value)) {
                    return trim($value) === '';
                }

                // Nested shapes (drag-drop zones, matching pairs) count only if something is inside.
                if (is_array($value)) {
                    return empty(array_filter($value, fn ($v) => is_string($v) ? trim($v) !== '' : !empty($v)));
                }

                return empty($value) && $value !== 0 && $value !== '0';
            })
            ->count();
    }

    /**
     * Whether the attempt is far enough past its allowed time to count as walked away from.
     * The set's own limit wins over the section default, matching what the test page enforces.
     */
    private function isPastDeadline(StudentAttempt $attempt, int $graceMinutes): bool
    {
        if (!$attempt->start_time) {
            return false;
        }

        $limit = $attempt->testSet?->time_limit_minutes
            ?? $attempt->testSet?->section?->time_limit
            ?? 40;

        return now()->greaterThan($attempt->start_time->copy()->addMinutes($limit + $graceMinutes));
    }

    /**
     * Run the section controller's own submit() with the draft answers.
     *
     * Reusing it rather than reimplementing scoring keeps this in step with the real submit path —
     * the answer rows, band score, counters and full-test bookkeeping all happen the same way. It
     * checks the attempt belongs to the current user, so the student is signed in for the call.
     */
    private function finalize(StudentAttempt $attempt, string $controllerClass): void
    {
        $user = $attempt->user;
        if (!$user) {
            throw new \RuntimeException('attempt has no user');
        }

        $request = Request::create('', 'POST', [
            'answers' => $attempt->draft_answers,
            'auto_submit' => 1,
        ]);
        $request->setUserResolver(fn () => $user);

        Auth::login($user);
        try {
            app($controllerClass)->submit($request, $attempt);
        } finally {
            Auth::logout();
        }
    }
}
