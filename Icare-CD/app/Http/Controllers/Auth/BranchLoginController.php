<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\BranchStaff;
use App\Models\UserDevice;
use App\Services\LocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class BranchLoginController extends Controller
{
    protected $locationService;

    public function __construct(LocationService $locationService)
    {
        $this->locationService = $locationService;
    }

    /**
     * Show the branch login form
     */
    public function showLoginForm()
    {
        // Redirect if already logged in as branch staff
        if (auth()->check()) {
            $user = auth()->user();

            // Check if user is branch staff
            $branchStaff = BranchStaff::where('user_id', $user->id)
                ->where('active', true)
                ->first();

            if ($branchStaff) {
                return redirect()->route('branch.dashboard');
            }

            // If admin, redirect to admin dashboard
            if ($user->is_admin || $user->role_id) {
                return redirect()->route('admin.dashboard');
            }
        }

        return view('auth.branch-login');
    }

    /**
     * Handle branch login request
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

        // Check if user is active branch staff
        $branchStaff = BranchStaff::where('user_id', $user->id)
            ->where('active', true)
            ->first();

        if (!$branchStaff) {
            Auth::logout();

            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'You are not authorized to access the branch panel. Please contact your administrator.']);
        }

        // Clear rate limiter
        RateLimiter::clear($this->throttleKey($request));

        // Update last login time
        $user->update(['last_login_at' => now()]);

        // Device tracking
        try {
            $this->handleDeviceTracking($request, $user);
        } catch (\Exception $e) {
            \Log::error('Device tracking failed: ' . $e->getMessage());
        }

        // Regenerate session for security
        $request->session()->regenerate();

        return redirect()->intended(route('branch.dashboard'));
    }

    /**
     * Handle device tracking for branch login
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
        return 'branch-login:' . $request->ip();
    }
}
