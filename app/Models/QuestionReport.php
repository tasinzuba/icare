<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id',
        'user_id',
        'student_attempt_id',
        'issue_type',
        'description',
        'status',
        'admin_note',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    /** The choices the student is offered on the result page. */
    public const ISSUE_TYPES = [
        'wrong_statement' => 'Wrong question statement',
        'wrong_answer' => 'Wrong option or answer',
        'missing_content' => 'Missing required content',
        'not_related' => 'Question not related to exam',
        'other' => 'Other',
    ];

    public const STATUSES = [
        'pending' => 'Pending',
        'reviewing' => 'Reviewing',
        'resolved' => 'Resolved',
        'dismissed' => 'Dismissed',
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(StudentAttempt::class, 'student_attempt_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function getIssueLabelAttribute(): string
    {
        return self::ISSUE_TYPES[$this->issue_type] ?? ucfirst(str_replace('_', ' ', (string) $this->issue_type));
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst((string) $this->status);
    }

    /** Colour classes for the status pill, kept here so the list and detail views agree. */
    public function getStatusClassesAttribute(): string
    {
        return match ($this->status) {
            'resolved' => 'bg-emerald-100 text-emerald-700',
            'reviewing' => 'bg-blue-100 text-blue-700',
            'dismissed' => 'bg-gray-100 text-gray-600',
            default => 'bg-amber-100 text-amber-700',
        };
    }

    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['pending', 'reviewing']);
    }
}
