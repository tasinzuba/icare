<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('human_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluation_request_id')->constrained('human_evaluation_requests')->onDelete('cascade');
            $table->foreignId('evaluator_id')->constrained('users')->onDelete('cascade');
            $table->json('task_scores'); // {"task1": {"score": 7.5, "criteria": {...}}, "task2": {...}}
            $table->decimal('overall_band_score', 3, 1);
            $table->json('detailed_feedback'); // Criteria-wise feedback
            $table->json('strengths')->nullable();
            $table->json('improvements')->nullable();
            $table->timestamp('evaluated_at');
            $table->timestamps();
            
            $table->unique('evaluation_request_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('human_evaluations');
    }
};
