<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Batch extends Model
{
    protected $fillable = [
        'branch_id',
        'name',
        'description',
        'full_tests_allowed',
        'section_test_limits',
        'validity_days',
        'allowed_full_tests',
        'allowed_section_tests',
        'status',
        'created_by',
    ];

    protected $casts = [
        'section_test_limits' => 'array',
        'allowed_full_tests' => 'array',
        'allowed_section_tests' => 'array',
        'full_tests_allowed' => 'integer',
        'validity_days' => 'integer',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(OfflineEnrollment::class);
    }

    public function activeEnrollments(): HasMany
    {
        return $this->enrollments()->where('status', 'active');
    }

    /**
     * Total section tests = sum of per-section limits
     */
    public function getSectionTestsAllowedAttribute(): int
    {
        return $this->section_test_limits ? array_sum($this->section_test_limits) : 0;
    }

    public function getStudentCountAttribute(): int
    {
        return $this->enrollments()->count();
    }

    public function getActiveStudentCountAttribute(): int
    {
        return $this->activeEnrollments()->count();
    }

    /**
     * Check if batch has test configuration
     */
    public function isConfigured(): bool
    {
        return $this->full_tests_allowed !== null;
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeConfigured($query)
    {
        return $query->whereNotNull('full_tests_allowed');
    }

    public function scopeForBranch($query, int $branchId)
    {
        return $query->where('branch_id', $branchId);
    }
}
