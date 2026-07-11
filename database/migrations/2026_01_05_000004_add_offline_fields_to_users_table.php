<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds student_type and branch_id fields to users table for offline student support
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Student type: public (online) or offline (branch-based)
            $table->enum('student_type', ['public', 'offline'])->default('public')->after('email');

            // Branch association for offline students (nullable for public students)
            $table->foreignId('branch_id')->nullable()->after('student_type')->constrained()->nullOnDelete();

            // Index for filtering
            $table->index(['student_type', 'branch_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropIndex(['student_type', 'branch_id']);
            $table->dropColumn(['student_type', 'branch_id']);
        });
    }
};
