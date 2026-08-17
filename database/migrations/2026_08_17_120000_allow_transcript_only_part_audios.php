<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Let a part hold an audioscript without holding an audio file.
     *
     * Every set in the bank uploads one full-length audio on part 0, which is what students hear.
     * The script, though, is written and read per part — a student reviewing part 2 wants part 2's
     * script, not all four run together. Those per-part rows carry a transcript and no file of
     * their own, which audio_path being NOT NULL made impossible.
     */
    public function up(): void
    {
        Schema::table('test_part_audios', function (Blueprint $table) {
            $table->string('audio_path')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Rows added for their transcript alone have no path to restore, so they would block a
        // straight revert. They are dropped here rather than left to fail the column change.
        \DB::table('test_part_audios')->whereNull('audio_path')->delete();

        Schema::table('test_part_audios', function (Blueprint $table) {
            $table->string('audio_path')->nullable(false)->change();
        });
    }
};
