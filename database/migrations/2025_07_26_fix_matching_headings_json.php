<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ensure section_specific_data column exists and is JSON type
        if (!Schema::hasColumn('questions', 'section_specific_data')) {
            Schema::table('questions', function (Blueprint $table) {
                $table->json('section_specific_data')->nullable();
            });
        } else {
            // Change to JSON if it's not already
            try {
                DB::statement('ALTER TABLE questions MODIFY section_specific_data JSON');
            } catch (\Exception $e) {
                // If the above fails, try alternative approach
                Schema::table('questions', function (Blueprint $table) {
                    $table->json('section_specific_data')->nullable()->change();
                });
            }
        }
        
        // Fix any corrupted matching_headings data
        $this->fixMatchingHeadingsData();
    }
    
    /**
     * Fix corrupted matching headings data
     */
    private function fixMatchingHeadingsData()
    {
        $questions = DB::table('questions')
            ->where('question_type', 'matching_headings')
            ->get();
            
        foreach ($questions as $question) {
            try {
                $data = json_decode($question->section_specific_data, true);
                
                // Initialize if null or invalid
                if (!is_array($data)) {
                    $data = [];
                }
                
                // Ensure required keys exist
                if (!isset($data['headings'])) {
                    $data['headings'] = [];
                }
                
                if (!isset($data['mappings'])) {
                    $data['mappings'] = [];
                }
                
                // Save back
                DB::table('questions')
                    ->where('id', $question->id)
                    ->update([
                        'section_specific_data' => json_encode($data)
                    ]);
                    
            } catch (\Exception $e) {
                // If parsing fails, reset to empty structure
                DB::table('questions')
                    ->where('id', $question->id)
                    ->update([
                        'section_specific_data' => json_encode([
                            'headings' => [],
                            'mappings' => []
                        ])
                    ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Nothing to reverse
    }
};
