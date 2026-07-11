<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Update existing records to have attempt_number = 1 if not set
        DB::table('student_attempts')
            ->whereNull('attempt_number')
            ->orWhere('attempt_number', 0)
            ->update([
                'attempt_number' => 1,
                'is_retake' => false
            ]);
            
        // Fix any duplicate attempt numbers per user/test combination
        $attempts = DB::table('student_attempts')
            ->select('user_id', 'test_set_id')
            ->groupBy('user_id', 'test_set_id')
            ->having(DB::raw('COUNT(*)'), '>', 1)
            ->get();
            
        foreach ($attempts as $group) {
            $userAttempts = DB::table('student_attempts')
                ->where('user_id', $group->user_id)
                ->where('test_set_id', $group->test_set_id)
                ->orderBy('created_at', 'asc')
                ->get();
                
            $attemptNumber = 1;
            foreach ($userAttempts as $attempt) {
                DB::table('student_attempts')
                    ->where('id', $attempt->id)
                    ->update([
                        'attempt_number' => $attemptNumber,
                        'is_retake' => $attemptNumber > 1
                    ]);
                $attemptNumber++;
            }
        }
    }

    public function down(): void
    {
        // Nothing to rollback
    }
};
