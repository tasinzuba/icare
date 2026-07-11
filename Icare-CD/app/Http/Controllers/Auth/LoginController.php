<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\UserDevice;
use App\Services\LocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    protected $locationService;

    public function __construct(LocationService $locationService)
    {
        $this->locationService = $locationService;
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        // Rate limiting
        $this->checkRateLimit($request);

        // Attempt login with email or phone
        $loginField = filter_var($request->email, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone_number';
        
        $credentials = [
            $loginField => $request->email,
            'password' => $request->password,
        ];

        // Remember me functionality - will keep user logged in for 30 days if checked
        $remember = $request->filled('remember');
        
        if (!Auth::attempt($credentials, $remember)) {
            $this->incrementRateLimiters($request);

            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'These credentials do not match our records.']);
        }

        // Clear rate limiters on successful login
        $this->clearRateLimiters($request);

        $user = Auth::user();
        
        // Update last login time
        $user->update(['last_login_at' => now()]);

        // Auto-verify email on login (OTP/email verification disabled)
        if (!$user->hasVerifiedEmail()) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        // Device tracking (without trust device feature)
        try {
            $this->handleDeviceTracking($request, $user);
        } catch (\Exception $e) {
            // Log error but don't fail login
            \Log::error('Device tracking failed: ' . $e->getMessage());
        }

        // Regenerate session for security
        $request->session()->regenerate();

        return redirect()->intended($this->redirectPath());
    }

    private function handleDeviceTracking(Request $request, $user)
    {
        try {
            $locationData = $this->locationService->getLocation($request->ip());
            $device = UserDevice::createFromRequest($request, $user->id, $locationData);

            // Check if new device
            if ($device->wasRecentlyCreated) {
                // Send notification for new device (only if class exists)
                if (class_exists('\App\Notifications\NewDeviceNotification')) {
                    $user->notify(new \App\Notifications\NewDeviceNotification($device));
                }
            } else {
                $device->updateActivity();
            }

            // Store device info in session
            session(['device_id' => $device->id, 'device_fingerprint' => $device->device_fingerprint]);
        } catch (\Exception $e) {
            // Don't fail login if device tracking fails
            \Log::error('Device tracking error: ' . $e->getMessage());
        }
    }

    private function checkRateLimit(Request $request)
    {
        // Check both IP-based and email-based rate limits
        $ipKey = $this->throttleKeyByIp($request);
        $emailKey = $this->throttleKeyByEmail($request);

        // IP-based: 10 attempts per minute (防止单IP大量尝试)
        if (RateLimiter::tooManyAttempts($ipKey, 10)) {
            $seconds = RateLimiter::availableIn($ipKey);

            throw \Illuminate\Validation\ValidationException::withMessages([
                'email' => ["Too many login attempts from your IP. Please try again in {$seconds} seconds."],
            ]);
        }

        // Email-based: 5 attempts per 15 minutes (防止针对特定账户的暴力破解)
        if (RateLimiter::tooManyAttempts($emailKey, 5)) {
            $seconds = RateLimiter::availableIn($emailKey);

            throw \Illuminate\Validation\ValidationException::withMessages([
                'email' => ["Too many login attempts for this account. Please try again in {$seconds} seconds or reset your password."],
            ]);
        }
    }

    /**
     * Increment rate limiters on failed attempt
     */
    private function incrementRateLimiters(Request $request): void
    {
        RateLimiter::hit($this->throttleKeyByIp($request), 60); // 1 minute decay
        RateLimiter::hit($this->throttleKeyByEmail($request), 900); // 15 minutes decay
    }

    /**
     * Clear rate limiters on successful login
     */
    private function clearRateLimiters(Request $request): void
    {
        RateLimiter::clear($this->throttleKeyByIp($request));
        RateLimiter::clear($this->throttleKeyByEmail($request));
    }

    /**
     * IP-based throttle key
     */
    private function throttleKeyByIp(Request $request): string
    {
        return 'login_ip:' . $request->ip();
    }

    /**
     * Email-based throttle key (normalized)
     */
    private function throttleKeyByEmail(Request $request): string
    {
        return 'login_email:' . Str::lower($request->input('email'));
    }

    /**
     * Combined throttle key (backward compatibility)
     */
    private function throttleKey(Request $request): string
    {
        return 'login:' . Str::lower($request->input('email')) . '|' . $request->ip();
    }

    private function redirectPath(): string
    {
        $user = auth()->user();
        
        // Check if admin
        if ($user->is_admin) {
            return route('admin.dashboard');
        }
        
        // Check if teacher
        if ($user->teacher()->exists()) {
            return route('teacher.dashboard');
        }
        
        // Default to student dashboard
        return route('student.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/');
    }
}