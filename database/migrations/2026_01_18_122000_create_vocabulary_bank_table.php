<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration creates the vocabulary_bank table for storing
     * student vocabulary items with definitions, examples, and progress tracking.
     */
    public function up(): void
    {
        Schema::create('vocabulary_bank', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('word', 100)->index();
            $table->json('definitions')->nullable(); // Array of definitions with parts of speech
            $table->json('examples')->nullable(); // Array of example sentences
            $table->string('phonetic', 100)->nullable(); // Phonetic transcription
            $table->string('audio_url')->nullable(); // Pronunciation audio URL
            $table->string('source')->default('practice'); // Where the word was encountered
            $table->integer('times_reviewed')->default(0);
            $table->boolean('is_mastered')->default(false);
            $table->timestamp('added_at')->useCurrent();
            $table->timestamp('last_reviewed_at')->nullable();
            $table->timestamps();

            // Composite unique index - one word per user
            $table->unique(['user_id', 'word']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vocabulary_bank');
    }
};
