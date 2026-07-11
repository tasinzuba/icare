<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration creates test assignments for existing enrollments
     * that don't have any assignments yet.
     */
    public function up(): void
    {
        // Get all enrollments with allowed_full_tests
        $enrollments = DB::table('offline_enrollments')
            ->whereNotNull('allowed_full_tests')
            ->get();

        foreach ($enrollments as $enrollment) {
            $allowedTests = json_decode($enrollment->allowed_full_tests, true);

            if (empty($allowedTests)) {
                continue;
            }

            foreach ($allowedTests as $testId) {
                // Check if assignment already exists
                $exists = DB::table('enrollment_test_assignments')
                    ->where('offline_enrollment_id', $enrollment->id)
                    ->where('full_test_id', $testId)
                    ->exists();

                if (!$exists) {
                    DB::table('enrollment_test_assignments')->insert([
                        'offline_enrollment_id' => $enrollment->id,
                        'full_test_id' => $testId,
                        'assigned_at' => $enrollment->valid_from ?? now()->toDateString(),
                        'valid_until' => $enrollment->valid_until,
                        'status' => 'available',
                        'renewal_batch' => max(1, $enrollment->renewal_count ?? 1),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Don't delete any data on rollback - it's seeded data
    }
};
