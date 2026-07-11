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
        Schema::create('subscription_features', function (Blueprint $table) {
    $table->id();
    $table->string('key')->unique(); // 'mock_tests_per_month', 'ai_evaluations'
    $table->string('name');
    $table->text('description')->nullable();
    $table->string('icon')->nullable(); // icon class or path
    $table->timestamps();
});

// Pivot table for plan features
Schema::create('plan_feature', function (Blueprint $table) {
    $table->id();
    $table->foreignId('plan_id')->constrained('subscription_plans')->onDelete('cascade');
    $table->foreignId('feature_id')->constrained('subscription_features')->onDelete('cascade');
    $table->string('value')->nullable(); // 'unlimited', '10', 'true'
    $table->integer('limit')->nullable(); // numeric limits
    $table->timestamps();
    
    $table->unique(['plan_id', 'feature_id']);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_features');
    }
};
