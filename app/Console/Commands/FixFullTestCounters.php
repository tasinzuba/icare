<?php

namespace App\Console\Commands;

use App\Models\FullTestAttempt;
use App\Models\OfflineEnrollment;
use Illuminate\Console\Command;

/**
 * Rebuild offline_enrollments.full_tests_taken from the attempts that actually exist.
 *
 * Finishing a standalone listening or reading test used to consume a FULL TEST from the student's
 * package — incrementTestCount() called incrementFullTestCount() on top of the section quota it had
 * already charged. That call is gone, but the counters it inflated are still there: enrollments
 * showing 278 full tests taken against none ever sat, and students told they had reached a limit
 * they had never approached.
 *
 * The rebuilt figure is the number of DISTINCT full tests the student has attempted. Quota is spent
 * the first time a test is started; retaking one is free, so counting attempts rather than tests
 * would charge for the retakes too. This matches how isFullTestRetake() decides what has already
 * been paid for.
 *
 * Reports and changes nothing until --apply.
 */
class FixFullTestCounters extends Command
{
    protected $signature = 'enrollments:fix-full-test-counters
                            {--apply : Actually write the corrected counters.}';

    protected $description = 'Rebuild full_tests_taken from real full test attempts';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');

        $attemptedPerUser = FullTestAttempt::select('user_id', 'full_test_id')
            ->get()
            ->groupBy('user_id')
            ->map(fn ($rows) => $rows->pluck('full_test_id')->unique()->count());

        $wrong = [];
        foreach (OfflineEnrollment::all() as $enrollment) {
            $recorded = (int) $enrollment->full_tests_taken;
            $real = (int) ($attemptedPerUser[$enrollment->user_id] ?? 0);

            if ($recorded !== $real) {
                $wrong[] = ['enrollment' => $enrollment, 'recorded' => $recorded, 'real' => $real];
            }
        }

        $this->info(sprintf('enrollments: %d   counters wrong: %d', OfflineEnrollment::count(), count($wrong)));
        if (!$apply) {
            $this->warn('Dry run — nothing changed. Re-run with --apply.');
        }

        $freed = 0;
        foreach ($wrong as $row) {
            $enrollment = $row['enrollment'];
            $allowed = (int) $enrollment->full_tests_allowed;

            $wasBlocked = $allowed > 0 && $row['recorded'] >= $allowed;
            $nowBlocked = $allowed > 0 && $row['real'] >= $allowed;
            if ($wasBlocked && !$nowBlocked) {
                $freed++;
            }

            $this->line(sprintf(
                '  enrolment %-5s user %-5s  %4d -> %-4d (allowed %s)%s',
                $enrollment->id,
                $enrollment->user_id,
                $row['recorded'],
                $row['real'],
                $allowed,
                $wasBlocked && !$nowBlocked ? '   unblocks the student' : ''
            ));

            if ($apply) {
                // Query builder rather than the model: nothing here should touch timestamps or fire
                // events, and the counter is the only column being corrected.
                OfflineEnrollment::where('id', $enrollment->id)
                    ->update(['full_tests_taken' => $row['real']]);
            }
        }

        $this->newLine();
        $this->info(sprintf(
            '%s %d   students unblocked: %d',
            $apply ? 'corrected:' : 'would correct:',
            count($wrong),
            $freed
        ));

        return self::SUCCESS;
    }
}
