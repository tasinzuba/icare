<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::table('questions', function (Blueprint $table) {
        $table->integer('word_limit')->nullable();
        $table->integer('time_limit')->nullable(); // in minutes
        $table->text('instructions')->nullable();
        $table->unsignedBigInteger('passage_id')->nullable(); // Link to reading passage
        $table->json('section_specific_data')->nullable();
        
        $table->foreign('passage_id')->references('id')->on('questions')->onDelete('cascade');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            //
        });
    }
};
