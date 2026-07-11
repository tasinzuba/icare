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
        Schema::create('avatar_generation_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->onDelete('cascade');
            $table->foreignId('avatar_teacher_id')->constrained()->onDelete('cascade');

            // D-ID Talk Info
            $table->string('talk_id')->unique()->index();
            $table->string('audio_url', 500);

            // Status tracking
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->text('error_message')->nullable();

            // Result data
            $table->string('result_url', 500)->nullable();
            $table->string('video_url', 500)->nullable();
            $table->string('video_path', 500)->nullable();
            $table->decimal('duration', 5, 2)->nullable();

            // Webhook tracking
            $table->timestamp('webhook_received_at')->nullable();
            $table->integer('poll_attempts')->default(0);

            $table->timestamps();

            // Index for pending tasks lookup
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('avatar_generation_tasks');
    }
};
