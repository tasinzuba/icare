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
        Schema::table('student_attempts', function (Blueprint $table) {
            $table->decimal('completion_rate', 5, 2)->default(0);
            $table->string('confidence_level', 20)->default('Very Low');
            $table->boolean('is_complete_attempt')->default(false);
            $table->integer('total_questions')->default(0);
            $table->integer('answered_questions')->default(0);
            $table->integer('correct_answers')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_attempts', function (Blueprint $table) {
            $table->dropColumn([
                'completion_rate',
                'confidence_level',
                'is_complete_attempt',
                'total_questions',
                'answered_questions',
                'correct_answers'
            ]);
        });
    }
};