<?php

namespace App\Exceptions;

/**
 * Exception thrown when a test doesn't have enough questions
 *
 * Examples:
 * - Writing test needs 2 questions (Task 1 & 2)
 * - Test set is empty
 */
class InsufficientQuestionsException extends TestException
{
    protected string $redirectRoute = 'student.dashboard';
    protected string $messageType = 'warning';

    public function __construct(
        string $message = 'This test does not have enough questions.',
        int $code = 400,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Create exception for writing test without enough questions
     */
    public static function writingTest(int $found, int $required = 2): self
    {
        $exception = new self("This writing test needs at least {$required} questions. Found: {$found}");
        $exception->redirectRoute = 'student.writing.index';
        return $exception;
    }

    /**
     * Create exception for empty test set
     */
    public static function emptyTestSet(string $section): self
    {
        $exception = new self('This test has no questions configured.');
        $exception->redirectRoute = "student.{$section}.index";
        return $exception;
    }
}
