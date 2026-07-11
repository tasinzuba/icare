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
        Schema::table('vocabulary_bank', function (Blueprint $table) {
            $table->boolean('is_bookmarked')->default(false)->after('is_mastered');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vocabulary_bank', function (Blueprint $table) {
            $table->dropColumn('is_bookmarked');
        });
    }
};
