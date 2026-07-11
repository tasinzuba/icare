<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('token_transactions')) return;
        // Modify the enum to include 'usage' type
        DB::statement("ALTER TABLE token_transactions MODIFY COLUMN type ENUM('purchase', 'admin_grant', 'admin_deduct', 'admin_set', 'subscription_bonus', 'referral_redemption', 'usage', 'evaluation') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE token_transactions MODIFY COLUMN type ENUM('purchase', 'admin_grant', 'admin_deduct', 'admin_set', 'subscription_bonus', 'referral_redemption') NOT NULL");
    }
};
