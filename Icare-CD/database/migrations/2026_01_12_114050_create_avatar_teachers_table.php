<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Avatar Teachers are used for AI-generated speaking test avatars.
     * Each teacher has a photo and an ElevenLabs voice for TTS.
     * D-ID uses the photo + audio to generate realistic talking videos.
     */
    public function up(): void
    {
        Schema::create('avatar_teachers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->comment('Display name: Ms. Sarah, Mr. John');
            $table->string('photo_url', 500)->comment('R2 CDN URL of teacher photo');
            $table->string('photo_path', 500)->nullable()->comment('R2 storage path');
            $table->string('elevenlabs_voice_id', 100)->comment('ElevenLabs voice ID for TTS');
            $table->string('voice_name', 100)->nullable()->comment('Friendly name: British Female');
            $table->string('d_id_source_url', 500)->nullable()->comment('D-ID processed source URL if different');
            $table->enum('gender', ['male', 'female'])->default('female');
            $table->enum('accent', ['british', 'american', 'australian', 'neutral'])->default('british');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false)->comment('Default teacher for new questions');
            $table->timestamps();

            $table->index('is_active');
            $table->index('is_default');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('avatar_teachers');
    }
};
