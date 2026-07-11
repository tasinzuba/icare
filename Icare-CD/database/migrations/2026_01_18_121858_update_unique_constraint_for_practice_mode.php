<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration updates the unique constraint triggers to handle practice mode.
     *
     * For practice attempts, the key includes the practice_question_id, allowing:
     * - Multiple practice attempts for different questions in the same test_set
     * - Only one active practice attempt per specific question per user
     *
     * For regular tests:
     * - Only one active attempt per test_set per user (existing behavior)
     */
    public function up(): void
    {
        // Drop existing triggers
        DB::unprepared("DROP TRIGGER IF EXISTS student_attempts_set_active_key_insert");
        DB::unprepared("DROP TRIGGER IF EXISTS student_attempts_set_active_key_update");

        // Recreate INSERT trigger with practice mode support
        DB::unprepared("
            CREATE TRIGGER student_attempts_set_active_key_insert
            BEFORE INSERT ON student_attempts
            FOR EACH ROW
            BEGIN
                IF NEW.status = 'in_progress' THEN
                    IF NEW.is_practice = 1 AND NEW.practice_question_id IS NOT NULL THEN
                        -- For practice mode: include practice_question_id in key
                        SET NEW.active_attempt_key = CONCAT(NEW.user_id, '-', NEW.test_set_id, '-P-', NEW.practice_question_id);
                    ELSE
                        -- For regular tests: use user_id and test_set_id only
                        SET NEW.active_attempt_key = CONCAT(NEW.user_id, '-', NEW.test_set_id);
                    END IF;
                ELSE
                    SET NEW.active_attempt_key = NULL;
                END IF;
            END
        ");

        // Recreate UPDATE trigger with practice mode support
        DB::unprepared("
            CREATE TRIGGER student_attempts_set_active_key_update
            BEFORE UPDATE ON student_attempts
            FOR EACH ROW
            BEGIN
                IF NEW.status = 'in_progress' THEN
                    IF NEW.is_practice = 1 AND NEW.practice_question_id IS NOT NULL THEN
                        -- For practice mode: include practice_question_id in key
                        SET NEW.active_attempt_key = CONCAT(NEW.user_id, '-', NEW.test_set_id, '-P-', NEW.practice_question_id);
                    ELSE
                        -- For regular tests: use user_id and test_set_id only
                        SET NEW.active_attempt_key = CONCAT(NEW.user_id, '-', NEW.test_set_id);
                    END IF;
                ELSE
                    SET NEW.active_attempt_key = NULL;
                END IF;
            END
        ");

        // Update existing in_progress practice attempts to have correct keys
        DB::statement("
            UPDATE student_attempts
            SET active_attempt_key = CONCAT(user_id, '-', test_set_id, '-P-', practice_question_id)
            WHERE status = 'in_progress'
              AND is_practice = 1
              AND practice_question_id IS NOT NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop updated triggers
        DB::unprepared("DROP TRIGGER IF EXISTS student_attempts_set_active_key_insert");
        DB::unprepared("DROP TRIGGER IF EXISTS student_attempts_set_active_key_update");

        // Restore original triggers (without practice mode support)
        DB::unprepared("
            CREATE TRIGGER student_attempts_set_active_key_insert
            BEFORE INSERT ON student_attempts
            FOR EACH ROW
            BEGIN
                IF NEW.status = 'in_progress' THEN
                    SET NEW.active_attempt_key = CONCAT(NEW.user_id, '-', NEW.test_set_id);
                ELSE
                    SET NEW.active_attempt_key = NULL;
                END IF;
            END
        ");

        DB::unprepared("
            CREATE TRIGGER student_attempts_set_active_key_update
            BEFORE UPDATE ON student_attempts
            FOR EACH ROW
            BEGIN
                IF NEW.status = 'in_progress' THEN
                    SET NEW.active_attempt_key = CONCAT(NEW.user_id, '-', NEW.test_set_id);
                ELSE
                    SET NEW.active_attempt_key = NULL;
                END IF;
            END
        ");

        // Restore existing in_progress attempts to original key format
        DB::statement("
            UPDATE student_attempts
            SET active_attempt_key = CONCAT(user_id, '-', test_set_id)
            WHERE status = 'in_progress'
        ");
    }
};
