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
        Schema::table('human_evaluation_requests', function (Blueprint $table) {
            $table->boolean('is_offline_request')->default(false)->after('tokens_used');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('human_evaluation_requests', function (Blueprint $table) {
            $table->dropColumn('is_offline_request');
        });
    }
};
