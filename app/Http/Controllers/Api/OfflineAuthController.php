<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OfflineEnrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class OfflineAuthController extends Controller
{
    /**
     * Login offline student and return Sanctum token
     * POST /api/offline/login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Rate limiting
        $key = 'offline-api-login:' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'message' => "Too many login attempts. Try again in {$seconds} seconds."
            ], 429);
        }

        // Attempt authentication
        if (!Auth::attempt($request->only('email', 'password'))) {
            RateLimiter::hit($key);
            return response()->json([
                'message' => 'Invalid email or password.'
            ], 401);
        }

        $user = Auth::user();

        // Must be offline student
        if ($user->student_type !== 'offline') {
            Auth::logout();
            return response()->json([
                'message' => 'This login is for offline students only.'
            ], 403);
        }

        // Must have active enrollment
        $enrollment = OfflineEnrollment::where('user_id', $user->id)
            ->where('status', 'active')
            ->where('valid_until', '>=', now()->toDateString())
            ->first();

        if (!$enrollment) {
            Auth::logout();
            return response()->json([
                'message' => 'Your enrollment has expired or is inactive. Contact your branch.'
            ], 403);
        }

        // Clear rate limiter
        RateLimiter::clear($key);

        // Update last login
        $user->update(['last_login_at' => now()]);

        // Create Sanctum token
        $token = $user->createToken('desktop-app')->plainTextToken;

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'student_type' => $user->student_type,
            ],
            'token' => $token,
        ]);
    }

    /**
     * Logout — revoke current token
     * POST /api/offline/logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out']);
    }
}
