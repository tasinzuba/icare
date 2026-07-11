<?php

namespace App\Events;

use App\Models\StudentAttempt;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event dispatched when a student completes a test
 *
 * This event is fired after a test is submitted and scored.
 * Listeners can handle side effects like:
 * - Updating user statistics
 * - Updating leaderboard entries
 * - Checking for achievements
 * - Sending notifications
 */
class TestCompleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The student attempt that was completed
     */
    public StudentAttempt $attempt;

    /**
     * The user who completed the test
     */
    public User $user;

    /**
     * The section type (listening, reading, writing, speaking)
     */
    public string $section;

    /**
     * The score data from the test
     */
    public array $scoreData;

    /**
     * Whether this test is part of a full test
     */
    public bool $isPartOfFullTest;

    /**
     * The full test attempt ID (if part of full test)
     */
    public ?int $fullTestAttemptId;

    /**
     * Create a new event instance.
     */
    public function __construct(
        StudentAttempt $attempt,
        User $user,
        string $section,
        array $scoreData = [],
        bool $isPartOfFullTest = false,
        ?int $fullTestAttemptId = null
    ) {
        $this->attempt = $attempt;
        $this->user = $user;
        $this->section = $section;
        $this->scoreData = $scoreData;
        $this->isPartOfFullTest = $isPartOfFullTest;
        $this->fullTestAttemptId = $fullTestAttemptId;
    }

    /**
     * Get the band score from score data
     */
    public function getBandScore(): ?float
    {
        return $this->scoreData['band_score'] ?? null;
    }

    /**
     * Get correct answers count
     */
    public function getCorrectAnswers(): int
    {
        return $this->scoreData['correct_answers'] ?? $this->attempt->correct_answers ?? 0;
    }

    /**
     * Get total questions count
     */
    public function getTotalQuestions(): int
    {
        return $this->scoreData['total_questions'] ?? $this->attempt->total_questions ?? 0;
    }

    /**
     * Check if the test requires human evaluation (writing/speaking)
     */
    public function requiresHumanEvaluation(): bool
    {
        return in_array($this->section, ['writing', 'speaking']);
    }
}
