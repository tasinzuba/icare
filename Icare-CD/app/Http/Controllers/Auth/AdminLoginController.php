<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\UserDevice;
use App\Services\LocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class AdminLoginController extends Controller
{
    protected $locationService;

    public function __construct(LocationService $locationService)
    {
        $this->locationService = $locationService;
    }

    /**
     * Show the admin login form
     */
    public function showLoginForm()
    {
        // Redirect if already logged in as admin
        if (auth()->check()) {
            $user = auth()->user();

            // Admin users go to admin panel (even if also branch staff)
            if ($user->is_admin || $user->role_id) {
                return redirect()->route('admin.dashboard');
            }

            // Branch staff (non-admin) go to their panel
            if ($user->isBranchStaff()) {
                return redirect()->route('branch.dashboard');
            }
        }

        return view('auth.admin-login');
    }

    /**
     * Handle admin login request
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
                ->withErrors(['email' => 'These credentials do not match our records.']);
        }

        $user = Auth::user();

        // Check if user is admin or has admin role FIRST
        // (Admin users who are also branch staff should still access admin panel)
        if (!$user->is_admin && !$user->role_id) {
            // Not an admin — check if branch staff (redirect them)
            if ($user->isBranchStaff()) {
                Auth::logout();

                return redirect()->route('branch.login')
                    ->withInput($request->only('email'))
                    ->with('error', 'Branch staff must use the Branch Admin login page.');
            }
        }

        // Not admin and not branch staff
        if (!$user->is_admin && !$user->role_id) {
            Auth::logout();

            return redirect()->route('login')
                ->withInput($request->only('email'))
                ->with('error', 'You are not an administrator. Please use the student login page.')
                ->with('info', 'Redirected to student login page.');
        }

        // Clear rate limiter
        RateLimiter::clear($this->throttleKey($request));

        // Update last login time
        $user->update(['last_login_at' => now()]);

        // Auto-verify email on login (OTP/email verification disabled)
        if (!$user->hasVerifiedEmail()) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        // Device tracking
        try {
            $this->handleDeviceTracking($request, $user);
        } catch (\Exception $e) {
            \Log::error('Device tracking failed: ' . $e->getMessage());
        }

        // Regenerate session for security
        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    /**
     * Handle device tracking for admin login
     */
    private function handleDeviceTracking(Request $request, $user)
    {
        try {
            $locationData = $this->locationService->getLocation($request->ip());
            $device = UserDevice::createFromRequest($request, $user->id, $locationData);

            if ($device->wasRecentlyCreated) {
                if (class_exists('\App\Notifications\NewDeviceNotification')) {
                    $user->notify(new \App\Notifications\NewDeviceNotification($device));
                }
            } else {
                $device->updateActivity();
            }

            session(['device_id' => $device->id, 'device_fingerprint' => $device->device_fingerprint]);
        } catch (\Exception $e) {
            \Log::error('Device tracking error: ' . $e->getMessage());
        }
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
        return 'admin-login:' . $request->ip();
    }
}
