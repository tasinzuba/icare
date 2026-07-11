<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Fix enrollments that were prematurely marked as 'completed' when only
     * full tests were exhausted but section tests were still remaining.
     *
     * Previously, incrementFullTestCount() would mark the enrollment as
     * 'completed' when full_tests_taken >= full_tests_allowed, regardless
     * of remaining section test quota.
     */
    public function up(): void
    {
        // Find enrollments that are 'completed' but still have validity
        // and may have remaining section tests
        $enrollments = DB::table('offline_enrollments')
            ->where('status', 'completed')
            ->where('valid_until', '>=', now()->toDateString())
            ->get();

        $reactivatedCount = 0;

        foreach ($enrollments as $enrollment) {
            $sectionTestLimits = json_decode($enrollment->section_test_limits, true) ?? [];
            $sectionTestsTaken = json_decode($enrollment->section_tests_taken_by_type, true) ?? [];

            $hasRemainingSectionTests = false;

            if (!empty($sectionTestLimits)) {
                // Per-section limits check
                foreach ($sectionTestLimits as $type => $limit) {
                    if ($limit > 0 && ($sectionTestsTaken[$type] ?? 0) < $limit) {
                        $hasRemainingSectionTests = true;
                        break;
                    }
                }
            } else {
                // Legacy total section check
                if ($enrollment->section_tests_allowed > 0 &&
                    $enrollment->section_tests_taken < $enrollment->section_tests_allowed) {
                    $hasRemainingSectionTests = true;
                }
            }

            // Also check if full tests still remain
            $hasRemainingFullTests = $enrollment->full_tests_taken < $enrollment->full_tests_allowed;

            // If either type still has remaining tests, reactivate
            if ($hasRemainingSectionTests || $hasRemainingFullTests) {
                DB::table('offline_enrollments')
                    ->where('id', $enrollment->id)
                    ->update(['status' => 'active']);
                $reactivatedCount++;
            }
        }

        if ($reactivatedCount > 0) {
            \Log::info("Fixed {$reactivatedCount} wrongly completed enrollments - reactivated to 'active'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot reliably reverse - the completed status was wrong to begin with
    }
};
