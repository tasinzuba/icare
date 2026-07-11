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
        Schema::create('payment_transactions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->foreignId('subscription_id')->nullable()->constrained('user_subscriptions');
    $table->string('transaction_id')->unique();
    $table->string('payment_method'); // stripe, bkash, nagad
    $table->decimal('amount', 10, 2);
    $table->string('currency', 3)->default('BDT');
    $table->enum('status', ['pending', 'completed', 'failed', 'refunded']);
    $table->json('gateway_response')->nullable();
    $table->text('notes')->nullable();
    $table->timestamps();
    
    $table->index(['user_id', 'status']);
    $table->index('transaction_id');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
