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
        // First, change the column to a string to avoid enum constraints
        Schema::table('users', function (Blueprint $table) {
            $table->string('subscription_status', 50)->default('free')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Convert back to enum (note: this might fail if there are values outside the enum)
        Schema::table('users', function (Blueprint $table) {
            $table->enum('subscription_status', ['free', 'premium', 'pro'])->default('free')->change();
        });
    }
};
