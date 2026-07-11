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
            $table->boolean('is_practice')->default(false)->after('is_retake');
            $table->string('practice_mode')->nullable()->after('is_practice'); // 'full', 'task1', 'task2', 'single_question'
            $table->unsignedBigInteger('practice_question_id')->nullable()->after('practice_mode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_attempts', function (Blueprint $table) {
            $table->dropColumn(['is_practice', 'practice_mode', 'practice_question_id']);
        });
    }
};
