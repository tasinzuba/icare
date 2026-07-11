<?php

namespace App\Exceptions;

/**
 * Exception thrown when user cannot access a test
 *
 * Examples:
 * - Test is premium and user doesn't have subscription
 * - Test is inactive
 * - Test belongs to wrong section
 */
class TestAccessDeniedException extends TestException
{
    protected string $redirectRoute = 'welcome';

    public function __construct(
        string $message = 'You do not have access to this test.',
        int $code = 403,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Create exception for premium test without subscription
     */
    public static function premiumRequired(): self
    {
        $exception = new self('This test is available for premium users only. Please upgrade your subscription.');
        $exception->redirectRoute = 'welcome';
        return $exception;
    }

    /**
     * Create exception for inactive test
     */
    public static function testInactive(): self
    {
        $exception = new self('This test is currently not available.');
        $exception->redirectRoute = 'student.dashboard';
        return $exception;
    }

    /**
     * Create exception for wrong section
     */
    public static function wrongSection(string $expectedSection): self
    {
        $exception = new self("This test does not belong to the {$expectedSection} section.");
        $exception->redirectRoute = "student.{$expectedSection}.index";
        return $exception;
    }

    /**
     * Create exception for wrong student type (offline vs online)
     */
    public static function wrongStudentType(string $section): self
    {
        $exception = new self('This test is not available for your student type.');
        $exception->redirectRoute = "student.{$section}.index";
        return $exception;
    }
}
