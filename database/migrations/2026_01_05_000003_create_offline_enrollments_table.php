<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates table for tracking offline student enrollments at branches
     */
    public function up(): void
    {
        Schema::create('offline_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');

            // Student ID format: BranchCode-Year-Serial (e.g., DHK-2025-0001)
            $table->string('student_id', 20)->unique();

            // Test limits
            $table->integer('full_tests_allowed')->default(5);
            $table->integer('full_tests_taken')->default(0);
            $table->integer('section_tests_allowed')->default(20);
            $table->integer('section_tests_taken')->default(0);

            // Payment tracking
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->decimal('due_amount', 10, 2)->default(0);
            $table->enum('payment_status', ['paid', 'partial', 'pending', 'refunded'])->default('pending');
            $table->string('payment_method')->nullable(); // cash, bank, bkash, etc.
            $table->text('payment_notes')->nullable();

            // Validity period
            $table->date('valid_from');
            $table->date('valid_until');

            // Enrollment details
            $table->foreignId('enrolled_by')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['active', 'inactive', 'expired', 'completed'])->default('active');
            $table->text('notes')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'status']);
            $table->index(['branch_id', 'status']);
            $table->index(['valid_from', 'valid_until']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offline_enrollments');
    }
};
