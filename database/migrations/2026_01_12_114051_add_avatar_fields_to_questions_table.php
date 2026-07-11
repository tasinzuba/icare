<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds avatar-related fields to questions table for AI Speaking Test feature.
     * When avatar_video_url is set, the speaking test shows the video instead of text.
     */
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            // Avatar media URLs (stored in R2 CDN)
            if (!Schema::hasColumn('questions', 'avatar_audio_url')) {
                $table->string('avatar_audio_url', 500)->nullable()->after('speaking_tips')
                    ->comment('ElevenLabs generated audio URL');
            }
            if (!Schema::hasColumn('questions', 'avatar_video_url')) {
                $table->string('avatar_video_url', 500)->nullable()->after('avatar_audio_url')
                    ->comment('D-ID generated video URL');
            }
            if (!Schema::hasColumn('questions', 'avatar_duration')) {
                $table->decimal('avatar_duration', 5, 2)->nullable()->after('avatar_video_url')
                    ->comment('Audio/Video duration in seconds');
            }

            // Generation status tracking
            if (!Schema::hasColumn('questions', 'avatar_status')) {
                $table->enum('avatar_status', ['none', 'pending', 'generating_audio', 'generating_video', 'ready', 'failed'])
                    ->default('none')->after('avatar_duration')
                    ->comment('Avatar generation status');
            }
            if (!Schema::hasColumn('questions', 'avatar_error')) {
                $table->text('avatar_error')->nullable()->after('avatar_status')
                    ->comment('Error message if generation failed');
            }

            // Teacher relationship
            if (!Schema::hasColumn('questions', 'avatar_teacher_id')) {
                $table->foreignId('avatar_teacher_id')->nullable()->after('avatar_error')
                    ->constrained('avatar_teachers')->nullOnDelete()
                    ->comment('Which teacher avatar to use');
            }

            // Timing settings
            if (!Schema::hasColumn('questions', 'pause_before_record')) {
                $table->unsignedTinyInteger('pause_before_record')->default(2)->after('avatar_teacher_id')
                    ->comment('Seconds to wait after video ends before recording starts (1-5)');
            }
        });

        // Add indexes (wrapped in try-catch in case they already exist)
        try {
            Schema::table('questions', function (Blueprint $table) {
                $table->index('avatar_status');
            });
        } catch (\Exception $e) {
            // Index already exists, ignore
        }

        try {
            Schema::table('questions', function (Blueprint $table) {
                $table->index('avatar_teacher_id');
            });
        } catch (\Exception $e) {
            // Index already exists, ignore
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropForeign(['avatar_teacher_id']);
            $table->dropColumn([
                'avatar_audio_url',
                'avatar_video_url',
                'avatar_duration',
                'avatar_status',
                'avatar_error',
                'avatar_teacher_id',
                'pause_before_record',
            ]);
        });
    }
};
