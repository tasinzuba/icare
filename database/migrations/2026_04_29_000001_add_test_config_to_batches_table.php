<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('batches', function (Blueprint $table) {
            $table->unsignedInteger('full_tests_allowed')->nullable()->after('description');
            $table->json('section_test_limits')->nullable()->after('full_tests_allowed');
            $table->unsignedInteger('validity_days')->nullable()->after('section_test_limits');
            $table->json('allowed_full_tests')->nullable()->after('validity_days');
            $table->json('allowed_section_tests')->nullable()->after('allowed_full_tests');
        });
    }

    public function down(): void
    {
        Schema::table('batches', function (Blueprint $table) {
            $table->dropColumn([
                'full_tests_allowed',
                'section_test_limits',
                'validity_days',
                'allowed_full_tests',
                'allowed_section_tests',
            ]);
        });
    }
};
