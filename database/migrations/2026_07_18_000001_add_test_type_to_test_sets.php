<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Adds the IELTS Reading module type to test sets.
 *
 * Values: 'academic' | 'general'. NULL means "not applicable / legacy" and is
 * treated as Academic by the scorer (ScoreCalculator::calculateReadingBandScore
 * already branches on this, defaulting to academic). Only meaningful for Reading
 * test sets — mirrors how writing_task_type / writing_category are writing-only.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('test_sets', function (Blueprint $table) {
            if (!Schema::hasColumn('test_sets', 'test_type')) {
                $table->string('test_type', 20)->nullable()->after('writing_category');
            }
        });
    }

    public function down(): void
    {
        Schema::table('test_sets', function (Blueprint $table) {
            if (Schema::hasColumn('test_sets', 'test_type')) {
                $table->dropColumn('test_type');
            }
        });
    }
};
