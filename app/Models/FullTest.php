<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FullTest extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'is_premium',
        'is_for_offline',
        'is_for_online',
        'active',
        'order_number'
    ];

    protected $casts = [
        'is_premium' => 'boolean',
        'is_for_offline' => 'boolean',
        'is_for_online' => 'boolean',
        'active' => 'boolean',
        'order_number' => 'integer'
    ];

    /**
     * Get the test sets for this full test.
     */
    public function testSets(): BelongsToMany
    {
        return $this->belongsToMany(TestSet::class, 'full_test_sets')
            ->withPivot('section_type', 'order_number')
            ->withTimestamps()
            ->orderBy('full_test_sets.order_number');
    }

    /**
     * Get the listening test set.
     */
    public function listeningTestSet()
    {
        return $this->testSets()->wherePivot('section_type', 'listening')->first();
    }

    /**
     * Get the reading test set.
     */
    public function readingTestSet()
    {
        return $this->testSets()->wherePivot('section_type', 'reading')->first();
    }

    /**
     * Get the writing test set.
     */
    public function writingTestSet()
    {
        return $this->testSets()->wherePivot('section_type', 'writing')->first();
    }

    /**
     * Get the speaking test set.
     */
    public function speakingTestSet()
    {
        return $this->testSets()->wherePivot('section_type', 'speaking')->first();
    }

    /**
     * Get all attempts for this full test.
     */
    public function attempts(): HasMany
    {
        return $this->hasMany(FullTestAttempt::class);
    }

    /**
     * Check if all sections are assigned.
     */
    public function hasAllSections(): bool
    {
        $sections = $this->testSets()->pluck('full_test_sets.section_type')->toArray();
        $requiredSections = ['listening', 'reading', 'writing', 'speaking'];
        
        return count(array_intersect($requiredSections, $sections)) === 4;
    }
    
    /**
     * Get available sections for this test in proper sequence order.
     * Uses order_number from pivot table to maintain correct sequence.
     */
    public function getAvailableSections(): array
    {
        // Get sections ordered by order_number from pivot table
        return $this->testSets()
            ->orderBy('full_test_sets.order_number')
            ->get()
            ->pluck('pivot.section_type')
            ->toArray();
    }

    /**
     * Get sections with their order numbers for display.
     */
    public function getSectionsWithOrder(): array
    {
        return $this->testSets()
            ->orderBy('full_test_sets.order_number')
            ->get()
            ->map(function ($testSet) {
                return [
                    'section' => $testSet->pivot->section_type,
                    'order' => $testSet->pivot->order_number,
                    'test_set_id' => $testSet->id,
                    'title' => $testSet->title,
                ];
            })
            ->toArray();
    }
    
    /**
     * Check if test has a specific section.
     */
    public function hasSection(string $section): bool
    {
        return in_array($section, $this->getAvailableSections());
    }

    /**
     * Get minimum required sections count.
     */
    public function getMinimumSectionsCount(): int
    {
        return 3; // Minimum 3 sections required
    }

    /**
     * Check if test has minimum required sections.
     */
    public function hasMinimumSections(): bool
    {
        return count($this->getAvailableSections()) >= $this->getMinimumSectionsCount();
    }

    /**
     * Get active full tests.
     */
    public static function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Get free full tests.
     */
    public static function scopeFree($query)
    {
        return $query->where('is_premium', false);
    }

    /**
     * Get premium full tests.
     */
    public static function scopePremium($query)
    {
        return $query->where('is_premium', true);
    }

    /**
     * Get offline full tests (for branch students).
     */
    public static function scopeOffline($query)
    {
        return $query->where('is_for_offline', true);
    }

    /**
     * Get online full tests (for public students).
     */
    public static function scopeOnline($query)
    {
        return $query->where('is_for_online', true);
    }

    /**
     * Scope for tests based on student type.
     */
    public static function scopeForStudentType($query, $user)
    {
        if ($user->isOfflineStudent()) {
            return $query->where('is_for_offline', true);
        }
        return $query->where('is_for_online', true);
    }

    /**
     * Scope for tests visible to both student types.
     */
    public static function scopeForBoth($query)
    {
        return $query->where('is_for_offline', true)->where('is_for_online', true);
    }

    /**
     * Get visibility label for display.
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
}
