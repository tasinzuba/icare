<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Mistakes students report against a question from their result page.
     *
     * The Report button has been on every result row for a while, posting to an endpoint that did
     * not exist — every report a student has ever filed went to a 404 and was silently dropped.
     */
    public function up(): void
    {
        Schema::create('question_reports', function (Blueprint $table) {
            $table->id();

            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Which sitting the student was reviewing. Nullable and set null on delete: the report
            // is still worth reading after the attempt has been cleaned up.
            $table->foreignId('student_attempt_id')->nullable()
                ->constrained()->nullOnDelete();

            $table->string('issue_type', 40);
            $table->text('description')->nullable();

            $table->string('status', 20)->default('pending');
            $table->text('admin_note')->nullable();
            $table->foreignId('reviewed_by')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();

            // The admin list is filtered by status and read newest first.
            $table->index(['status', 'created_at']);
            $table->index('question_id');

            // One open report per student per question, so a student tapping Report twice corrects
            // their earlier report rather than filling the queue with duplicates.
            $table->unique(['question_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_reports');
    }
};
