<?php

namespace App\Traits;

use App\Models\QuestionBlank;
use Illuminate\Support\Facades\Log;

trait ManagesBlanks
{
    /**
     * Save blank answers using QuestionBlank model
     */
    public function saveBlanks(array $blankAnswers)
    {
        // Clear existing blanks
        $this->blanks()->delete();
        
        foreach ($blankAnswers as $number => $answer) {
            if (!empty($answer)) {
                $this->blanks()->create([
                    'blank_number' => $number,
                    'correct_answer' => $answer,
                    'alternate_answers' => $this->extractAlternates($answer)
                ]);
            }
        }
        
        // Also save in section_specific_data for backward compatibility
        $sectionData = $this->section_specific_data ?? [];
        $sectionData['blank_answers'] = $blankAnswers;
        $this->section_specific_data = $sectionData;
        $this->save();
    }
    
    /**
     * Extract alternate answers from format: "answer1/answer2/answer3"
     */
    private function extractAlternates($answer)
    {
        if (strpos($answer, '/') === false) {
            return null;
        }
        
        $parts = array_map('trim', explode('/', $answer));
        // First part is the main answer, rest are alternates
        return array_slice($parts, 1);
    }
    
    /**
     * Check if a student's answer is correct for a specific blank
     */
    public function checkBlankAnswer($blankNumber, $studentAnswer): bool
    {
        // First try QuestionBlank model
        $blank = $this->blanks()->where('blank_number', $blankNumber)->first();
        
        if ($blank) {
            return $blank->isCorrect($studentAnswer);
        }
        
        // Fallback to section_specific_data
        $blankAnswers = $this->section_specific_data['blank_answers'] ?? [];
        $correctAnswer = $blankAnswers[$blankNumber] ?? null;
        
        if (!$correctAnswer) {
            Log::warning("No answer found for blank {$blankNumber} in question {$this->id}");
            return false;
        }
        
        // Handle multiple answers separated by /
        if (strpos($correctAnswer, '/') !== false) {
            $possibleAnswers = array_map('trim', explode('/', $correctAnswer));
            foreach ($possibleAnswers as $answer) {
                if ($this->compareAnswers($studentAnswer, $answer)) {
                    return true;
                }
            }
            return false;
        }
        
        return $this->compareAnswers($studentAnswer, $correctAnswer);
    }
    
    /**
     * Get all blank answers with proper indexing
     */
    public function getBlankAnswersArray(): array
    {
        // First try QuestionBlank model
        if ($this->blanks()->exists()) {
            $blankData = $this->blanks()
                ->orderBy('blank_number')
                ->pluck('correct_answer', 'blank_number')
                ->toArray();
            
            // Log for debugging
            \Log::info('Getting blank answers from QuestionBlank', [
                'question_id' => $this->id,
                'blank_data' => $blankData
            ]);
            
            return $blankData;
        }
        
        // Fallback to section_specific_data
        $data = $this->section_specific_data['blank_answers'] ?? [];
        
        // Log for debugging
        \Log::info('Getting blank answers from section_specific_data', [
            'question_id' => $this->id,
            'section_data' => $data
        ]);
        
        // If data contains slashes, extract first answer only
        $cleanData = [];
        foreach ($data as $num => $answer) {
            if (strpos($answer, '/') !== false) {
                $parts = explode('/', $answer);
                $cleanData[$num] = trim($parts[0]);
            } else {
                $cleanData[$num] = $answer;
            }
        }
        
        return $cleanData;
    }
    
    /**
     * Check multiple blank answers at once
     */
    public function checkMultipleBlanks(array $studentAnswers): array
    {
        $results = [
            'total' => 0,
            'correct' => 0,
            'details' => []
        ];
        
        // Get all correct answers
        $correctAnswers = $this->getBlankAnswersArray();
        
        // Process each blank
        foreach ($correctAnswers as $blankNum => $correctAnswer) {
            $results['total']++;
            
            // Try different key formats for student answer
            $studentAnswer = $this->findStudentAnswer($studentAnswers, $blankNum);
            
            if ($studentAnswer !== null && $this->checkBlankAnswer($blankNum, $studentAnswer)) {
                $results['correct']++;
                $results['details'][$blankNum] = [
                    'correct' => true,
                    'student_answer' => $studentAnswer,
                    'correct_answer' => $correctAnswer
                ];
            } else {
                $results['details'][$blankNum] = [
                    'correct' => false,
                    'student_answer' => $studentAnswer ?? '',
                    'correct_answer' => $correctAnswer
                ];
            }
        }
        
        return $results;
    }
    
    /**
     * Find student answer with flexible key matching
     */
    private function findStudentAnswer(array $studentAnswers, $blankNumber): ?string
    {
        // Try different key formats
        $possibleKeys = [
            "blank_{$blankNumber}",     // blank_1
            "blank{$blankNumber}",      // blank1
            (string)$blankNumber,       // "1"
            $blankNumber,               // 1
            "answer_{$blankNumber}",    // answer_1
            "BLANK_{$blankNumber}",     // BLANK_1
        ];
        
        foreach ($possibleKeys as $key) {
            if (isset($studentAnswers[$key])) {
                return trim($studentAnswers[$key]);
            }
        }
        
        // Log for debugging
        Log::info("Could not find student answer for blank {$blankNumber}", [
            'available_keys' => array_keys($studentAnswers),
            'tried_keys' => $possibleKeys
        ]);
        
        return null;
    }
    
    /**
     * Parse inline blanks from content
     * Format: [____answer____] or [____answer1|answer2____]
     */
    public function parseAndSaveInlineBlanks()
    {
        $content = $this->content;
        $blanks = [];
        $displayContent = $content;
        $blankNumber = 0;
        
        // Match pattern [____anything____] where anything is not just a number
        $pattern = '/\[____([^\d]+.*?)____\]/'; 
        
        $displayContent = preg_replace_callback($pattern, function($matches) use (&$blanks, &$blankNumber) {
            $blankNumber++;
            $answerString = trim($matches[1]);
            
            // Split by | for multiple correct answers
            $answers = array_map('trim', explode('|', $answerString));
            
            $blanks[$blankNumber] = $answers;
            
            // Return numbered placeholder
            return "[____{$blankNumber}____]";
        }, $content);
        
        // Save blanks if found
        if (!empty($blanks)) {
            $blankAnswers = [];
            foreach ($blanks as $num => $answers) {
                $blankAnswers[$num] = implode('/', $answers);
            }
            
            $this->saveBlanks($blankAnswers);
            $this->content = $displayContent;
            $this->save();
        }
        
        return ['blanks' => $blanks, 'count' => $blankNumber];
    }
    
    /**
     * Compare answers with normalization
     */
    private function compareAnswers($studentAnswer, $correctAnswer): bool
    {
        // Handle null/empty
        if (empty($studentAnswer) && empty($correctAnswer)) {
            return true;
        }
        
        if (empty($studentAnswer) || empty($correctAnswer)) {
            return false;
        }
        
        // Normalize
        $studentNorm = $this->normalizeAnswer($studentAnswer);
        $correctNorm = $this->normalizeAnswer($correctAnswer);
        
        // Direct match
        if ($studentNorm === $correctNorm) {
            return true;
        }
        
        // Check alternatives (separated by /)
        if (strpos($correctAnswer, '/') !== false) {
            $alternatives = array_map('trim', explode('/', $correctAnswer));
            foreach ($alternatives as $alt) {
                if ($this->normalizeAnswer($alt) === $studentNorm) {
                    return true;
                }
            }
        }
        
        // Check parenthetical variations
        if (preg_match('/\(([^)]+)\)/', $correctAnswer)) {
            // Try without parentheses
            $withoutParens = preg_replace('/\([^)]+\)/', '', $correctAnswer);
            if ($this->normalizeAnswer($withoutParens) === $studentNorm) {
                return true;
            }
            
            // Try with parentheses content included
            $withParens = str_replace(['(', ')'], '', $correctAnswer);
            if ($this->normalizeAnswer($withParens) === $studentNorm) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get correct answer for display
     */
    public function getCorrectAnswerForDisplay($blankNumber = null)
    {
        if ($blankNumber !== null) {
            // For specific blank
            $blank = $this->blanks()->where('blank_number', $blankNumber)->first();
            if ($blank) {
                return $blank->correct_answer;
            }
            
            // Fallback to section_specific_data
            $answers = $this->getBlankAnswersArray();
            return $answers[$blankNumber] ?? 'N/A';
        }
        
        // For all blanks
        $answers = $this->getBlankAnswersArray();
        if (empty($answers)) {
            return 'N/A';
        }
        
        return implode(', ', $answers);
    }
    
    /**
     * Normalize answer for comparison
     */
    private function normalizeAnswer($answer): string
    {
        $answer = (string) $answer;
        $answer = strtolower($answer);
        $answer = trim($answer);
        $answer = preg_replace('/\s+/', ' ', $answer);
        $answer = preg_replace("/[^\w\s'\-]/", '', $answer);
        
        // Remove articles
        $answer = preg_replace('/^(the|a|an)\s+/i', '', $answer);
        $answer = preg_replace('/\s+(the|a|an)\s+/i', ' ', $answer);
        
        // Common replacements
        $replacements = [
            "don't" => "do not",
            "won't" => "will not",
            "can't" => "cannot",
            "it's" => "it is",
            "didn't" => "did not",
            "doesn't" => "does not",
            '&' => 'and',
            
            // Numbers
            'one' => '1', 'two' => '2', 'three' => '3',
            'four' => '4', 'five' => '5', 'six' => '6',
            'seven' => '7', 'eight' => '8', 'nine' => '9',
            'ten' => '10', 'eleven' => '11', 'twelve' => '12',
            
            // Spelling variations
            'colour' => 'color',
            'centre' => 'center',
            'theatre' => 'theater',
            'organisation' => 'organization',
        ];
        
        $answer = strtr($answer, $replacements);
        $answer = preg_replace('/\s+/', ' ', $answer);
        
        return trim($answer);
    }
}