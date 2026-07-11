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
        Schema::create('user_subscriptions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->foreignId('plan_id')->constrained('subscription_plans');
    $table->enum('status', ['active', 'cancelled', 'expired', 'pending']);
    $table->timestamp('starts_at');
    $table->timestamp('ends_at');
    $table->timestamp('cancelled_at')->nullable();
    $table->boolean('auto_renew')->default(true);
    $table->string('payment_method')->nullable(); // stripe, bkash, nagad
    $table->string('payment_reference')->nullable();
    $table->timestamps();
    
    $table->index(['user_id', 'status']);
    $table->index('ends_at');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_subscriptions');
    }
};
