<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('test_part_audios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_set_id')->constrained()->onDelete('cascade');
            $table->integer('part_number');
            $table->string('audio_path');
            $table->string('audio_duration')->nullable();
            $table->string('audio_size')->nullable();
            $table->text('transcript')->nullable();
            $table->timestamps();
            
            // Unique constraint: One audio per part per test set
            $table->unique(['test_set_id', 'part_number']);
            $table->index('part_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_part_audios');
    }
};