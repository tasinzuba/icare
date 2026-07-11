<?php

namespace App\Traits;

trait SimplifiedBlanks
{
    /**
     * Parse content and extract blanks with inline answers
     * Format: [____answer____] or [____answer1|answer2|answer3____]
     */
    public function parseInlineBlanks($content)
    {
        $blanks = [];
        $displayContent = $content;
        $blankNumber = 0;
        
        // Match pattern [____anything____]
        $pattern = '/\[____(.*?)____\]/';
        
        $displayContent = preg_replace_callback($pattern, function($matches) use (&$blanks, &$blankNumber) {
            $blankNumber++;
            $answerString = trim($matches[1]);
            
            // Split by | for multiple correct answers
            $answers = array_map('trim', explode('|', $answerString));
            
            // Store blank info
            $blanks[$blankNumber] = [
                'number' => $blankNumber,
                'answers' => $answers,
                'primary_answer' => $answers[0]
            ];
            
            // Return placeholder for display
            return "[BLANK_{$blankNumber}]";
        }, $content);
        
        return [
            'display_content' => $displayContent,
            'blanks' => $blanks,
            'blank_count' => $blankNumber
        ];
    }
    
    /**
     * Save parsed blanks to database
     */
    public function saveInlineBlanks($content)
    {
        $parsed = $this->parseInlineBlanks($content);
        
        // Clear existing blanks
        $this->blanks()->delete();
        
        // Save new blanks
        foreach ($parsed['blanks'] as $blankNum => $blankData) {
            $this->blanks()->create([
                'blank_number' => $blankNum,
                'correct_answer' => $blankData['primary_answer'],
                'alternate_answers' => count($blankData['answers']) > 1 ? array_slice($blankData['answers'], 1) : null
            ]);
        }
        
        // Update question content with display version
        $this->content = $parsed['display_content'];
        
        // Also save in section_specific_data for backward compatibility
        $blankAnswers = [];
        foreach ($parsed['blanks'] as $num => $data) {
            $blankAnswers[$num] = implode('/', $data['answers']);
        }
        
        $sectionData = $this->section_specific_data ?? [];
        $sectionData['blank_answers'] = $blankAnswers;
        $sectionData['original_content'] = $content; // Keep original for editing
        $this->section_specific_data = $sectionData;
        
        $this->save();
        
        return $parsed;
    }
    
    /**
     * Get original content for editing
     */
    public function getOriginalContent()
    {
        if ($this->section_specific_data && isset($this->section_specific_data['original_content'])) {
            return $this->section_specific_data['original_content'];
        }
        
        // Try to reconstruct from current content and blanks
        $content = $this->content;
        $blanks = $this->blanks()->orderBy('blank_number')->get();
        
        foreach ($blanks as $blank) {
            $answers = [$blank->correct_answer];
            if ($blank->alternate_answers) {
                $answers = array_merge($answers, $blank->alternate_answers);
            }
            $answerString = implode('|', $answers);
            
            $content = str_replace(
                "[BLANK_{$blank->blank_number}]", 
                "[____{$answerString}____]", 
                $content
            );
        }
        
        return $content;
    }
}