<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Drop referral-related columns from users.
     * Backup: storage/db-backups/backup_pre_phase2_*.sql
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'referred_by')) {
                try {
                    $table->dropForeign(['referred_by']);
                } catch (\Throwable $e) {
                    // FK may not exist
                }
            }
        });

        Schema::table('users', function (Blueprint $table) {
            foreach (['referral_code', 'referred_by', 'referral_balance', 'total_referrals', 'successful_referrals'] as $col) {
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
