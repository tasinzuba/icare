<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionOption extends Model
{
    protected $fillable = ['question_id', 'content', 'is_correct', 'order', 'metadata'];

    /**
     * Option text is rendered with v-html on the test pages, so scrub scripting on write.
     * Clean content is stored unchanged.
     */
    public function setContentAttribute($value): void
    {
        $this->attributes['content'] = \App\Models\Question::sanitizeAuthorHtml($value);
    }
    
    protected $casts = [
        'is_correct' => 'boolean',
        'metadata' => 'array',
    ];
    
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}