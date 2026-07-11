<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Handles test session management - keep-alive, recovery, etc.
 *
 * This controller is critical for long-running tests (Full Tests can be 2.5+ hours)
 * to prevent session timeout and data loss.
 */
class TestSessionController extends Controller
{
    /**
     * Keep session alive during test.
     * Called every 5 minutes from the test interface via JavaScript.
     *
     * This prevents session expiry during long tests while also
     * returning useful status information to the client.
     */
    public function keepAlive(Request $request): JsonResponse
    {
        // Session is automatically refreshed by Laravel middleware
        // Just return success with some useful info

        $user = auth()->user();

        return response()->json([
            'success' => true,
            'timestamp' => now()->toIso8601String(),
            'session_active' => true,
            'user_id' => $user ? $user->id : null,
            'expires_in' => config('session.lifetime') * 60, // seconds
        ]);
    }

    /**
     * Check if user is still authenticated.
     * Used before auto-submit to ensure session is valid.
     */
    public function checkAuth(Request $request): JsonResponse
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'authenticated' => false,
                'message' => 'Session expired',
            ], 401);
        }

        return response()->json([
            'authenticated' => true,
            'user_id' => $user->id,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Emergency save endpoint - saves all form data to attempt's draft_answers.
     * Called when network issues are detected or before page unload.
     */
    public function emergencySave(Request $request): JsonResponse
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Not authenticated',
            ], 401);
        }

        $attemptId = $request->input('attempt_id');
        $answers = $request->input('answers', []);
        $section = $request->input('section');

        if (!$attemptId) {
            return response()->json([
                'success' => false,
                'message' => 'Missing attempt_id',
            ], 400);
        }

        try {
            $attempt = \App\Models\StudentAttempt::where('id', $attemptId)
                ->where('user_id', $user->id)
                ->where('status', 'in_progress')
                ->first();

            if (!$attempt) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attempt not found or already completed',
                ], 404);
            }

            // Save to draft_answers
            $attempt->update([
                'draft_answers' => $answers,
                'draft_saved_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Emergency save successful',
                'saved_at' => now()->toIso8601String(),
            ]);

        } catch (\Exception $e) {
            \Log::error('Emergency save failed', [
                'attempt_id' => $attemptId,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Save failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get network status and server time for sync.
     */
    public function status(Request $request): JsonResponse
    {
        return response()->json([
            'online' => true,
            'server_time' => now()->toIso8601String(),
            'session_lifetime' => config('session.lifetime'),
        ]);
    }
}
