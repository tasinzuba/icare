<?php
// database/migrations/2025_01_14_create_maintenance_mode_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_modes', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_active')->default(false);
            $table->string('title')->default('Maintenance Mode');
            $table->text('message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('expected_end_at')->nullable();
            $table->timestamps();
        });

        // Add maintenance notification preference to users table
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('notify_maintenance')->default(true);
            $table->timestamp('last_maintenance_notified_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_modes');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['notify_maintenance', 'last_maintenance_notified_at']);
        });
    }
};