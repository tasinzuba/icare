<?php

namespace App\Console\Commands;

use App\Models\Question;
use App\Models\QuestionBlank;
use Illuminate\Console\Command;

class MigrateExistingBlanks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'blanks:migrate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate existing blank answers from section_specific_data to QuestionBlank model';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting migration of existing blank answers...');
        
        $blankTypes = [
            'sentence_completion',
            'note_completion',
            'summary_completion',
            'form_completion',
            'fill_blanks'
        ];
        
        $questions = Question::whereIn('question_type', $blankTypes)
            ->whereNotNull('section_specific_data')
            ->get();
        
        $this->info("Found {$questions->count()} questions with potential blank data.");
        
        $migrated = 0;
        $skipped = 0;
        
        foreach ($questions as $question) {
            $sectionData = $question->section_specific_data;
            
            if (!isset($sectionData['blank_answers']) || !is_array($sectionData['blank_answers'])) {
                $skipped++;
                continue;
            }
            
            // Check if already migrated
            if ($question->blanks()->exists()) {
                $this->line("Question {$question->id} already has blanks migrated. Skipping.");
                $skipped++;
                continue;
            }
            
            $this->info("Migrating blanks for Question ID: {$question->id}");
            
            foreach ($sectionData['blank_answers'] as $num => $answer) {
                if (!empty($answer)) {
                    // Extract alternatives if present
                    $alternates = null;
                    if (strpos($answer, '/') !== false) {
                        $parts = array_map('trim', explode('/', $answer));
                        $answer = $parts[0]; // Main answer
                        $alternates = array_slice($parts, 1); // Alternative answers
                    }
                    
                    QuestionBlank::create([
                        'question_id' => $question->id,
                        'blank_number' => $num,
                        'correct_answer' => $answer,
                        'alternate_answers' => $alternates
                    ]);
                    
                    $this->line("  - Created blank {$num}: {$answer}");
                }
            }
            
            $migrated++;
        }
        
        $this->info("\nMigration complete!");
        $this->info("Migrated: {$migrated} questions");
        $this->info("Skipped: {$skipped} questions");
        
        return Command::SUCCESS;
    }
}