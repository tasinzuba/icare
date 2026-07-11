<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

class TestCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'color',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Test sets that belong to this category
     */
    public function testSets(): BelongsToMany
    {
        return $this->belongsToMany(TestSet::class, 'test_category_test_set')
            ->withTimestamps();
    }

    /**
     * Get active test sets
     */
    public function activeTestSets(): BelongsToMany
    {
        return $this->testSets()
            ->where('test_sets.active', true)
            ->orderBy('test_sets.created_at', 'desc');
    }

    /**
     * Get test sets by section
     */
    public function testSetsBySection(string $sectionSlug): BelongsToMany
    {
        return $this->testSets()
            ->whereHas('section', function ($query) use ($sectionSlug) {
                $query->where('slug', $sectionSlug)
                      ->orWhere('name', $sectionSlug);
            })
            ->where('test_sets.active', true)
            ->orderBy('test_sets.created_at', 'desc');
    }
    
    /**
     * Get count of test sets by section
     * Using a more efficient approach with cached counts
     */
    public function getTestCountBySection(string $section): int
    {
        // Use Eloquent's relationship to count properly
        return $this->testSets()
            ->whereHas('section', function ($query) use ($section) {
                $query->where('slug', $section)
                      ->orWhere('name', $section);
            })
            ->where('active', true)
            ->count();
    }

    /**
     * Count test sets in this category
     */
    public function getTestSetCountAttribute(): int
    {
        return $this->testSets()->count();
    }

    /**
     * Count active test sets in this category
     */
    public function getActiveTestSetCountAttribute(): int
    {
        return $this->activeTestSets()->count();
    }

    /**
     * Scope for active categories
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for ordered categories
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')
            ->orderBy('name', 'asc');
    }
}
