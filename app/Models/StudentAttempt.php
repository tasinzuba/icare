<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class StudentAttempt extends Model
{
    protected $fillable = [
        'user_id',
        'test_set_id',
        'start_time',
        'end_time',
        'status',
        'band_score',
        'feedback',
        'completion_rate',
        'confidence_level',
        'is_complete_attempt',
        'total_questions',
        'answered_questions',
        'correct_answers',
        'ai_band_score',      // Added
        'ai_evaluated_at',     // Added
        'attempt_number',      // Added for retake
        'is_retake',          // Added for retake
        'original_attempt_id', // Added for retake
        'draft_answers',       // Added for server-side auto-save
        'draft_saved_at',      // Added for server-side auto-save
        'is_practice',         // Added for practice mode
        'practice_mode',       // Added for practice mode ('full', 'task1', 'task2', 'single_question')
        'practice_question_id', // Added for single question practice
        'is_overtime',         // H17: attempt submitted past the enforced time limit
        'time_taken_minutes',  // H17/M36: non-negative elapsed minutes
        'allowed_minutes',     // H17: resolved allowed duration at submit
    ];
    
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'band_score' => 'float',
        'completion_rate' => 'float',
        'is_complete_attempt' => 'boolean',
        'total_questions' => 'integer',
        'answered_questions' => 'integer',
        'correct_answers' => 'integer',
        'ai_band_score' => 'float',           // Added
        'ai_evaluated_at' => 'datetime',      // Added
        'attempt_number' => 'integer',        // Added for retake
        'is_retake' => 'boolean',             // Added for retake
        'draft_answers' => 'array',           // Added for server-side auto-save
        'draft_saved_at' => 'datetime',       // Added for server-side auto-save
        'is_practice' => 'boolean',           // Added for practice mode
        'practice_question_id' => 'integer',  // Added for single question practice
        'is_overtime' => 'boolean',           // H17
        'time_taken_minutes' => 'integer',    // H17/M36
        'allowed_minutes' => 'integer',       // H17
    ];
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function testSet(): BelongsTo
    {
        return $this->belongsTo(TestSet::class);
    }
    
    public function answers(): HasMany
    {
        return $this->hasMany(StudentAnswer::class, 'attempt_id');
    }
    
    /**
     * Get the original attempt (for retakes)
     */
    public function originalAttempt(): BelongsTo
    {
        return $this->belongsTo(StudentAttempt::class, 'original_attempt_id');
    }
    
    /**
     * Get all retakes for this attempt
     */
    public function retakes(): HasMany
    {
        return $this->hasMany(StudentAttempt::class, 'original_attempt_id');
    }
    
    /**
     * Get all attempts for a user and test set (including retakes)
     */
    public static function getAllAttemptsForUserAndTest($userId, $testSetId)
    {
        return self::where('user_id', $userId)
            ->where('test_set_id', $testSetId)
            ->orderBy('attempt_number', 'desc')
            ->get();
    }
    
    /**
     * Get the latest attempt for a user and test set
     */
    public static function getLatestAttempt($userId, $testSetId)
    {
        return self::where('user_id', $userId)
            ->where('test_set_id', $testSetId)
            ->orderBy('attempt_number', 'desc')
            ->orderBy('id', 'desc')  // Fallback for old records without attempt_number
            ->first();
    }
    
    /**
     * Check if user can retake this test
     */
    public function canRetake(): bool
    {
        // Only completed tests can be retaken
        if ($this->status !== 'completed') {
            return false;
        }
        
        // Check if this is already the latest attempt
        $latestAttempt = self::getLatestAttempt($this->user_id, $this->test_set_id);
        return $this->id === $latestAttempt->id;
    }
    
    /**
     * Get AI evaluation jobs for this attempt
     */
    public function aiEvaluationJobs(): HasMany
    {
        return $this->hasMany(AIEvaluationJob::class, 'attempt_id');
    }
    
    /**
     * Get human evaluation request for this attempt
     */
    public function humanEvaluationRequest(): HasOne
    {
        return $this->hasOne(HumanEvaluationRequest::class, 'student_attempt_id');
    }

    /**
     * Get full test section attempt if this is part of a full test
     */
    public function fullTestSectionAttempt(): HasOne
    {
        return $this->hasOne(FullTestSectionAttempt::class, 'student_attempt_id');
    }

    /**
     * Scope to get only exam mode attempts (exclude practice)
     */
    public function scopeExamOnly($query)
    {
        return $query->where('is_practice', false);
    }

    /**
     * Scope to get only practice mode attempts
     */
    public function scopePracticeOnly($query)
    {
        return $query->where('is_practice', true);
    }

    /**
     * Scope to filter by practice mode type
     */
    public function scopeByPracticeMode($query, $mode)
    {
        return $query->where('practice_mode', $mode);
    }
}