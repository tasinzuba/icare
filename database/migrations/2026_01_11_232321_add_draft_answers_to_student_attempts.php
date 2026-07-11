<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This adds a draft_answers column to store auto-saved answers.
     * This provides data safety - even if browser crashes, answers are preserved on server.
     *
     * How it works:
     * 1. Frontend auto-saves answers every 30 seconds to this column
     * 2. On page reload/resume, answers are loaded from this column
     * 3. On final submission, this column is cleared and answers go to student_answers table
     */
    public function up(): void
    {
        Schema::table('student_attempts', function (Blueprint $table) {
            $table->json('draft_answers')->nullable()->after('feedback');
            $table->timestamp('draft_saved_at')->nullable()->after('draft_answers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_attempts', function (Blueprint $table) {
            $table->dropColumn(['draft_answers', 'draft_saved_at']);
        });
    }
};
