<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Drop tables for features no longer in use.
     * All listed tables verified empty (0 rows) at migration time.
     * Backup: storage/db-backups/*.sql before running.
     */
    public function up(): void
    {
        $tables = [
            // FK children first
            'announcement_analytics',
            'announcement_dismissals',
            'announcements_dismissed',
            'announcements',

            'coupon_redemptions',
            'coupons',

            'referral_redemptions',
            'referral_rewards',

            'user_achievements',
            'achievement_badges',

            'plan_feature',

            'payment_transactions',
            'token_packages',

            'user_goals',
            'vocabulary_bank',
            'question_reports',

            'ban_appeals',
            'maintenance_modes',
        ];

        Schema::disableForeignKeyConstraints();
        foreach ($tables as $table) {
            Schema::dropIfExists($table);
        }
        Schema::enableForeignKeyConstraints();
    }

    /**
     * No automatic rollback — restore from storage/db-backups/*.sql instead.
     */
    public function down(): void
    {
        // Intentionally empty. Use SQL backup to restore.
    }
};
