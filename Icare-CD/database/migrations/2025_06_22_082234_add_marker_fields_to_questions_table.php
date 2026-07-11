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
        Schema::table('questions', function (Blueprint $table) {
            // Add marker_id to link question with passage marker
            $table->string('marker_id')->nullable();
            // Example: Q1, Q2, Q3
            
            // Add processed explanation with clickable markers
            $table->text('processed_explanation')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn(['marker_id', 'processed_explanation']);
        });
    }
};