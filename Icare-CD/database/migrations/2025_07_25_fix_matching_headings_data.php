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
        // Ensure section_specific_data is JSON type
        Schema::table('questions', function (Blueprint $table) {
            // First check if column exists
            if (!Schema::hasColumn('questions', 'section_specific_data')) {
                $table->json('section_specific_data')->nullable();
            }
        });
        
        // Fix any existing matching_headings questions that might have corrupted data
        $matchingHeadingsQuestions = DB::table('questions')
            ->where('question_type', 'matching_headings')
            ->get();
            
        foreach ($matchingHeadingsQuestions as $question) {
            try {
                // Try to decode existing data
                $data = json_decode($question->section_specific_data, true);
                
                // If data is invalid or missing required keys, fix it
                if (!is_array($data) || !isset($data['headings']) || !isset($data['mappings'])) {
                    $fixedData = [
                        'headings' => $data['headings'] ?? [],
                        'mappings' => $data['mappings'] ?? []
                    ];
                    
                    DB::table('questions')
                        ->where('id', $question->id)
                        ->update([
                            'section_specific_data' => json_encode($fixedData)
                        ]);
                }
            } catch (\Exception $e) {
                // If JSON decode fails, reset to empty structure
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
        // Nothing to revert
    }
};
