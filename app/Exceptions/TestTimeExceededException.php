<?php

namespace App\Exceptions;

/**
 * Exception thrown when test time limit is exceeded
 *
 * Examples:
 * - Reading test submitted after 60 minutes
 * - Listening test submitted after 40 minutes
 */
class TestTimeExceededException extends TestException
{
    protected string $redirectRoute = 'student.results';

    protected int $allowedMinutes;
    protected int $actualMinutes;

    public function __construct(
        string $message = 'Test time limit exceeded.',
        int $code = 422,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Create exception for exceeded time limit
     */
    public static function exceeded(string $testType, int $allowedMinutes, int $actualMinutes): self
    {
        $exception = new self(
            "Your {$testType} test time has exceeded the allowed {$allowedMinutes} minutes. " .
            "Time taken: {$actualMinutes} minutes. Your answers have been submitted but marked as overtime."
        );

        $exception->allowedMinutes = $allowedMinutes;
        $exception->actualMinutes = $actualMinutes;

        return $exception;
    }

    /**
     * Create exception for reading test
     */
    public static function readingExceeded(int $actualMinutes): self
    {
        return self::exceeded('reading', 60, $actualMinutes);
    }

    /**
     * Create exception for listening test
     */
    public static function listeningExceeded(int $actualMinutes): self
    {
        return self::exceeded('listening', 40, $actualMinutes);
    }

    /**
     * Get allowed minutes
     */
    public function getAllowedMinutes(): int
    {
        return $this->allowedMinutes ?? 0;
    }

    /**
     * Get actual minutes taken
     */
    public function getActualMinutes(): int
    {
        return $this->actualMinutes ?? 0;
    }
}
