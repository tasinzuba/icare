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
        Schema::table('full_tests', function (Blueprint $table) {
            $table->boolean('is_offline')->default(true)->after('is_premium');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('full_tests', function (Blueprint $table) {
            $table->dropColumn('is_offline');
        });
    }
};
