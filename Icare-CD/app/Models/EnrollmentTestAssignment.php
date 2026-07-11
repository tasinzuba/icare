<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnrollmentTestAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'offline_enrollment_id',
        'full_test_id',
        'assigned_at',
        'valid_until',
        'status',
        'renewal_batch',
    ];

    protected $casts = [
        'assigned_at' => 'date',
        'valid_until' => 'date',
    ];

    const STATUS_AVAILABLE = 'available';
    const STATUS_COMPLETED = 'completed';
    const STATUS_EXPIRED = 'expired';

    /**
     * Get the enrollment this assignment belongs to
     */
    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(OfflineEnrollment::class, 'offline_enrollment_id');
    }

    /**
     * Get the full test
     */
    public function fullTest(): BelongsTo
    {
        return $this->belongsTo(FullTest::class);
    }

    /**
     * Check if this assignment is expired
     */
    public function isExpired(): bool
    {
        return $this->valid_until->isPast();
    }

    /**
     * Check if this assignment is available (not expired, not completed)
     */
    public function isAvailable(): bool
    {
        return $this->status === self::STATUS_AVAILABLE && !$this->isExpired();
    }

    /**
     * Get days remaining
     */
    public function getDaysRemainingAttribute(): int
    {
        if ($this->valid_until->isPast()) {
            return 0;
        }
        return now()->diffInDays($this->valid_until);
    }

    /**
     * Mark as completed
     */
    public function markCompleted(): void
    {
        $this->update(['status' => self::STATUS_COMPLETED]);
    }

    /**
     * Scope for available tests
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', self::STATUS_AVAILABLE)
                     ->where('valid_until', '>=', now()->toDateString());
    }

    /**
     * Scope for a specific enrollment
     */
    public function scopeForEnrollment($query, $enrollmentId)
    {
        return $query->where('offline_enrollment_id', $enrollmentId);
    }
}
