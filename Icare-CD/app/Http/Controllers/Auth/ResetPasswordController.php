<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\UserDevice;
use App\Services\LocationService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

class ResetPasswordController extends Controller
{
    protected $locationService;

    public function __construct(LocationService $locationService)
    {
        $this->locationService = $locationService;
    }

    /**
     * Show the reset password form
     */
    public function showResetForm(Request $request, $token)
    {
        // Validate token exists and is not expired
        $email = $request->email;

        if (!$email) {
            return view('auth.password-reset-expired', [
                'title' => 'Invalid Reset Link',
                'message' => 'This password reset link is invalid. Please request a new password reset link from the login page.'
            ]);
        }

        // Check if token is valid
        $tokenData = \DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (!$tokenData) {
            return view('auth.password-reset-expired', [
                'title' => 'Link Expired or Already Used',
                'message' => 'This password reset link has expired or has already been used. For security reasons, each reset link can only be used once.'
            ]);
        }

        // Check if token matches
        if (!Hash::check($token, $tokenData->token)) {
            return view('auth.password-reset-expired', [
                'title' => 'Invalid Reset Link',
                'message' => 'This password reset link is invalid or corrupted. Please request a new password reset link.'
            ]);
        }

        // Check if token is expired (default 60 minutes)
        $expiration = config('auth.passwords.users.expire', 60);
        $createdAt = \Carbon\Carbon::parse($tokenData->created_at);

        if ($createdAt->addMinutes($expiration)->isPast()) {
            // Delete expired token
            \DB::table('password_reset_tokens')->where('email', $email)->delete();

            return view('auth.password-reset-expired', [
                'title' => 'Link Expired',
                'message' => 'This password reset link has expired. Password reset links are valid for 60 minutes only. Please request a new one.'
            ]);
        }

        return view('auth.reset-password', [
            'token' => $token,
            'email' => $email
        ]);
    }

    /**
     * Reset the password
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));

                // Send security notification
                $this->sendSecurityNotification($user, $request);

                // Clear all device sessions except current
                $user->devices()->delete();

                // Delete the password reset token to prevent reuse
                \DB::table('password_reset_tokens')
                    ->where('email', $user->email)
                    ->delete();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', 'Your password has been reset successfully! Please login with your new password.');
        }

        return back()->withErrors(['email' => [__($status)]]);
    }

    /**
     * Send security notification about password change
     */
    private function sendSecurityNotification($user, $request)
    {
        $locationData = $this->locationService->getLocation($request->ip());
        
        $user->notify(new \App\Notifications\PasswordChangedNotification([
            'ip' => $request->ip(),
            'browser' => $request->userAgent(),
            'location' => $locationData['cityName'] . ', ' . $locationData['countryName'],
            'time' => now(),
        ]));
    }
}