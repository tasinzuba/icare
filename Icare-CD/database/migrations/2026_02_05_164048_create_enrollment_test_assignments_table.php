<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This table tracks individual test assignments with their own validity dates.
     * When a student is enrolled or renewed, each test gets its own expiry date.
     */
    public function up(): void
    {
        Schema::create('enrollment_test_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offline_enrollment_id')->constrained()->onDelete('cascade');
            $table->foreignId('full_test_id')->constrained()->onDelete('cascade');
            $table->date('assigned_at')->default(now());
            $table->date('valid_until');
            $table->enum('status', ['available', 'completed', 'expired'])->default('available');
            $table->unsignedInteger('renewal_batch')->default(1); // Track which renewal this came from
            $table->timestamps();

            // Unique constraint: one test per enrollment per batch
            $table->unique(['offline_enrollment_id', 'full_test_id', 'renewal_batch'], 'enrollment_test_batch_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollment_test_assignments');
    }
};
