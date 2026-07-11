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
        Schema::create('subscription_plans', function (Blueprint $table) {
    $table->id();
    $table->string('name'); // Free, Premium, Pro
    $table->string('slug')->unique(); // free, premium, pro
    $table->decimal('price', 10, 2)->default(0);
    $table->decimal('discount_price', 10, 2)->nullable();
    $table->integer('duration_days')->default(30);
    $table->text('description')->nullable();
    $table->json('features'); // ['unlimited_tests', 'ai_evaluation', etc]
    $table->integer('sort_order')->default(0);
    $table->boolean('is_active')->default(true);
    $table->boolean('is_featured')->default(false);
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
