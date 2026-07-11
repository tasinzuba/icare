<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FullTestSectionAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_test_attempt_id',
        'student_attempt_id',
        'section_type'
    ];

    /**
     * Get the full test attempt.
     */
    public function fullTestAttempt(): BelongsTo
    {
        return $this->belongsTo(FullTestAttempt::class);
    }

    /**
     * Get the student attempt.
     */
    public function studentAttempt(): BelongsTo
    {
        return $this->belongsTo(StudentAttempt::class);
    }
}
