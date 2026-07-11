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
     * This migration adds a database-level constraint to prevent multiple
     * in_progress attempts for the same user and test_set combination.
     *
     * How it works:
     * - A generated column 'active_attempt_key' is created
     * - When status = 'in_progress', it stores 'user_id-test_set_id'
     * - When status != 'in_progress', it stores NULL
     * - A UNIQUE index on this column allows only one in_progress attempt
     *   (NULL values don't violate unique constraints in MySQL)
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') return;
        // First, clean up any existing duplicate in_progress attempts
        // Keep only the latest one for each user-test combination
        $this->cleanupDuplicateInProgressAttempts();

        Schema::table('student_attempts', function (Blueprint $table) {
            // Add generated column for unique constraint
            // MySQL 5.7+ supports generated columns
            $table->string('active_attempt_key', 50)->nullable()->after('status');
        });

        // Update existing in_progress records to set the key
        DB::statement("
            UPDATE student_attempts
            SET active_attempt_key = CONCAT(user_id, '-', test_set_id)
            WHERE status = 'in_progress'
        ");

        // Add unique index on the generated column
        Schema::table('student_attempts', function (Blueprint $table) {
            $table->unique('active_attempt_key', 'unique_active_attempt');
        });

        // Create trigger to auto-update the key on INSERT
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

        // Create trigger to auto-update the key on UPDATE
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop triggers first
        DB::unprepared("DROP TRIGGER IF EXISTS student_attempts_set_active_key_insert");
        DB::unprepared("DROP TRIGGER IF EXISTS student_attempts_set_active_key_update");

        Schema::table('student_attempts', function (Blueprint $table) {
            $table->dropUnique('unique_active_attempt');
            $table->dropColumn('active_attempt_key');
        });
    }

    /**
     * Clean up duplicate in_progress attempts before adding constraint.
     * Keeps only the most recent attempt for each user-test combination.
     */
    private function cleanupDuplicateInProgressAttempts(): void
    {
        // Find duplicates and mark older ones as abandoned
        $duplicates = DB::select("
            SELECT sa1.id
            FROM student_attempts sa1
            INNER JOIN (
                SELECT user_id, test_set_id, MAX(created_at) as max_created
                FROM student_attempts
                WHERE status = 'in_progress'
                GROUP BY user_id, test_set_id
                HAVING COUNT(*) > 1
            ) sa2 ON sa1.user_id = sa2.user_id
                AND sa1.test_set_id = sa2.test_set_id
                AND sa1.created_at < sa2.max_created
            WHERE sa1.status = 'in_progress'
        ");

        if (!empty($duplicates)) {
            $ids = array_column($duplicates, 'id');
            DB::table('student_attempts')
                ->whereIn('id', $ids)
                ->update([
                    'status' => 'abandoned',
                    'updated_at' => now()
                ]);

            \Log::info('Cleaned up duplicate in_progress attempts', ['count' => count($ids), 'ids' => $ids]);
        }
    }
};
