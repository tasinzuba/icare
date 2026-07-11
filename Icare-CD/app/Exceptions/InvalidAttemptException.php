<?php

namespace App\Exceptions;

/**
 * Exception thrown when an attempt is invalid
 *
 * Examples:
 * - Attempt belongs to another user
 * - Attempt already completed
 * - Attempt not found
 */
class InvalidAttemptException extends TestException
{
    protected string $redirectRoute = 'student.results.index';

    public function __construct(
        string $message = 'Invalid test attempt.',
        int $code = 403,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Create exception for attempt belonging to another user
     */
    public static function notOwner(): self
    {
        return new self('You do not have permission to access this test attempt.');
    }

    /**
     * Create exception for already completed attempt
     */
    public static function alreadyCompleted(): self
    {
        return new self('This test has already been submitted.');
    }

    /**
     * Create exception for attempt not found
     */
    public static function notFound(): self
    {
        return new self('Test attempt not found.', 404);
    }
}
