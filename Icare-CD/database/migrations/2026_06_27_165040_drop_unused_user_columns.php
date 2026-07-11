<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Drop user columns tied to removed features.
     * Backup: storage/db-backups/backup_pre_cleanup_*.sql
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $cols = [
                'subscription_status',
                'subscription_ends_at',
                'last_subscription_check',
                'achievement_points',
                'study_streak_days',
                'last_study_date',
                'show_on_leaderboard',
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }

    /**
     * No automatic rollback — restore from SQL backup.
     */
    public function down(): void
    {
        // Intentionally empty. Use SQL backup to restore.
    }
};
