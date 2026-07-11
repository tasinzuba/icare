<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->text('explanation')->nullable();
            $table->string('passage_reference')->nullable();
            $table->text('common_mistakes')->nullable();
            $table->text('tips')->nullable();
            $table->string('difficulty_level')->nullable();
            $table->json('related_topics')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn([
                'explanation',
                'passage_reference', 
                'common_mistakes',
                'tips',
                'difficulty_level',
                'related_topics'
            ]);
        });
    }
};