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
        Schema::table('offline_enrollments', function (Blueprint $table) {
            // Add allowed_section_tests column after allowed_full_tests
            if (!Schema::hasColumn('offline_enrollments', 'allowed_section_tests')) {
                $table->json('allowed_section_tests')->nullable()->after('allowed_full_tests');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('offline_enrollments', function (Blueprint $table) {
            if (Schema::hasColumn('offline_enrollments', 'allowed_section_tests')) {
                $table->dropColumn('allowed_section_tests');
            }
        });
    }
};
