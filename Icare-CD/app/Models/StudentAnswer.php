<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class StudentAnswer extends Model
{
    protected $fillable = [
        'attempt_id',
        'question_id',
        'answer',
        'selected_option_id',
        'ai_evaluation',
        'ai_band_score',
        'ai_evaluated_at',
        'detailed_results',
    ];

    protected $casts = [
        'ai_evaluation' => 'array',
        'ai_evaluated_at' => 'datetime',
    ];
    
    public function attempt(): BelongsTo
    {
        return $this->belongsTo(StudentAttempt::class, 'attempt_id');
    }
    
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
    
    public function selectedOption(): BelongsTo
    {
        return $this->belongsTo(QuestionOption::class, 'selected_option_id');
    }
    
    public function speakingRecording(): HasOne
    {
        return $this->hasOne(SpeakingRecording::class, 'answer_id');
    }
}