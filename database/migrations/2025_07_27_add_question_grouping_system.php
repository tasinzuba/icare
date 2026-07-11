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
        // Add question_group_id to questions table
        Schema::table('questions', function (Blueprint $table) {
            if (!Schema::hasColumn('questions', 'question_group_id')) {
                $table->string('question_group_id')->nullable();
                $table->index('question_group_id');
            }
        });
        
        // Add is_group_master flag
        Schema::table('questions', function (Blueprint $table) {
            if (!Schema::hasColumn('questions', 'is_group_master')) {
                $table->boolean('is_group_master')->default(false);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn(['question_group_id', 'is_group_master']);
        });
    }
};
