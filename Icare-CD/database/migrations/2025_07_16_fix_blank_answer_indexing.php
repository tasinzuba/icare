<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Question;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix existing questions with blank answers to ensure proper indexing
        $questions = Question::whereNotNull('section_specific_data')
            ->where(function($query) {
                $query->where('question_type', 'sentence_completion')
                    ->orWhere('question_type', 'note_completion')
                    ->orWhere('question_type', 'summary_completion')
                    ->orWhere('question_type', 'form_completion');
            })
            ->get();
            
        foreach ($questions as $question) {
            $sectionData = $question->section_specific_data;
            $updated = false;
            
            // Fix blank_answers indexing if needed
            if (isset($sectionData['blank_answers']) && is_array($sectionData['blank_answers'])) {
                $fixedAnswers = [];
                $index = 1;
                
                // Re-index blank answers starting from 1
                foreach ($sectionData['blank_answers'] as $key => $value) {
                    if (is_numeric($key) && $key == 0) {
                        // Fix 0-based indexing
                        $fixedAnswers[$index] = $value;
                        $index++;
                    } elseif (is_numeric($key)) {
                        $fixedAnswers[$key] = $value;
                    } else {
                        // Handle non-numeric keys
                        $fixedAnswers[$index] = $value;
                        $index++;
                    }
                }
                
                if ($fixedAnswers != $sectionData['blank_answers']) {
                    $sectionData['blank_answers'] = $fixedAnswers;
                    $updated = true;
                }
            }
            
            // Fix dropdown_correct indexing if needed
            if (isset($sectionData['dropdown_correct']) && is_array($sectionData['dropdown_correct'])) {
                $fixedDropdown = [];
                $index = 1;
                
                foreach ($sectionData['dropdown_correct'] as $key => $value) {
                    if (is_numeric($key) && $key == 0) {
                        $fixedDropdown[$index] = $value;
                        $index++;
                    } elseif (is_numeric($key)) {
                        $fixedDropdown[$key] = $value;
                    } else {
                        $fixedDropdown[$index] = $value;
                        $index++;
                    }
                }
                
                if ($fixedDropdown != $sectionData['dropdown_correct']) {
                    $sectionData['dropdown_correct'] = $fixedDropdown;
                    $updated = true;
                }
            }
            
            // Fix dropdown_options indexing if needed
            if (isset($sectionData['dropdown_options']) && is_array($sectionData['dropdown_options'])) {
                $fixedOptions = [];
                $index = 1;
                
                foreach ($sectionData['dropdown_options'] as $key => $value) {
                    if (is_numeric($key) && $key == 0) {
                        $fixedOptions[$index] = $value;
                        $index++;
                    } elseif (is_numeric($key)) {
                        $fixedOptions[$key] = $value;
                    } else {
                        $fixedOptions[$index] = $value;
                        $index++;
                    }
                }
                
                if ($fixedOptions != $sectionData['dropdown_options']) {
                    $sectionData['dropdown_options'] = $fixedOptions;
                    $updated = true;
                }
            }
            
            if ($updated) {
                $question->section_specific_data = $sectionData;
                $question->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse this fix
    }
};