<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Question;
use App\Models\QuestionOption;

class FixMatchingHeadingsData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:matching-headings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix matching headings question data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Fixing matching headings questions...');
        
        $questions = Question::where('question_type', 'matching_headings')->get();
        
        if ($questions->count() === 0) {
            $this->info('No matching headings questions found.');
            return;
        }
        
        $fixed = 0;
        $failed = 0;
        
        foreach ($questions as $question) {
            try {
                $this->info("Processing question #{$question->id}...");
                
                // Get current data
                $data = $question->section_specific_data ?? [];
                
                // Initialize if missing
                if (!isset($data['headings'])) {
                    $data['headings'] = [];
                }
                
                if (!isset($data['mappings'])) {
                    $data['mappings'] = [];
                }
                
                // Build headings from options if empty
                if (empty($data['headings']) && $question->options->count() > 0) {
                    $this->info("  Building headings from options...");
                    $data['headings'] = [];
                    
                    foreach ($question->options as $index => $option) {
                        $data['headings'][] = [
                            'id' => chr(65 + $index),
                            'text' => $option->content
                        ];
                    }
                }
                
                // Save back
                $question->section_specific_data = $data;
                $question->save();
                
                $this->info("  ✓ Fixed question #{$question->id}");
                $fixed++;
                
            } catch (\Exception $e) {
                $this->error("  ✗ Failed to fix question #{$question->id}: " . $e->getMessage());
                $failed++;
            }
        }
        
        $this->info("\nSummary:");
        $this->info("  Fixed: {$fixed}");
        $this->error("  Failed: {$failed}");
        $this->info("  Total: " . ($fixed + $failed));
    }
}
