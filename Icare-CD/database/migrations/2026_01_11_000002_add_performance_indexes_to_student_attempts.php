<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration adds performance indexes to the student_attempts table.
     *
     * Why these indexes:
     * - (user_id, test_set_id, status): Most common query pattern for finding
     *   in_progress attempts. Used in ListeningTestController, ReadingTestController,
     *   WritingTestController, SpeakingTestController on every test start/resume.
     *
     * - (user_id, status): Used for listing user's attempts filtered by status.
     *   Common in ResultController, Dashboard queries.
     *
     * - (status, created_at): Used for admin queries filtering by status and date.
     *   Common in AdminDashboard, Reports.
     *
     * Performance Impact:
     * - Before: Full table scan on status column (no index)
     * - After: Direct index lookup, 10x-100x faster on large datasets
     */
    public function up(): void
    {
        Schema::table('student_attempts', function (Blueprint $table) {
            // Primary compound index for the most common query:
            // WHERE user_id = ? AND test_set_id = ? AND status = ?
            $table->index(['user_id', 'test_set_id', 'status'], 'idx_user_test_status');

            // Secondary index for user-status queries:
            // WHERE user_id = ? AND status = ?
            $table->index(['user_id', 'status'], 'idx_user_status');

            // Index for admin/reporting queries:
            // WHERE status = ? ORDER BY created_at
            $table->index(['status', 'created_at'], 'idx_status_created');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_attempts', function (Blueprint $table) {
            $table->dropIndex('idx_user_test_status');
            $table->dropIndex('idx_user_status');
            $table->dropIndex('idx_status_created');
        });
    }
};
