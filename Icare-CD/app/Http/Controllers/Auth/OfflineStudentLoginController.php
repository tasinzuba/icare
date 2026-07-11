<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\OfflineEnrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class OfflineStudentLoginController extends Controller
{
    /**
     * Show the offline student login form
     */
    public function showLoginForm()
    {
        // Redirect if already logged in as offline student
        if (auth()->check()) {
            $user = auth()->user();

            if ($user->student_type === 'offline') {
                return redirect()->route('offline.dashboard');
            }

            // If admin or branch staff, redirect appropriately
            if ($user->is_admin || $user->role_id) {
                return redirect()->route('admin.dashboard');
            }

            // Online student
            return redirect()->route('student.dashboard');
        }

        return view('auth.offline-student-login');
    }

    /**
     * Handle offline student login request
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Rate limiting
        $this->checkRateLimit($request);

        $credentials = [
            'email' => $request->email,
            'password' => $request->password,
        ];

        // Remember me functionality
        $remember = $request->filled('remember');

        if (!Auth::attempt($credentials, $remember)) {
            RateLimiter::hit($this->throttleKey($request));

            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Invalid email or password.']);
        }

        $user = Auth::user();

        // Check if user is an offline student
        if ($user->student_type !== 'offline') {
            Auth::logout();

            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'This login is for offline students only. Please use the appropriate login page.']);
        }

        // Check if user has active enrollment
        $hasActiveEnrollment = OfflineEnrollment::where('user_id', $user->id)
            ->where('status', 'active')
            ->where('valid_until', '>=', now()->toDateString())
            ->exists();

        if (!$hasActiveEnrollment) {
            Auth::logout();

            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Your enrollment has expired or is inactive. Please contact your branch.']);
        }

        // Clear rate limiter
        RateLimiter::clear($this->throttleKey($request));

        // Update last login time
        $user->update(['last_login_at' => now()]);

        // Regenerate session for security
        $request->session()->regenerate();

        return redirect()->intended(route('offline.dashboard'));
    }

    /**
     * Check rate limit for login attempts
     */
    private function checkRateLimit(Request $request)
    {
        $key = $this->throttleKey($request);

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            throw \Illuminate\Validation\ValidationException::withMessages([
                'email' => ["Too many login attempts. Please try again in {$seconds} seconds."],
            ]);
        }
    }

    /**
     * Get the rate limiting throttle key
     */
    private function throttleKey(Request $request): string
    {
        return 'offline-login:' . $request->ip();
    }
}
