<?php

namespace App\Console\Commands;

use App\Models\Question;
use Illuminate\Console\Command;

class FixBlankAnswers extends Command
{
    protected $signature = 'blanks:fix';
    protected $description = 'Fix existing blank questions to ensure answers are properly stored';

    public function handle()
    {
        $this->info('Fixing blank answer questions...');
        
        $questions = Question::whereIn('question_type', [
            'sentence_completion',
            'note_completion', 
            'summary_completion',
            'form_completion',
            'fill_blanks'
        ])->get();
        
        $fixed = 0;
        $failed = 0;
        
        foreach ($questions as $question) {
            $this->line("\nProcessing Question ID: {$question->id}");
            
            // Check if has blanks in content
            if (!preg_match('/\[____\d+____\]|\[BLANK_\d+\]/', $question->content)) {
                $this->warn("No blanks found in content. Skipping.");
                continue;
            }
            
            // Check if already has blank answers
            $hasAnswers = false;
            
            // Check QuestionBlank model
            if ($question->blanks()->exists()) {
                $hasAnswers = true;
                $this->info("Already has QuestionBlank records.");
            }
            
            // Check section_specific_data
            if (!$hasAnswers && $question->section_specific_data && 
                isset($question->section_specific_data['blank_answers'])) {
                
                $blankAnswers = $question->section_specific_data['blank_answers'];
                
                if (!empty($blankAnswers)) {
                    $this->info("Found answers in section_specific_data. Creating QuestionBlank records...");
                    
                    foreach ($blankAnswers as $num => $answer) {
                        if (!empty($answer)) {
                            $question->blanks()->create([
                                'blank_number' => $num,
                                'correct_answer' => $answer,
                                'alternate_answers' => $this->extractAlternates($answer)
                            ]);
                            
                            $this->line("  Created blank {$num}: {$answer}");
                        }
                    }
                    
                    $fixed++;
                    $hasAnswers = true;
                }
            }
            
            if (!$hasAnswers) {
                $this->error("No answers found! Please update this question manually.");
                $this->line("Content: " . substr($question->content, 0, 100) . "...");
                $failed++;
            }
        }
        
        $this->info("\n✅ Fixed: {$fixed} questions");
        $this->info("❌ Failed: {$failed} questions");
        
        if ($failed > 0) {
            $this->warn("\nQuestions without answers need manual update.");
            $this->warn("Use the new inline format: [____answer____]");
        }
        
        return Command::SUCCESS;
    }
    
    private function extractAlternates($answer)
    {
        if (strpos($answer, '/') === false) {
            return null;
        }
        
        $parts = array_map('trim', explode('/', $answer));
        return array_slice($parts, 1);
    }
}