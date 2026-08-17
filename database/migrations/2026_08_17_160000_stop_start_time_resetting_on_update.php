<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Stop student_attempts.start_time rewriting itself on every update.
     *
     * The column was created as `timestamp DEFAULT current_timestamp() ON UPDATE current_timestamp()`
     * — MySQL's default for the first timestamp column in a table when nothing else is specified. So
     * every write to the row, whatever it changed, silently reset the moment the student started to
     * the moment of the write.
     *
     * That is why attempts ended up with end_time before start_time and with negative durations: the
     * finish was recorded, then some later update pushed the start past it. It also undermines the
     * time limit, which is measured from start_time.
     *
     * The same defect on end_time would reset the finish time on any later edit, so it is checked
     * too. Nothing here touches the values themselves; the rows already damaged are repaired
     * separately, since guessing a start time is a judgement this migration should not make.
     */
    public function up(): void
    {
        if (!Schema::hasTable('student_attempts')) {
            return;
        }

        foreach (['start_time', 'end_time'] as $column) {
            $definition = DB::selectOne('SHOW COLUMNS FROM student_attempts WHERE Field = ?', [$column]);
            if (!$definition || !str_contains(strtolower((string) $definition->Extra), 'on update')) {
                continue;
            }

            // The DEFAULT has to be stated. MySQL applies DEFAULT CURRENT_TIMESTAMP ON UPDATE
            // CURRENT_TIMESTAMP to a NOT NULL timestamp column that declares neither, so leaving it
            // off puts the very clause this migration removes straight back.
            $suffix = strtoupper((string) $definition->Null) === 'YES'
                ? 'NULL DEFAULT NULL'
                : 'NOT NULL DEFAULT CURRENT_TIMESTAMP';

            DB::statement("ALTER TABLE `student_attempts` MODIFY `{$column}` TIMESTAMP {$suffix}");
        }
    }

    public function down(): void
    {
        // Deliberately not restored: putting ON UPDATE CURRENT_TIMESTAMP back would resume
        // corrupting these columns on every write.
    }
};
