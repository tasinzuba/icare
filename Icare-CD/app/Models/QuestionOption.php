<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionOption extends Model
{
    protected $fillable = ['question_id', 'content', 'is_correct', 'order', 'metadata'];
    
    protected $casts = [
        'is_correct' => 'boolean',
        'metadata' => 'array',
    ];
    
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}