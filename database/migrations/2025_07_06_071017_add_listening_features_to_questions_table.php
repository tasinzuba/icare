<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            // Group question support
            $table->string('group_id')->nullable();
            $table->string('group_instruction')->nullable();
            $table->integer('group_start_number')->nullable();
            $table->integer('group_end_number')->nullable();
            
            // Drag-drop matching support
            $table->json('matching_options')->nullable();
            $table->json('drag_drop_config')->nullable();
            
            // Display formatting
            $table->boolean('show_question_number')->default(true);
            $table->string('display_format')->default('default');
            
            // Audio sections
            $table->string('audio_section')->nullable();
            $table->integer('audio_start_time')->nullable();
            $table->integer('audio_end_time')->nullable();
            
            // Indexes
            $table->index('group_id');
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn([
                'group_id', 'group_instruction', 'group_start_number', 
                'group_end_number', 'matching_options', 'drag_drop_config',
                'show_question_number', 'display_format', 'audio_section',
                'audio_start_time', 'audio_end_time'
            ]);
        });
    }
};