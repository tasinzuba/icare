<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add referral fields to users table
        Schema::table('users', function (Blueprint $table) {
            $table->string('referral_code', 10)->unique()->nullable();
            $table->foreignId('referred_by')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('referral_balance', 10, 2)->default(0);
            $table->integer('total_referrals')->default(0);
            $table->integer('successful_referrals')->default(0);
            $table->index('referral_code');
        });

        // Create referrals table
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('referred_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending');
            $table->decimal('reward_amount', 10, 2)->default(0);
            $table->string('reward_currency', 3)->default('BDT');
            $table->timestamp('completed_at')->nullable();
            $table->string('completion_condition')->nullable(); // e.g., 'first_purchase', 'first_test', etc.
            $table->timestamps();
            
            $table->index(['referrer_id', 'status']);
            $table->index(['referred_id', 'status']);
            $table->unique(['referrer_id', 'referred_id']);
        });

        // Create referral rewards table
        Schema::create('referral_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('referral_id')->constrained()->cascadeOnDelete();
            $table->enum('reward_type', ['cash', 'tokens', 'subscription_discount']);
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('BDT');
            $table->enum('status', ['pending', 'credited', 'redeemed', 'expired']);
            $table->timestamp('credited_at')->nullable();
            $table->timestamp('redeemed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->json('metadata')->nullable(); // For storing additional info
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index('expires_at');
        });

        // Create referral redemptions table
        Schema::create('referral_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('redemption_type', ['tokens', 'subscription']);
            $table->decimal('amount_spent', 10, 2);
            $table->string('currency', 3)->default('BDT');
            $table->integer('tokens_received')->nullable();
            $table->foreignId('subscription_plan_id')->nullable()->constrained();
            $table->integer('subscription_days')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'redemption_type']);
        });

        // Create referral settings table
        Schema::create('referral_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('value');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Insert default referral settings
        DB::table('referral_settings')->insert([
            [
                'key' => 'referral_reward_amount',
                'value' => '100',
                'description' => 'Amount in BDT per successful referral',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'referral_completion_condition',
                'value' => 'first_test',
                'description' => 'Condition for referral completion: first_test, first_purchase, etc.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'tokens_per_taka',
                'value' => '10',
                'description' => 'Number of tokens per 1 BDT',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'min_redemption_amount',
                'value' => '50',
                'description' => 'Minimum balance required for redemption',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referral_redemptions');
        Schema::dropIfExists('referral_rewards');
        Schema::dropIfExists('referrals');
        Schema::dropIfExists('referral_settings');
        
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['referral_code']);
            $table->dropForeign(['referred_by']);
            $table->dropColumn([
                'referral_code',
                'referred_by',
                'referral_balance',
                'total_referrals',
                'successful_referrals'
            ]);
        });
    }
};
