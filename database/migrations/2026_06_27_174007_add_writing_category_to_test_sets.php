<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('test_sets', function (Blueprint $table) {
            if (!Schema::hasColumn('test_sets', 'writing_category')) {
                $table->string('writing_category')->nullable()->after('writing_task_type');
                $table->index('writing_category');
            }
        });
    }

    public function down(): void
    {
        Schema::table('test_sets', function (Blueprint $table) {
            if (Schema::hasColumn('test_sets', 'writing_category')) {
                $table->dropIndex(['writing_category']);
                $table->dropColumn('writing_category');
            }
        });
    }
};
