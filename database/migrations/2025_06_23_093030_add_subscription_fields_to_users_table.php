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
        Schema::table('users', function (Blueprint $table) {
    $table->enum('subscription_status', ['free', 'premium', 'pro'])->default('free');
    $table->timestamp('subscription_ends_at')->nullable();
    $table->integer('tests_taken_this_month')->default(0);
    $table->integer('ai_evaluations_used')->default(0);
    $table->timestamp('last_subscription_check')->nullable();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
