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
        // First, check if the table exists
        if (!Schema::hasTable('otp_verifications')) {
            Schema::create('otp_verifications', function (Blueprint $table) {
                $table->id();
                $table->string('identifier');
                $table->string('token');
                $table->string('otp_code', 10)->nullable();
                $table->timestamp('expires_at');
                $table->timestamps();
                
                $table->index('identifier');
                $table->index('expires_at');
            });
        } else {
            // If table exists, modify it to ensure all columns are present
            Schema::table('otp_verifications', function (Blueprint $table) {
                $columns = Schema::getColumnListing('otp_verifications');
                
                if (!in_array('id', $columns)) {
                    $table->id();
                }
                
                if (!in_array('identifier', $columns)) {
                    $table->string('identifier');
                }
                
                if (!in_array('token', $columns)) {
                    $table->string('token');
                }
                
                if (!in_array('otp_code', $columns)) {
                    $table->string('otp_code', 10)->nullable();
                }
                
                if (!in_array('expires_at', $columns)) {
                    $table->timestamp('expires_at');
                }
                
                if (!in_array('created_at', $columns)) {
                    $table->timestamp('created_at')->nullable();
                }
                
                if (!in_array('updated_at', $columns)) {
                    $table->timestamp('updated_at')->nullable();
                }
            });
            
            // Add indexes - Laravel will handle duplicates gracefully
            try {
                Schema::table('otp_verifications', function (Blueprint $table) {
                    $table->index('identifier');
                    $table->index('expires_at');
                });
            } catch (\Exception $e) {
                // Indexes might already exist
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otp_verifications');
    }
};
