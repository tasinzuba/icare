<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Mark all speaking test sets as available for offline students.
     */
    public function up(): void
    {
        // section_id = 4 is the speaking section
        DB::table('test_sets')
            ->where('section_id', 4)
            ->where('active', true)
            ->update(['is_for_offline' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('test_sets')
            ->where('section_id', 4)
            ->update(['is_for_offline' => false]);
    }
};
