<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AvatarGenerationTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id',
        'avatar_teacher_id',
        'talk_id',
        'audio_url',
        'status',
        'error_message',
        'result_url',
        'video_url',
        'video_path',
        'duration',
        'webhook_received_at',
        'poll_attempts',
    ];

    protected $casts = [
        'duration' => 'decimal:2',
        'poll_attempts' => 'integer',
        'webhook_received_at' => 'datetime',
    ];

    /**
     * Get the question associated with this task.
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Get the avatar teacher associated with this task.
     */
    public function avatarTeacher(): BelongsTo
    {
        return $this->belongsTo(AvatarTeacher::class);
    }

    /**
     * Check if task is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if task is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if task has failed.
     */
    public function hasFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Mark as processing.
     */
    public function markAsProcessing(): void
    {
        $this->update(['status' => 'processing']);
    }

    /**
     * Mark as completed with result.
     */
    public function markAsCompleted(string $resultUrl, ?string $videoUrl = null, ?string $videoPath = null, ?float $duration = null): void
    {
        $this->update([
            'status' => 'completed',
            'result_url' => $resultUrl,
            'video_url' => $videoUrl,
            'video_path' => $videoPath,
            'duration' => $duration,
            'webhook_received_at' => now(),
        ]);
    }

    /**
     * Mark as failed.
     */
    public function markAsFailed(string $error): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $error,
        ]);
    }

    /**
     * Increment poll attempts.
     */
    public function incrementPollAttempts(): void
    {
        $this->increment('poll_attempts');
    }

    /**
     * Scope for pending tasks.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for processing tasks.
     */
    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    /**
     * Scope for stale tasks (older than X minutes).
     */
    public function scopeStale($query, int $minutes = 5)
    {
        return $query->whereIn('status', ['pending', 'processing'])
            ->where('created_at', '<', now()->subMinutes($minutes));
    }

    /**
     * Find task by D-ID talk_id.
     */
    public static function findByTalkId(string $talkId): ?self
    {
        return static::where('talk_id', $talkId)->first();
    }
}
