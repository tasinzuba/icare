<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add new fields to questions table
        Schema::table('questions', function (Blueprint $table) {
            // Display timing settings
            $table->integer('read_time')->default(5)->comment('Time to read question before recording');
            $table->integer('min_response_time')->default(15)->comment('Minimum speaking time');
            $table->integer('max_response_time')->default(45)->comment('Maximum speaking time');
            $table->boolean('auto_progress')->default(true)->comment('Auto move to next question');
            
            // Visual settings
            $table->string('card_theme')->default('blue')->nullable();
            $table->text('speaking_tips')->nullable();
        });

        // Create speaking test configurations table (optional but recommended)
        Schema::create('speaking_test_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_set_id')->constrained()->onDelete('cascade');
            $table->boolean('show_progress_bar')->default(true);
            $table->boolean('show_timer')->default(true);
            $table->boolean('enable_auto_recording')->default(true);
            $table->string('transition_style')->default('slide'); // slide, fade, none
            $table->timestamps();
            
            $table->unique('test_set_id');
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn([
                'read_time',
                'min_response_time', 
                'max_response_time',
                'auto_progress',
                'card_theme',
                'speaking_tips'
            ]);
        });
        
        Schema::dropIfExists('speaking_test_configs');
    }
};