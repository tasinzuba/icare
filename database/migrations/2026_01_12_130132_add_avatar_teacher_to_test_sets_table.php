<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds default avatar teacher to test sets (for Speaking section).
     * Questions inherit this avatar teacher unless overridden.
     */
    public function up(): void
    {
        Schema::table('test_sets', function (Blueprint $table) {
            if (!Schema::hasColumn('test_sets', 'avatar_teacher_id')) {
                $table->foreignId('avatar_teacher_id')->nullable()->after('active')
                    ->constrained('avatar_teachers')->nullOnDelete()
                    ->comment('Default avatar teacher for speaking questions');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('test_sets', function (Blueprint $table) {
            $table->dropForeign(['avatar_teacher_id']);
            $table->dropColumn('avatar_teacher_id');
        });
    }
};
