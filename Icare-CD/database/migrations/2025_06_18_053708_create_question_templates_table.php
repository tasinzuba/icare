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
        Schema::create('question_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('section', ['listening', 'reading', 'writing', 'speaking']);
            $table->string('question_type');
            $table->text('template_content');
            $table->text('instructions')->nullable();
            $table->json('default_options')->nullable(); // For storing default option structures
            $table->integer('default_marks')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_templates');
    }
};