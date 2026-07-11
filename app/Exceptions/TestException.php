<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Base exception for all test-related errors
 */
abstract class TestException extends Exception
{
    /**
     * The redirect route for this exception
     */
    protected string $redirectRoute = 'student.dashboard';

    /**
     * Flash message type (error, warning, info)
     */
    protected string $messageType = 'error';

    /**
     * Get the redirect route
     */
    public function getRedirectRoute(): string
    {
        return $this->redirectRoute;
    }

    /**
     * Get the message type
     */
    public function getMessageType(): string
    {
        return $this->messageType;
    }

    /**
     * Render the exception as an HTTP response
     */
    public function render(Request $request): RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $this->getMessage(),
                'error_type' => class_basename($this),
            ], $this->getCode() ?: 400);
        }

        return redirect()
            ->route($this->redirectRoute)
            ->with($this->messageType, $this->getMessage());
    }
}
