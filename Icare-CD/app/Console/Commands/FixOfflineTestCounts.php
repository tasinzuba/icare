<?php

namespace App\Console\Commands;

use App\Models\FullTestAttempt;
use App\Models\OfflineEnrollment;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixOfflineTestCounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:offline-test-counts {--dry-run : Show what would be fixed without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix offline student test counts that were incorrectly incremented per section instead of per full test';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('DRY RUN MODE - No changes will be made');
        }

        $this->info('');
        $this->info('===========================================');
        $this->info('  Fixing Offline Student Test Counts');
        $this->info('===========================================');
        $this->info('');

        // Get all offline enrollments
        $enrollments = OfflineEnrollment::with(['user', 'user.fullTestAttempts' => function($query) {
            $query->whereIn('status', ['completed', 'in_progress']);
        }])->get();

        $this->info("Found {$enrollments->count()} offline enrollments to check");
        $this->info('');

        $fixed = 0;
        $errors = 0;
        $tableData = [];

        foreach ($enrollments as $enrollment) {
            try {
                $user = $enrollment->user;
                if (!$user) {
                    continue;
                }

                // Count actual full test attempts (not sections)
                $actualFullTestCount = FullTestAttempt::where('user_id', $user->id)
                    ->whereIn('status', ['completed', 'in_progress'])
                    ->count();

                $currentCount = $enrollment->full_tests_taken;

                // Check if count is wrong
                if ($currentCount != $actualFullTestCount) {
                    $tableData[] = [
                        $enrollment->id,
                        $user->name,
                        $user->email,
                        $currentCount,
                        $actualFullTestCount,
                        $currentCount - $actualFullTestCount,
                    ];

                    if (!$dryRun) {
                        $enrollment->update(['full_tests_taken' => $actualFullTestCount]);

                        // Also update status if needed
                        if ($actualFullTestCount >= $enrollment->full_tests_allowed) {
                            $enrollment->update(['status' => OfflineEnrollment::STATUS_COMPLETED]);
                        } elseif ($enrollment->status === OfflineEnrollment::STATUS_COMPLETED && $actualFullTestCount < $enrollment->full_tests_allowed) {
                            $enrollment->update(['status' => OfflineEnrollment::STATUS_ACTIVE]);
                        }
                    }

                    $fixed++;
                }
            } catch (\Exception $e) {
                $errors++;
                $this->error("Error fixing enrollment #{$enrollment->id}: {$e->getMessage()}");
            }
        }

        $this->info('');
        $this->info('===========================================');
        $this->info('  Summary');
        $this->info('===========================================');

        if (count($tableData) > 0) {
            $this->info('');
            $this->info($dryRun ? 'Records that would be fixed:' : 'Records fixed:');
            $this->table(
                ['Enrollment ID', 'Student Name', 'Email', 'Old Count', 'Correct Count', 'Difference'],
                $tableData
            );
        }

        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Enrollments Checked', $enrollments->count()],
                [$dryRun ? 'Would Fix' : 'Fixed', $fixed],
                ['Errors', $errors],
            ]
        );

        if ($dryRun && $fixed > 0) {
            $this->info('');
            $this->warn('This was a DRY RUN. Run without --dry-run to apply fixes.');
        }

        if ($fixed === 0) {
            $this->info('');
            $this->info('All offline student test counts are correct!');
        }

        return $errors > 0 ? 1 : 0;
    }
}
