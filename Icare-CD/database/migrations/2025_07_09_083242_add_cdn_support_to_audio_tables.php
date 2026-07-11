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
        // Update test_part_audios table
        Schema::table('test_part_audios', function (Blueprint $table) {
            if (!Schema::hasColumn('test_part_audios', 'audio_url')) $table->string('audio_url')->nullable();
            if (!Schema::hasColumn('test_part_audios', 'storage_disk')) $table->string('storage_disk')->default('public');
        });

        // Update speaking_recordings table
        Schema::table('speaking_recordings', function (Blueprint $table) {
            if (!Schema::hasColumn('speaking_recordings', 'file_url')) $table->string('file_url')->nullable();
            if (!Schema::hasColumn('speaking_recordings', 'storage_disk')) $table->string('storage_disk')->default('public');
            if (!Schema::hasColumn('speaking_recordings', 'file_size')) $table->unsignedBigInteger('file_size')->nullable();
            if (!Schema::hasColumn('speaking_recordings', 'mime_type')) $table->string('mime_type')->nullable();
        });

        // Update questions table for audio if needed
        if (Schema::hasColumn('questions', 'media_path') && !Schema::hasColumn('questions', 'media_url')) {
            Schema::table('questions', function (Blueprint $table) {
                $table->string('media_url')->nullable();
                $table->string('media_storage_disk')->default('public');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('test_part_audios', function (Blueprint $table) {
            $table->dropColumn(['audio_url', 'storage_disk']);
        });

        Schema::table('speaking_recordings', function (Blueprint $table) {
            $table->dropColumn(['file_url', 'storage_disk', 'file_size', 'mime_type']);
        });

        if (Schema::hasColumn('questions', 'media_url')) {
            Schema::table('questions', function (Blueprint $table) {
                $table->dropColumn(['media_url', 'media_storage_disk']);
            });
        }
    }
};
