<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Drop remaining unused tables (phase 2).
     * Backup: storage/db-backups/backup_pre_phase2_*.sql
     */
    public function up(): void
    {
        $tables = [
            // Subscriptions — child first
            'user_subscriptions',
            'subscription_features',
            'subscription_plans',

            // Token wallet
            'user_evaluation_tokens',

            // Referrals
            'referral_settings',
            'referrals',

            // Gamification
            'leaderboard_entries',
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
