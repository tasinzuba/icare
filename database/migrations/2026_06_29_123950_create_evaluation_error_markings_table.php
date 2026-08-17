<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Guarded because the table can already exist without this migration being recorded: a
     * database imported from another environment brings the table along but not that environment's
     * migrations rows, and the create then fails and halts everything queued behind it.
     */
    public function up(): void
    {
        if (Schema::hasTable('evaluation_error_markings')) {
            return;
        }

        Schema::create('evaluation_error_markings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('human_evaluation_id')->constrained('human_evaluations')->cascadeOnDelete();
            $table->foreignId('student_answer_id')->nullable()->constrained('student_answers')->nullOnDelete();
            $table->unsignedTinyInteger('task_number')->default(1);
            $table->text('marked_text');
            $table->unsignedInteger('start_position');
            $table->unsignedInteger('end_position');
            $table->string('error_type', 50);
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->index('human_evaluation_id');
            $table->index('student_answer_id');
            $table->index('error_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluation_error_markings');
    }
};
