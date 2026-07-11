<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->json('specialization')->nullable(); // ['writing', 'speaking', 'reading', 'listening']
            $table->decimal('rating', 3, 2)->default(0); // 0.00 to 5.00
            $table->integer('experience_years')->default(0);
            $table->json('qualifications')->nullable();
            $table->integer('evaluation_price_tokens')->default(10);
            $table->integer('total_evaluations_done')->default(0);
            $table->decimal('average_turnaround_hours', 5, 2)->default(24);
            $table->boolean('is_available')->default(true);
            $table->text('profile_description')->nullable();
            $table->json('languages')->nullable();
            $table->timestamps();
            
            $table->index('is_available');
            $table->index('rating');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};
