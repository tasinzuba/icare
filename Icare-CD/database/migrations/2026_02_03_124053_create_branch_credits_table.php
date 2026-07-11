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
        // Branch Credits - Main balance table
        Schema::create('branch_credits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->decimal('balance', 10, 4)->default(0); // Credits balance (1 credit = 1 USD = 100 cents)
            $table->decimal('total_purchased', 10, 4)->default(0); // Total credits ever purchased
            $table->decimal('total_used', 10, 4)->default(0); // Total credits ever used
            $table->timestamps();

            $table->unique('branch_id');
        });

        // Credit Transactions - History of all credit activities
        Schema::create('branch_credit_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // Who triggered
            $table->enum('type', ['credit', 'debit']); // credit = add, debit = subtract
            $table->decimal('amount', 10, 4); // Amount in credits
            $table->decimal('balance_after', 10, 4); // Balance after transaction
            $table->string('reason'); // writing_evaluation, speaking_evaluation, admin_topup, etc.
            $table->string('description')->nullable(); // Human readable description
            $table->json('metadata')->nullable(); // Additional data (attempt_id, etc.)
            $table->timestamps();

            $table->index(['branch_id', 'created_at']);
            $table->index(['branch_id', 'type']);
            $table->index('reason');
        });

        // AI Evaluation Rates - Configurable rates
        Schema::create('ai_evaluation_rates', function (Blueprint $table) {
            $table->id();
            $table->string('evaluation_type')->unique(); // writing, speaking
            $table->decimal('credit_cost', 10, 4); // Cost in credits (cents converted)
            $table->decimal('bdt_equivalent', 10, 2)->nullable(); // BDT price for reference
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Insert default rates
        // Writing: 45-50 BDT = ~0.38-0.42 USD = 38-42 cents = 0.40 credits
        // Speaking: 90-100 BDT = ~0.75-0.83 USD = 75-83 cents = 0.80 credits
        DB::table('ai_evaluation_rates')->insert([
            [
                'evaluation_type' => 'writing',
                'credit_cost' => 0.40, // 40 cents = 0.40 credits
                'bdt_equivalent' => 48.00,
                'description' => 'AI Writing Evaluation (Task 1 or Task 2)',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'evaluation_type' => 'speaking',
                'credit_cost' => 0.80, // 80 cents = 0.80 credits
                'bdt_equivalent' => 96.00,
                'description' => 'AI Speaking Evaluation (includes transcription)',
                'is_active' => true,
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
        Schema::dropIfExists('ai_evaluation_rates');
        Schema::dropIfExists('branch_credit_transactions');
        Schema::dropIfExists('branch_credits');
    }
};
