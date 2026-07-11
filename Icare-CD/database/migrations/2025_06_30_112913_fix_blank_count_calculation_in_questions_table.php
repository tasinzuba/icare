<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Question;

return new class extends Migration
{
    public function up(): void
    {
        // Update existing questions' blank count
        $questions = Question::where('question_type', 'fill_blanks')->get();
        
        foreach ($questions as $question) {
            $blankCount = $question->countBlanks();
            $question->blank_count = $blankCount;
            $question->save();
        }
    }

    public function down(): void
    {
        // Nothing to rollback
    }
};