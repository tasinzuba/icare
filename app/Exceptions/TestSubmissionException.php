<?php

namespace App\Exceptions;

/**
 * Exception thrown when test submission fails
 *
 * Examples:
 * - No answers provided
 * - Invalid answer format
 * - Submission timeout
 */
class TestSubmissionException extends TestException
{
    protected string $redirectRoute = 'student.results.index';

    public function __construct(
        string $message = 'Failed to submit test.',
        int $code = 400,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Create exception for no answers provided
     */
    public static function noAnswers(): self
    {
        return new self('No answers were provided. Please answer at least one question.');
    }

    /**
     * Create exception for invalid answer format
     */
    public static function invalidFormat(string $details = ''): self
    {
        $message = 'Invalid answer format.';
        if ($details) {
            $message .= " {$details}";
        }
        return new self($message);
    }

    /**
     * Create exception for database error during submission
     */
    public static function databaseError(): self
    {
        return new self('Failed to save your answers. Please try again.', 500);
    }
}
