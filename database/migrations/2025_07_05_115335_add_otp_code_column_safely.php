<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if the column exists using a try-catch approach
        try {
            // Attempt to select the column
            DB::select("SELECT otp_code FROM otp_verifications LIMIT 1");
            // If no exception, column exists
        } catch (\Exception $e) {
            // Column doesn't exist, add it
            try {
                DB::statement('ALTER TABLE otp_verifications ADD otp_code VARCHAR(10) NULL');
            } catch (\Exception $addException) {
                // If adding fails, it might already exist or there's another issue
                // Log the error for debugging
                \Log::error('Failed to add otp_code column: ' . $addException->getMessage());
            }
        }
        
        // Ensure all other required columns exist
        $columns = Schema::getColumnListing('otp_verifications');
        
        if (!in_array('identifier', $columns)) {
            DB::statement('ALTER TABLE otp_verifications ADD identifier VARCHAR(255)');
        }
        
        if (!in_array('token', $columns)) {
            DB::statement('ALTER TABLE otp_verifications ADD token VARCHAR(255)');
        }
        
        if (!in_array('expires_at', $columns)) {
            DB::statement('ALTER TABLE otp_verifications ADD expires_at TIMESTAMP NULL');
        }
        
        if (!in_array('created_at', $columns)) {
            DB::statement('ALTER TABLE otp_verifications ADD created_at TIMESTAMP NULL');
        }
        
        if (!in_array('updated_at', $columns)) {
            DB::statement('ALTER TABLE otp_verifications ADD updated_at TIMESTAMP NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove only the otp_code column if it exists
        try {
            DB::statement('ALTER TABLE otp_verifications DROP COLUMN otp_code');
        } catch (\Exception $e) {
            // Column might not exist, which is fine
        }
    }
};
