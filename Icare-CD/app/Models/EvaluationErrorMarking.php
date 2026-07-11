<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvaluationErrorMarking extends Model
{
    protected $fillable = [
        'human_evaluation_id',
        'student_answer_id',
        'task_number',
        'marked_text',
        'start_position',
        'end_position',
        'error_type',
        'comment'
    ];
    
    protected $casts = [
        'start_position' => 'integer',
        'end_position' => 'integer',
        'task_number' => 'integer'
    ];
    
    protected $appends = ['note']; // Add note to JSON output
    
    public function humanEvaluation(): BelongsTo
    {
        return $this->belongsTo(HumanEvaluation::class);
    }
    
    public function studentAnswer(): BelongsTo
    {
        return $this->belongsTo(StudentAnswer::class);
    }
    
    /**
     * Accessor for note (alias for comment)
     */
    public function getNoteAttribute()
    {
        return $this->comment;
    }
    
    /**
     * Mutator for note (alias for comment)
     */
    public function setNoteAttribute($value)
    {
        $this->attributes['comment'] = $value;
    }
    
    /**
     * Get human-readable error type
     */
    public function getErrorTypeLabel(): string
    {
        return match($this->error_type) {
            'task_achievement' => 'Task Achievement',
            'coherence_cohesion' => 'Coherence & Cohesion',
            'lexical_resource' => 'Lexical Resource',
            'grammar' => 'Grammar',
            default => $this->error_type
        };
    }
    
    /**
     * Get color class for error type
     */
    public function getErrorTypeColor(): string
    {
        return match($this->error_type) {
            'task_achievement' => 'bg-blue-200 border-blue-400',
            'coherence_cohesion' => 'bg-purple-200 border-purple-400',
            'lexical_resource' => 'bg-yellow-200 border-yellow-400',
            'grammar' => 'bg-red-200 border-red-400',
            default => 'bg-gray-200 border-gray-400'
        };
    }
}
