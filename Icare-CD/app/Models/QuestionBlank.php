<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionBlank extends Model
{
    protected $fillable = [
        'question_id',
        'blank_number',
        'correct_answer',
        'alternate_answers', // JSON array for multiple acceptable answers
    ];

    protected $casts = [
        'alternate_answers' => 'array',
    ];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Check if student answer is correct
     */
    public function isCorrect($studentAnswer): bool
    {
        // Normalize student answer
        $normalized = $this->normalizeAnswer($studentAnswer);
        
        // Check main answer
        if ($normalized === $this->normalizeAnswer($this->correct_answer)) {
            return true;
        }
        
        // Check alternate answers
        if ($this->alternate_answers) {
            foreach ($this->alternate_answers as $alternate) {
                if ($normalized === $this->normalizeAnswer($alternate)) {
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * Normalize answer for comparison
     */
    private function normalizeAnswer($answer): string
    {
        if (empty($answer)) return '';
        
        $answer = (string) $answer;
        $answer = strtolower(trim($answer));
        $answer = preg_replace('/\s+/', ' ', $answer);
        $answer = preg_replace("/[^\w\s'\-]/u", '', $answer); // Added 'u' flag for Unicode
        
        // Remove articles
        $answer = preg_replace('/^(the|a|an)\s+/i', '', $answer);
        
        return $answer;
    }
}