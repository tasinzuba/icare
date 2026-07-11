<?php

namespace App\Console\Commands;

use App\Models\FullTestAttempt;
use App\Models\FullTestSectionAttempt;
use App\Models\OfflineEnrollment;
use App\Models\StudentAttempt;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixSectionQuotaFromFullTests extends Command
{
    protected $signature = 'fix:section-quota-from-full-tests
                            {--dry-run : Show what would be fixed without making changes}
                            {--enrollment= : Fix a specific enrollment ID only}';

    protected $description = 'Fix section test quotas that were wrongly consumed when taking sections within full tests. Recalculates section_tests_taken and section_tests_taken_by_type to only count standalone section tests.';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $specificEnrollment = $this->option('enrollment');

        $this->info('');
        $this->info('=====================================================');
        $this->info('  Fix Section Quota Wrongly Consumed in Full Tests');
        $this->info('=====================================================');
        $this->info('');

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
            $this->info('');
        }

        // Get enrollments to check
        $query = OfflineEnrollment::with('user');
        if ($specificEnrollment) {
            $query->where('id', $specificEnrollment);
        }
        $enrollments = $query->get();

        $this->info("Checking {$enrollments->count()} enrollment(s)...");
        $this->info('');

        $fixed = 0;
        $skipped = 0;
        $errors = 0;
        $tableData = [];

        foreach ($enrollments as $enrollment) {
            try {
                $user = $enrollment->user;
                if (!$user) {
                    $skipped++;
                    continue;
                }

                // =====================================================
                // Step 1: Find all StudentAttempt IDs that are part of
                //         a Full Test (linked via FullTestSectionAttempt)
                // =====================================================
                $fullTestStudentAttemptIds = FullTestSectionAttempt::whereHas('fullTestAttempt', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                })->pluck('student_attempt_id')->toArray();

                // =====================================================
                // Step 2: Count ONLY standalone completed section tests
                //         (NOT part of any full test)
                // =====================================================
                $standaloneSectionAttempts = StudentAttempt::where('user_id', $user->id)
                    ->where('status', 'completed')
                    ->whereNotIn('id', $fullTestStudentAttemptIds)
                    ->whereHas('testSet.section') // Must have a section
                    ->with('testSet.section')
                    ->get();

                // Count by section type
                $correctCountsByType = [
                    'listening' => 0,
                    'reading' => 0,
                    'writing' => 0,
                    'speaking' => 0,
                ];

                foreach ($standaloneSectionAttempts as $attempt) {
                    $sectionName = strtolower($attempt->testSet->section->name ?? '');
                    if (isset($correctCountsByType[$sectionName])) {
                        $correctCountsByType[$sectionName]++;
                    }
                }

                $correctTotal = array_sum($correctCountsByType);

                // =====================================================
                // Step 3: Compare with current stored values
                // =====================================================
                $currentTotal = $enrollment->section_tests_taken ?? 0;
                $currentByType = $enrollment->section_tests_taken_by_type ?? [
                    'listening' => 0, 'reading' => 0, 'writing' => 0, 'speaking' => 0,
                ];

                // Check if anything is wrong
                $totalMismatch = $currentTotal != $correctTotal;
                $typeMismatch = false;
                foreach (['listening', 'reading', 'writing', 'speaking'] as $type) {
                    if (($currentByType[$type] ?? 0) != $correctCountsByType[$type]) {
                        $typeMismatch = true;
                        break;
                    }
                }

                if (!$totalMismatch && !$typeMismatch) {
                    $skipped++;
                    continue;
                }

                // =====================================================
                // Step 4: Build report row
                // =====================================================
                $overcharged = $currentTotal - $correctTotal;

                $detailParts = [];
                foreach (['listening', 'reading', 'writing', 'speaking'] as $type) {
                    $old = $currentByType[$type] ?? 0;
                    $new = $correctCountsByType[$type];
                    if ($old != $new) {
                        $diff = $old - $new;
                        $detailParts[] = "{$type}: {$old}→{$new} (-{$diff})";
                    }
                }

                $tableData[] = [
                    $enrollment->id,
                    $enrollment->student_id,
                    $user->name,
                    $currentTotal,
                    $correctTotal,
                    $overcharged > 0 ? "+{$overcharged} extra" : "{$overcharged}",
                    implode(', ', $detailParts) ?: 'total only',
                ];

                // =====================================================
                // Step 5: Apply fix
                // =====================================================
                if (!$dryRun) {
                    $enrollment->update([
                        'section_tests_taken' => $correctTotal,
                        'section_tests_taken_by_type' => $correctCountsByType,
                    ]);

                    // If enrollment was wrongly marked completed due to inflated counts,
                    // check if it should be reactivated
                    if ($enrollment->status === OfflineEnrollment::STATUS_COMPLETED) {
                        if (!$enrollment->fresh()->isAllTestsExhausted() && !$enrollment->isExpired()) {
                            $enrollment->update(['status' => OfflineEnrollment::STATUS_ACTIVE]);
                            $this->info("  → Reactivated enrollment #{$enrollment->id} (was wrongly completed)");
                        }
                    }
                }

                $fixed++;

            } catch (\Exception $e) {
                $errors++;
                $this->error("Error on enrollment #{$enrollment->id}: {$e->getMessage()}");
            }
        }

        // =====================================================
        // Summary
        // =====================================================
        $this->info('');
        $this->info('=====================================================');
        $this->info('  Summary');
        $this->info('=====================================================');

        if (count($tableData) > 0) {
            $this->info('');
            $this->info($dryRun ? 'Enrollments that NEED fixing:' : 'Enrollments FIXED:');
            $this->table(
                ['Enroll ID', 'Student ID', 'Name', 'Old Total', 'Correct Total', 'Overcharged', 'Per-Type Changes'],
                $tableData
            );
        }

        $this->info('');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Enrollments Checked', $enrollments->count()],
                [$dryRun ? 'Need Fix' : 'Fixed', $fixed],
                ['Already Correct', $skipped],
                ['Errors', $errors],
            ]
        );

        if ($dryRun && $fixed > 0) {
            $this->info('');
            $this->warn('This was a DRY RUN. To apply fixes, run:');
            $this->warn('  php artisan fix:section-quota-from-full-tests');
            if ($specificEnrollment) {
                $this->warn("  php artisan fix:section-quota-from-full-tests --enrollment={$specificEnrollment}");
            }
        }

        if ($fixed === 0) {
            $this->info('');
            $this->info('All section quotas are already correct!');
        }

        return $errors > 0 ? 1 : 0;
    }
}
