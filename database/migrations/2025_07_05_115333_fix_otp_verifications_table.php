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
        Schema::table('otp_verifications', function (Blueprint $table) {
            // Add the missing otp_code column if it doesn't exist
            if (!Schema::hasColumn('otp_verifications', 'otp_code')) {
                $table->string('otp_code', 10)->nullable();
            }
            
            // Ensure all required columns exist
            if (!Schema::hasColumn('otp_verifications', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
            
            if (!Schema::hasColumn('otp_verifications', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('otp_verifications', function (Blueprint $table) {
            if (Schema::hasColumn('otp_verifications', 'otp_code')) {
                $table->dropColumn('otp_code');
            }
        });
    }
};
