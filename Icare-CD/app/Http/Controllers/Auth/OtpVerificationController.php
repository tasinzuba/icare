<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\OtpVerification;
use App\Models\UserDevice;
use App\Services\LocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class OtpVerificationController extends Controller
{
    protected $locationService;

    public function __construct(LocationService $locationService)
    {
        $this->locationService = $locationService;
    }

    public function show(Request $request)
    {
        $email = $request->query('email');

        if (!$email || !session('otp_session')) {
            return redirect()->route('login');
        }

        // Fetch the latest OTP for this email
        $otp = OtpVerification::where('identifier', $email)
            ->where('type', 'email')
            ->latest()
            ->first();

        // Calculate expiry timestamp (fallback to 5 minutes if no OTP found)
        $expiresAt = $otp ? $otp->expires_at->timestamp : now()->addMinutes(5)->timestamp;

        return view('auth.verify-otp', [
            'email' => $email,
            'expiresAt' => $expiresAt,
            'resend_after' => 60, // seconds
        ]);
    }

    public function verify(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|min:6|max:8', // Support both old 6-digit and new 8-char OTPs
        ]);

        // SECURITY FIX: Rate limiting per IP + email combination
        $rateLimitKey = 'otp-verify:' . $request->ip() . ':' . $request->email;
        if (RateLimiter::tooManyAttempts($rateLimitKey, 10)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            return back()->withErrors([
                'otp' => "Too many verification attempts. Please try again in {$seconds} seconds."
            ]);
        }

        // Find the latest OTP for this email (without checking code first)
        $otp = OtpVerification::where('identifier', $request->email)
            ->where('type', 'email')
            ->whereNull('verified_at')
            ->latest()
            ->first();

        if (!$otp) {
            RateLimiter::hit($rateLimitKey, 300); // 5 minutes
            return back()->withErrors(['otp' => 'No pending OTP found. Please request a new one.']);
        }

        // SECURITY FIX: Check lockout status
        if ($otp->isLockedOut()) {
            $remainingSeconds = $otp->getLockoutRemainingSeconds();
            if ($remainingSeconds > 0) {
                $remainingMinutes = ceil($remainingSeconds / 60);
                return back()->withErrors([
                    'otp' => "Account temporarily locked due to too many failed attempts. Please try again in {$remainingMinutes} minute(s)."
                ]);
            }
            // Lockout expired, reset attempts
            $otp->update(['attempts' => 0]);
        }

        if ($otp->isExpired()) {
            return back()->withErrors(['otp' => 'OTP has expired. Please request a new one.']);
        }

        // SECURITY FIX: Use timing-safe comparison to prevent timing attacks
        if (!hash_equals($otp->otp_code, strtoupper($request->otp))) {
            // Increment attempts on failed verification
            $otp->incrementAttempts();
            RateLimiter::hit($rateLimitKey, 300);

            $remainingAttempts = OtpVerification::MAX_ATTEMPTS - $otp->attempts;

            if ($remainingAttempts <= 0) {
                return back()->withErrors([
                    'otp' => 'Too many failed attempts. Please wait ' . OtpVerification::LOCKOUT_MINUTES . ' minutes or request a new OTP.'
                ]);
            }

            return back()->withErrors([
                'otp' => "Invalid OTP code. {$remainingAttempts} attempt(s) remaining."
            ]);
        }

        // Mark OTP as verified
        $otp->markAsVerified();

        // Mark email as verified
        $user = User::where('email', $request->email)->first();
        $user->markEmailAsVerified();

        // Login user
        Auth::login($user);
        
        // Update last login time
        $user->update(['last_login_at' => now()]);

        // Track device
        $this->trackLoginDevice($request, $user);

        // Clear session
        session()->forget(['otp_session', 'registration_data']);

        return redirect()->intended(route('student.dashboard'))
            ->with('success', 'Email verified successfully!');
    }

    public function resend(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        // Rate limiting
        $key = 'otp-resend:' . $request->email;
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->withErrors([
                'email' => "Too many requests. Please try again in {$seconds} seconds."
            ]);
        }

        RateLimiter::hit($key, 60); // 1 minute decay

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return back()->withErrors(['email' => 'User not found.']);
        }

        // Create new OTP
        $otp = OtpVerification::createForEmail($user->email);
        
        // Send OTP
        $user->notify(new \App\Notifications\OtpNotification($otp));

        return back()->with('success', 'New OTP sent to your email.');
    }

    private function trackLoginDevice(Request $request, User $user)
    {
        try {
            $locationData = $this->locationService->getLocation($request->ip());
            $device = UserDevice::createFromRequest($request, $user->id, $locationData);

            // Check if this is a new device
            if ($device->wasRecentlyCreated) {
                // Send new device notification only if the class exists
                if (class_exists('\App\Notifications\NewDeviceNotification')) {
                    $user->notify(new \App\Notifications\NewDeviceNotification($device));
                }
            } else {
                // Update last activity
                $device->updateActivity();
            }

            // Store device fingerprint in session
            session(['device_fingerprint' => $device->device_fingerprint]);
        } catch (\Exception $e) {
            // Log the error but don't fail the login
            \Log::error('Device tracking failed during OTP verification: ' . $e->getMessage());
            // Continue without device tracking
        }
    }
}