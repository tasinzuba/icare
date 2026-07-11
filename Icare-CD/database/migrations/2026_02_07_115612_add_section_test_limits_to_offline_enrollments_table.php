<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add per-section test limits to offline enrollments.
     * Instead of a single "section_tests_allowed" counter, we now track
     * limits per section type (listening, reading, writing, speaking).
     *
     * The section_test_limits JSON column stores:
     * {"listening": 5, "reading": 5, "writing": 4, "speaking": 2}
     *
     * The section_tests_taken_by_type JSON column stores:
     * {"listening": 2, "reading": 1, "writing": 0, "speaking": 0}
     */
    public function up(): void
    {
        Schema::table('offline_enrollments', function (Blueprint $table) {
            // Per-section limits: {"listening": 5, "reading": 5, "writing": 4, "speaking": 2}
            if (!Schema::hasColumn('offline_enrollments', 'section_test_limits')) {
                $table->json('section_test_limits')->nullable()->after('section_tests_allowed');
            }

            // Per-section taken counts: {"listening": 2, "reading": 1, "writing": 0, "speaking": 0}
            if (!Schema::hasColumn('offline_enrollments', 'section_tests_taken_by_type')) {
                $table->json('section_tests_taken_by_type')->nullable()->after('section_tests_taken');
            }
        });

        // Migrate existing data: if section_tests_allowed > 0, distribute evenly
        // This is a best-effort migration for existing enrollments
        \App\Models\OfflineEnrollment::whereNotNull('section_tests_allowed')
            ->where('section_tests_allowed', '>', 0)
            ->whereNull('section_test_limits')
            ->each(function ($enrollment) {
                $total = $enrollment->section_tests_allowed;
                // Set equal distribution as default
                $perSection = (int) floor($total / 4);
                $remainder = $total % 4;

                $limits = [
                    'listening' => $perSection + ($remainder > 0 ? 1 : 0),
                    'reading' => $perSection + ($remainder > 1 ? 1 : 0),
                    'writing' => $perSection + ($remainder > 2 ? 1 : 0),
                    'speaking' => $perSection,
                ];

                $enrollment->update([
                    'section_test_limits' => $limits,
                    'section_tests_taken_by_type' => [
                        'listening' => 0,
                        'reading' => 0,
                        'writing' => 0,
                        'speaking' => 0,
                    ],
                ]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('offline_enrollments', function (Blueprint $table) {
            if (Schema::hasColumn('offline_enrollments', 'section_test_limits')) {
                $table->dropColumn('section_test_limits');
            }
            if (Schema::hasColumn('offline_enrollments', 'section_tests_taken_by_type')) {
                $table->dropColumn('section_tests_taken_by_type');
            }
        });
    }
};
