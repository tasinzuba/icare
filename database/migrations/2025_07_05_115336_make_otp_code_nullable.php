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
        if (DB::getDriverName() === 'sqlite') return;
        // Make otp_code nullable if it isn't already
        DB::statement('ALTER TABLE otp_verifications MODIFY otp_code VARCHAR(10) NULL DEFAULT NULL');

        // Also ensure that the column can accept NULL values
        DB::statement('UPDATE otp_verifications SET otp_code = NULL WHERE otp_code = ""');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse this as making a column nullable is generally safe
    }
};
