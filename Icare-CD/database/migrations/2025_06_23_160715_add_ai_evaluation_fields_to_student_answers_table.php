<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_answers', function (Blueprint $table) {
            $table->json('ai_evaluation')->nullable();
            $table->decimal('ai_band_score', 3, 1)->nullable();
            $table->timestamp('ai_evaluated_at')->nullable();
        });
        
        // Also add to student_attempts table if needed
        Schema::table('student_attempts', function (Blueprint $table) {
            $table->decimal('ai_band_score', 3, 1)->nullable();
            $table->timestamp('ai_evaluated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('student_answers', function (Blueprint $table) {
            $table->dropColumn(['ai_evaluation', 'ai_band_score', 'ai_evaluated_at']);
        });
        
        Schema::table('student_attempts', function (Blueprint $table) {
            $table->dropColumn(['ai_band_score', 'ai_evaluated_at']);
        });
    }
};