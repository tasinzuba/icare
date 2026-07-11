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
     * in_progress attempts for the same user and full_test combination.
     *
     * How it works:
     * - A column 'active_attempt_key' is created
     * - When status = 'in_progress', it stores 'user_id-full_test_id'
     * - When status != 'in_progress', it stores NULL
     * - A UNIQUE index on this column allows only one in_progress attempt
     *   (NULL values don't violate unique constraints in MySQL)
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') return;
        // First, clean up any existing duplicate in_progress attempts
        $this->cleanupDuplicateInProgressAttempts();

        Schema::table('full_test_attempts', function (Blueprint $table) {
            // Add column for unique constraint
            $table->string('active_attempt_key', 50)->nullable()->after('status');
        });

        // Update existing in_progress records to set the key
        DB::statement("
            UPDATE full_test_attempts
            SET active_attempt_key = CONCAT(user_id, '-', full_test_id)
            WHERE status = 'in_progress'
        ");

        // Add unique index on the column
        Schema::table('full_test_attempts', function (Blueprint $table) {
            $table->unique('active_attempt_key', 'unique_active_full_test_attempt');
        });

        // Create trigger to auto-update the key on INSERT
        DB::unprepared("
            CREATE TRIGGER full_test_attempts_set_active_key_insert
            BEFORE INSERT ON full_test_attempts
            FOR EACH ROW
            BEGIN
                IF NEW.status = 'in_progress' THEN
                    SET NEW.active_attempt_key = CONCAT(NEW.user_id, '-', NEW.full_test_id);
                ELSE
                    SET NEW.active_attempt_key = NULL;
                END IF;
            END
        ");

        // Create trigger to auto-update the key on UPDATE
        DB::unprepared("
            CREATE TRIGGER full_test_attempts_set_active_key_update
            BEFORE UPDATE ON full_test_attempts
            FOR EACH ROW
            BEGIN
                IF NEW.status = 'in_progress' THEN
                    SET NEW.active_attempt_key = CONCAT(NEW.user_id, '-', NEW.full_test_id);
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
        DB::unprepared("DROP TRIGGER IF EXISTS full_test_attempts_set_active_key_insert");
        DB::unprepared("DROP TRIGGER IF EXISTS full_test_attempts_set_active_key_update");

        Schema::table('full_test_attempts', function (Blueprint $table) {
            $table->dropUnique('unique_active_full_test_attempt');
            $table->dropColumn('active_attempt_key');
        });
    }

    /**
     * Clean up duplicate in_progress attempts before adding constraint.
     * Keeps only the most recent attempt for each user-full_test combination.
     */
    private function cleanupDuplicateInProgressAttempts(): void
    {
        // Find duplicates and mark older ones as abandoned
        $duplicates = DB::select("
            SELECT fta1.id
            FROM full_test_attempts fta1
            INNER JOIN (
                SELECT user_id, full_test_id, MAX(created_at) as max_created
                FROM full_test_attempts
                WHERE status = 'in_progress'
                GROUP BY user_id, full_test_id
                HAVING COUNT(*) > 1
            ) fta2 ON fta1.user_id = fta2.user_id
                AND fta1.full_test_id = fta2.full_test_id
                AND fta1.created_at < fta2.max_created
            WHERE fta1.status = 'in_progress'
        ");

        if (!empty($duplicates)) {
            $ids = array_column($duplicates, 'id');
            DB::table('full_test_attempts')
                ->whereIn('id', $ids)
                ->update([
                    'status' => 'abandoned',
                    'updated_at' => now()
                ]);

            \Log::info('Cleaned up duplicate in_progress full test attempts', ['count' => count($ids), 'ids' => $ids]);
        }
    }
};
