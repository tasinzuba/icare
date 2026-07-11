<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds is_for_offline and is_for_online fields to allow tests to be visible
     * to both student types simultaneously. Migrates data from old is_offline field.
     */
    public function up(): void
    {
        // Add new fields to test_sets
        Schema::table('test_sets', function (Blueprint $table) {
            $table->boolean('is_for_offline')->default(false)->after('is_premium');
            $table->boolean('is_for_online')->default(true)->after('is_for_offline');
        });

        // Add new fields to full_tests
        Schema::table('full_tests', function (Blueprint $table) {
            $table->boolean('is_for_offline')->default(true)->after('is_premium');
            $table->boolean('is_for_online')->default(false)->after('is_for_offline');
        });

        // Migrate existing data from is_offline to new fields for test_sets
        DB::table('test_sets')->where('is_offline', true)->update([
            'is_for_offline' => true,
            'is_for_online' => false,
        ]);
        DB::table('test_sets')->where('is_offline', false)->update([
            'is_for_offline' => false,
            'is_for_online' => true,
        ]);

        // Migrate existing data from is_offline to new fields for full_tests
        DB::table('full_tests')->where('is_offline', true)->update([
            'is_for_offline' => true,
            'is_for_online' => false,
        ]);
        DB::table('full_tests')->where('is_offline', false)->update([
            'is_for_offline' => false,
            'is_for_online' => true,
        ]);

        // Remove old is_offline columns
        Schema::table('test_sets', function (Blueprint $table) {
            $table->dropColumn('is_offline');
        });

        Schema::table('full_tests', function (Blueprint $table) {
            $table->dropColumn('is_offline');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add back is_offline columns
        Schema::table('test_sets', function (Blueprint $table) {
            $table->boolean('is_offline')->default(false)->after('is_premium');
        });

        Schema::table('full_tests', function (Blueprint $table) {
            $table->boolean('is_offline')->default(true)->after('is_premium');
        });

        // Migrate data back
        DB::table('test_sets')->where('is_for_offline', true)->update(['is_offline' => true]);
        DB::table('test_sets')->where('is_for_online', true)->update(['is_offline' => false]);
        DB::table('full_tests')->where('is_for_offline', true)->update(['is_offline' => true]);
        DB::table('full_tests')->where('is_for_online', true)->update(['is_offline' => false]);

        // Remove new columns
        Schema::table('test_sets', function (Blueprint $table) {
            $table->dropColumn(['is_for_offline', 'is_for_online']);
        });

        Schema::table('full_tests', function (Blueprint $table) {
            $table->dropColumn(['is_for_offline', 'is_for_online']);
        });
    }
};
