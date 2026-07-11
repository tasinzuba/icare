// database/migrations/2024_xx_xx_create_passage_explanations_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Update questions table to store marker position
        Schema::table('questions', function (Blueprint $table) {
            if (!Schema::hasColumn('questions', 'marker_text')) {
                $table->string('marker_text', 500)->nullable();
            }
            if (!Schema::hasColumn('questions', 'marker_start_pos')) {
                $table->integer('marker_start_pos')->nullable();
            }
            if (!Schema::hasColumn('questions', 'marker_end_pos')) {
                $table->integer('marker_end_pos')->nullable();
            }
        });

        // Create passage explanations table
        Schema::create('passage_explanations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->onDelete('cascade');
            $table->string('marker_id', 10); // Q1, Q2, etc.
            $table->text('explanation');
            $table->text('tips')->nullable();
            $table->text('common_mistakes')->nullable();
            $table->json('vocabulary')->nullable();
            $table->timestamps();
            
            $table->unique(['question_id', 'marker_id']);
            $table->index('marker_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('passage_explanations');
        
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn(['marker_text', 'marker_start_pos', 'marker_end_pos']);
        });
    }
};