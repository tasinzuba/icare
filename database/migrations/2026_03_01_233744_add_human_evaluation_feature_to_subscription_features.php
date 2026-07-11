<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('website_settings', 'human_evaluation_enabled')) {
            Schema::table('website_settings', function (Blueprint $table) {
                $table->boolean('human_evaluation_enabled')->default(false)->after('meta_tags');
            });
        }
    }

    public function down(): void
    {
        Schema::table('website_settings', function (Blueprint $table) {
            $table->dropColumn('human_evaluation_enabled');
        });
    }
};
