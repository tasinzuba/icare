<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds flexible package fields for offline enrollments:
     * - evaluation_type: Controls whether student can use AI or Human evaluation
     * - allowed_full_tests: JSON array of specific full_test IDs the student can access
     */
    public function up(): void
    {
        Schema::table('offline_enrollments', function (Blueprint $table) {
            // Evaluation type: 'ai' = AI only, 'human' = Human only, 'both' = Can choose
            $table->enum('evaluation_type', ['ai', 'human', 'both'])->default('ai')->after('section_tests_taken');

            // JSON array of allowed full_test IDs (null means all offline tests are allowed)
            $table->json('allowed_full_tests')->nullable()->after('evaluation_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('offline_enrollments', function (Blueprint $table) {
            $table->dropColumn(['evaluation_type', 'allowed_full_tests']);
        });
    }
};
