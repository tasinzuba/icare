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
            // Track previously completed tests (from past enrollments/renewals)
            $table->json('previously_completed_full_tests')->nullable()->after('allowed_full_tests');
            $table->json('previously_completed_section_tests')->nullable()->after('previously_completed_full_tests');

            // Renewal tracking
            $table->unsignedInteger('renewal_count')->default(0)->after('previously_completed_section_tests');
            $table->timestamp('last_renewed_at')->nullable()->after('renewal_count');

            // Index for faster queries
            $table->index('renewal_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('offline_enrollments', function (Blueprint $table) {
            $table->dropIndex(['renewal_count']);
            $table->dropColumn([
                'previously_completed_full_tests',
                'previously_completed_section_tests',
                'renewal_count',
                'last_renewed_at'
            ]);
        });
    }
};
