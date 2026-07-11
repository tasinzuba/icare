<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('test_sets', function (Blueprint $table) {
            $table->string('writing_task_type', 10)->nullable()->after('active');
            $table->unsignedSmallInteger('time_limit_minutes')->nullable()->after('writing_task_type');
        });
    }

    public function down(): void
    {
        Schema::table('test_sets', function (Blueprint $table) {
            $table->dropColumn(['writing_task_type', 'time_limit_minutes']);
        });
    }
};
