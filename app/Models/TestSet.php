<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TestSet extends Model
{
    protected $fillable = ['title', 'section_id', 'active', 'is_premium', 'is_for_offline', 'is_for_online', 'avatar_teacher_id', 'writing_task_type', 'writing_category', 'time_limit_minutes', 'test_type'];

    protected $casts = [
        'active' => 'boolean',
        'is_premium' => 'boolean',
        'is_for_offline' => 'boolean',
        'is_for_online' => 'boolean',
        'time_limit_minutes' => 'integer',
    ];

    /**
     * Default avatar teacher for speaking test sets.
     */
    public function avatarTeacher(): BelongsTo
    {
        return $this->belongsTo(AvatarTeacher::class, 'avatar_teacher_id');
    }

    /**
     * Scope for free test sets
     */
    public function scopeFree($query)
    {
        return $query->where('is_premium', false);
    }

    /**
     * Scope for premium test sets
     */
    public function scopePremium($query)
    {
        return $query->where('is_premium', true);
    }

    /**
     * Scope for offline test sets (for branch students)
     */
    public function scopeOffline($query)
    {
        return $query->where('is_for_offline', true);
    }

    /**
     * Scope for online test sets (for public students)
     */
    public function scopeOnline($query)
    {
        return $query->where('is_for_online', true);
    }

    /**
     * Scope for tests based on student type
     */
    public function scopeForStudentType($query, $user)
    {
        if ($user->isOfflineStudent()) {
            return $query->where('is_for_offline', true);
        }
        return $query->where('is_for_online', true);
    }

    /**
     * Scope for tests visible to both student types
     */
    public function scopeForBoth($query)
    {
        return $query->where('is_for_offline', true)->where('is_for_online', true);
    }

    /**
     * Get visibility label for display
     */
    public function getVisibilityLabelAttribute(): string
    {
        if ($this->is_for_offline && $this->is_for_online) {
            return 'Both';
        } elseif ($this->is_for_offline) {
            return 'Branch Only';
        } elseif ($this->is_for_online) {
            return 'Online Only';
        }
        return 'None';
    }
    
    public function section(): BelongsTo
    {
        return $this->belongsTo(TestSection::class, 'section_id');
    }
    
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('order_number');
    }
    
    public function attempts(): HasMany
    {
        return $this->hasMany(StudentAttempt::class);
    }

    public function partAudios()
{
    return $this->hasMany(TestPartAudio::class)->orderBy('part_number');
}

public function getPartAudio($partNumber)
{
    // First check if full audio exists (part_number = 0)
    $fullAudio = $this->partAudios()->where('part_number', 0)->first();
    
    // If full audio exists, return it for any part
    if ($fullAudio) {
        return $fullAudio;
    }
    
    // Otherwise return specific part audio
    return $this->partAudios()->where('part_number', $partNumber)->first();
}

public function hasPartAudio($partNumber): bool
{
    // Check if full audio exists (part_number = 0)
    $hasFullAudio = $this->partAudios()->where('part_number', 0)->exists();
    
    // If full audio exists, all parts have audio
    if ($hasFullAudio) {
        return true;
    }
    
    // Otherwise check for specific part audio
    return $this->partAudios()->where('part_number', $partNumber)->exists();
}

    /**
     * Categories that this test set belongs to
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(TestCategory::class, 'test_category_test_set')
            ->withTimestamps();
    }

    /**
     * Check if test set belongs to a specific category
     */
    public function belongsToCategory(string $categorySlug): bool
    {
        return $this->categories()->where('slug', $categorySlug)->exists();
    }

    /**
     * Get primary category (first one)
     */
    public function getPrimaryCategoryAttribute()
    {
        return $this->categories()->orderBy('sort_order')->first();
    }

    /**
     * Full tests that this test set belongs to
     */
    public function fullTests(): BelongsToMany
    {
        return $this->belongsToMany(FullTest::class, 'full_test_sets')
            ->withPivot('section_type', 'order_number')
            ->withTimestamps();
    }

    /**
     * Whether this is a single-task writing test set.
     */
    public function isSingleTaskWriting(): bool
    {
        return in_array($this->writing_task_type, ['task1', 'task2']);
    }

    /**
     * Get the effective time limit in minutes for this test set.
     * Priority: per-test-set override > task-type default > section default
     */
    public function getEffectiveTimeLimitMinutes(): int
    {
        if ($this->time_limit_minutes) {
            return $this->time_limit_minutes;
        }

        if ($this->writing_task_type === 'task1') return 20;
        if ($this->writing_task_type === 'task2') return 40;

        return $this->section->time_limit ?? 60;
    }

    /**
     * Get task number (1 or 2) for single-task test sets. Null for combined.
     */
    public function getWritingTaskNumber(): ?int
    {
        if ($this->writing_task_type === 'task1') return 1;
        if ($this->writing_task_type === 'task2') return 2;
        return null;
    }
}