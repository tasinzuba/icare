<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_attempts', function (Blueprint $table) {
            $table->unsignedInteger('attempt_number')->default(1);
            $table->boolean('is_retake')->default(false);
            $table->foreignId('original_attempt_id')->nullable()
                  ->constrained('student_attempts')->nullOnDelete();
            
            // Add index for better performance
            $table->index(['user_id', 'test_set_id', 'attempt_number']);
        });
    }

    public function down(): void
    {
        Schema::table('student_attempts', function (Blueprint $table) {
            $table->dropForeign(['original_attempt_id']);
            $table->dropIndex(['user_id', 'test_set_id', 'attempt_number']);
            $table->dropColumn(['attempt_number', 'is_retake', 'original_attempt_id']);
        });
    }
};
