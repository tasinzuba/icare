<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class FullTestAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'full_test_id',
        'start_time',
        'end_time',
        'status',
        'current_section',
        'overall_band_score',
        'listening_score',
        'reading_score',
        'writing_score',
        'speaking_score',
        'feedback'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'overall_band_score' => 'float',
        'listening_score' => 'float',
        'reading_score' => 'float',
        'writing_score' => 'float',
        'speaking_score' => 'float'
    ];

    /**
     * Get the user who took this test.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the full test.
     */
    public function fullTest(): BelongsTo
    {
        return $this->belongsTo(FullTest::class);
    }

    /**
     * Get the section attempts.
     */
    public function sectionAttempts(): HasMany
    {
        return $this->hasMany(FullTestSectionAttempt::class);
    }

    /**
     * Get the student attempts through section attempts.
     */
    public function studentAttempts(): HasManyThrough
    {
        return $this->hasManyThrough(
            StudentAttempt::class,
            FullTestSectionAttempt::class,
            'full_test_attempt_id',
            'id',
            'id',
            'student_attempt_id'
        );
    }

    /**
     * Get listening attempt.
     */
    public function listeningAttempt()
    {
        return $this->sectionAttempts()
            ->where('section_type', 'listening')
            ->with('studentAttempt')
            ->first()?->studentAttempt;
    }

    /**
     * Get reading attempt.
     */
    public function readingAttempt()
    {
        return $this->sectionAttempts()
            ->where('section_type', 'reading')
            ->with('studentAttempt')
            ->first()?->studentAttempt;
    }

    /**
     * Get writing attempt.
     */
    public function writingAttempt()
    {
        return $this->sectionAttempts()
            ->where('section_type', 'writing')
            ->with('studentAttempt')
            ->first()?->studentAttempt;
    }

    /**
     * Get speaking attempt.
     */
    public function speakingAttempt()
    {
        return $this->sectionAttempts()
            ->where('section_type', 'speaking')
            ->with('studentAttempt')
            ->first()?->studentAttempt;
    }

    /**
     * Calculate overall band score.
     * Only calculates based on available sections.
     * Uses official IELTS rounding rules.
     */
    public function calculateOverallScore(): float
    {
        $scores = [];

        // Only include scores for sections that exist in the test
        if ($this->fullTest->hasSection('listening') && $this->listening_score !== null) {
            $scores[] = $this->listening_score;
        }
        if ($this->fullTest->hasSection('reading') && $this->reading_score !== null) {
            $scores[] = $this->reading_score;
        }
        if ($this->fullTest->hasSection('writing') && $this->writing_score !== null) {
            $scores[] = $this->writing_score;
        }
        if ($this->fullTest->hasSection('speaking') && $this->speaking_score !== null) {
            $scores[] = $this->speaking_score;
        }

        if (count($scores) === 0) {
            return 0;
        }

        $average = array_sum($scores) / count($scores);

        // FIX: Use official IELTS rounding rules instead of PHP's banker's rounding
        // IELTS Rules:
        // - Decimal < 0.25 → Round DOWN to whole number
        // - Decimal 0.25-0.75 → Round to .5
        // - Decimal > 0.75 → Round UP to next whole number
        $decimal = $average - floor($average);

        if ($decimal < 0.25) {
            return floor($average);
        } elseif ($decimal < 0.75) {
            return floor($average) + 0.5;
        } else {
            return ceil($average);
        }
    }

    /**
     * Update section score.
     */
    public function updateSectionScore(string $section, float $score): void
    {
        \Log::info("Updating {$section} score to {$score} for full test attempt #{$this->id}");

        $this->update([
            "{$section}_score" => $score
        ]);

        // Refresh model to get updated values
        $this->refresh();

        \Log::info("After update: {$section}_score = " . $this->{$section . '_score'});

        // Recalculate overall score if all sections completed
        if ($this->hasAllSectionScores()) {
            $overallScore = $this->calculateOverallScore();
            \Log::info("All sections have scores. Calculating overall: {$overallScore}");

            $this->update([
                'overall_band_score' => $overallScore
            ]);

            $this->refresh();
            \Log::info("Overall band score updated to: {$this->overall_band_score}");
        } else {
            \Log::info("Not all sections have scores yet.");
        }
    }

    /**
     * Check if all available sections have scores.
     */
    public function hasAllSectionScores(): bool
    {
        $availableSections = $this->fullTest->getAvailableSections();
        $scoredSections = 0;
        
        foreach ($availableSections as $section) {
            $scoreField = $section . '_score';
            if ($this->$scoreField !== null) {
                $scoredSections++;
            }
        }
        
        // All available sections must have scores
        return $scoredSections === count($availableSections);
    }

    /**
     * Get next section to complete.
     *
     * A section is only "completed" when its linked StudentAttempt has status=completed.
     * Sections with abandoned/in_progress StudentAttempts are treated as incomplete
     * and must be retaken — this handles page reloads, tab closures, and network drops.
     */
    public function getNextSection(): ?string
    {
        $availableSections = $this->fullTest->getAvailableSections();
        $completedSections = $this->getActuallyCompletedSections();

        foreach ($availableSections as $section) {
            if (!in_array($section, $completedSections)) {
                return $section;
            }
        }

        return null;
    }

    /**
     * Get sections that are truly completed (StudentAttempt status = completed).
     * This is the single source of truth for section completion across the entire system.
     *
     * A FullTestSectionAttempt record existing does NOT mean the section is done.
     * The student may have been interrupted (page reload, tab close, network drop).
     * Only when the linked StudentAttempt is 'completed' is the section truly finished.
     */
    public function getActuallyCompletedSections(): array
    {
        return $this->sectionAttempts()
            ->whereHas('studentAttempt', function ($query) {
                $query->where('status', 'completed');
            })
            ->pluck('section_type')
            ->toArray();
    }

    /**
     * Clean up stale (non-completed) section attempt records for a given section.
     * This removes FullTestSectionAttempt records whose linked StudentAttempt
     * was abandoned or is still in_progress, allowing the section to be retaken.
     *
     * Called before creating a fresh section attempt when the student re-enters a section.
     */
    public function cleanupStaleSectionAttempt(string $sectionType): void
    {
        $staleSectionAttempts = $this->sectionAttempts()
            ->where('section_type', $sectionType)
            ->whereHas('studentAttempt', function ($query) {
                $query->whereIn('status', ['in_progress', 'abandoned']);
            })
            ->with('studentAttempt')
            ->get();

        foreach ($staleSectionAttempts as $staleAttempt) {
            // Mark the linked StudentAttempt as abandoned if still in_progress
            if ($staleAttempt->studentAttempt && $staleAttempt->studentAttempt->status === 'in_progress') {
                $staleAttempt->studentAttempt->update(['status' => 'abandoned']);
            }

            // Remove the stale FullTestSectionAttempt so a fresh one can be created
            $staleAttempt->delete();
        }
    }

    /**
     * Check if test is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Mark test as completed.
     */
    public function markAsCompleted(): void
    {
        $data = [
            'status' => 'completed',
            'end_time' => now(),
        ];

        // Only store overall band score if ALL sections have official scores
        // Don't store partial averages — the results page recalculates dynamically
        // using effective scores (human > AI fallback) for accurate display
        if ($this->hasAllSectionScores()) {
            $data['overall_band_score'] = $this->calculateOverallScore();
        }

        $this->update($data);
    }

    /**
     * Check if attempt is expired for offline students.
     * Offline students have 24 hours to complete a full test.
     */
    public function isExpiredForOfflineStudent(): bool
    {
        // Only applies to offline students
        if (!$this->user->isOfflineStudent()) {
            return false;
        }

        // Only check for in_progress attempts
        if ($this->status !== 'in_progress') {
            return false;
        }

        // Check if 24 hours have passed since start_time
        if ($this->start_time && $this->start_time->diffInHours(now()) >= 24) {
            return true;
        }

        return false;
    }

    /**
     * Mark test as expired.
     * This counts as a used test attempt.
     */
    public function markAsExpired(): void
    {
        $this->update([
            'status' => 'expired',
            'end_time' => now(),
        ]);

        // Calculate partial scores if any sections were completed
        $overallScore = $this->calculateOverallScore();
        if ($overallScore > 0) {
            $this->update(['overall_band_score' => $overallScore]);
        }
    }

    /**
     * Get remaining time in hours for offline students.
     * Returns null for online students.
     */
    public function getRemainingHoursAttribute(): ?int
    {
        if (!$this->user->isOfflineStudent()) {
            return null;
        }

        if ($this->status !== 'in_progress' || !$this->start_time) {
            return null;
        }

        $hoursElapsed = $this->start_time->diffInHours(now());
        $remaining = 24 - $hoursElapsed;

        return max(0, $remaining);
    }

    /**
     * Get remaining time formatted for display.
     */
    public function getRemainingTimeFormattedAttribute(): ?string
    {
        if (!$this->user->isOfflineStudent()) {
            return null;
        }

        if ($this->status !== 'in_progress' || !$this->start_time) {
            return null;
        }

        $totalMinutesElapsed = $this->start_time->diffInMinutes(now());
        $totalMinutesAllowed = 24 * 60; // 24 hours
        $remainingMinutes = max(0, $totalMinutesAllowed - $totalMinutesElapsed);

        $hours = floor($remainingMinutes / 60);
        $minutes = $remainingMinutes % 60;

        if ($hours > 0) {
            return "{$hours}h {$minutes}m remaining";
        }

        return "{$minutes}m remaining";
    }

    /**
     * Check if attempt can be continued.
     */
    public function canContinue(): bool
    {
        // Must be in_progress
        if ($this->status !== 'in_progress') {
            return false;
        }

        // Check expiration for offline students
        if ($this->isExpiredForOfflineStudent()) {
            return false;
        }

        return true;
    }
}
