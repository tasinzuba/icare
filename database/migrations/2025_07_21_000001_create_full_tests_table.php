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
        Schema::create('full_tests', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->boolean('is_premium')->default(false);
            $table->boolean('active')->default(true);
            $table->integer('order_number')->default(0);
            $table->timestamps();
        });

        // Create pivot table for linking test sets to full tests
        Schema::create('full_test_sets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('full_test_id')->constrained()->onDelete('cascade');
            $table->foreignId('test_set_id')->constrained()->onDelete('cascade');
            $table->enum('section_type', ['listening', 'reading', 'writing', 'speaking']);
            $table->integer('order_number')->default(0);
            $table->timestamps();
            
            $table->unique(['full_test_id', 'section_type']);
            $table->index(['full_test_id', 'section_type']);
        });

        // Create table for full test attempts
        Schema::create('full_test_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('full_test_id')->constrained()->onDelete('cascade');
            $table->dateTime('start_time');
            $table->dateTime('end_time')->nullable();
            $table->enum('status', ['in_progress', 'completed', 'abandoned'])->default('in_progress');
            $table->enum('current_section', ['listening', 'reading', 'writing', 'speaking'])->nullable();
            $table->float('overall_band_score', 3, 1)->nullable();
            $table->float('listening_score', 3, 1)->nullable();
            $table->float('reading_score', 3, 1)->nullable();
            $table->float('writing_score', 3, 1)->nullable();
            $table->float('speaking_score', 3, 1)->nullable();
            $table->text('feedback')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index(['full_test_id', 'status']);
        });

        // Link individual section attempts to full test attempt
        Schema::create('full_test_section_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('full_test_attempt_id')->constrained()->onDelete('cascade');
            $table->foreignId('student_attempt_id')->constrained()->onDelete('cascade');
            $table->enum('section_type', ['listening', 'reading', 'writing', 'speaking']);
            $table->timestamps();
            
            $table->unique(['full_test_attempt_id', 'section_type'], 'full_test_section_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('full_test_section_attempts');
        Schema::dropIfExists('full_test_attempts');
        Schema::dropIfExists('full_test_sets');
        Schema::dropIfExists('full_tests');
    }
};
