<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_attempts', function (Blueprint $table) {
            if (!Schema::hasColumn('student_attempts', 'is_overtime')) $table->boolean('is_overtime')->default(false)->after('status');
            if (!Schema::hasColumn('student_attempts', 'time_taken_minutes')) $table->integer('time_taken_minutes')->nullable()->after('is_overtime');
            if (!Schema::hasColumn('student_attempts', 'allowed_minutes')) $table->integer('allowed_minutes')->nullable()->after('time_taken_minutes');
        });
    }

    public function down(): void
    {
        Schema::table('student_attempts', function (Blueprint $table) {
            $table->dropColumn(['is_overtime', 'time_taken_minutes', 'allowed_minutes']);
        });
    }
};
