<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use App\Models\OtpVerification;
use App\Notifications\EmailVerificationNotification;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $oldEmail = $user->email;
        $newEmail = $request->input('email');
        
        // Process phone number with country code
        if ($request->filled('phone_number') && $request->filled('phone_country_code')) {
            $phoneNumber = $request->input('phone_country_code') . ' ' . $request->input('phone_number');
            $request->merge(['phone_number' => $phoneNumber]);
        }
        
        // Get all countries from helper
        $countries = \App\Helpers\CountryHelper::getAllCountries();
        
        // Set country name based on country code
        if ($request->filled('country_code')) {
            $countryCode = $request->input('country_code');
            $countryName = isset($countries[$countryCode]) ? $countries[$countryCode]['name'] : null;
            $request->merge(['country_name' => $countryName]);
        }
        
        // SECURITY (C4): mass-assign ONLY an explicit whitelist of profile fields.
        // Previously used $request->except(...), which passed every posted key (including
        // is_admin, role_id, branch_id, banned_at, ...) into the fillable User model,
        // allowing a normal user to escalate privileges via PATCH /profile.
        $profileData = $request->only(['name', 'phone_number', 'country_code', 'country_name']);

        // Check if email is being changed
        if ($oldEmail !== $newEmail) {
            // Check if new email is already taken
            if (\App\Models\User::where('email', $newEmail)->where('id', '!=', $user->id)->exists()) {
                return back()->withErrors(['email' => 'This email address is already taken.']);
            }
            
            // Store the new email in a temporary field or session
            session(['pending_email' => $newEmail]);
            
            // Generate and send OTP
            $this->sendEmailVerificationOtp($user, $newEmail);
            
            // Update other fields except email (email is deferred until OTP verification)
            $user->fill($profileData);
            $user->save();
            
            return Redirect::route('profile.edit')->with('status', 'email-verification-sent');
        }
        
        // If email is not being changed, update normally (email unchanged here)
        $user->fill($profileData);
        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }
    
    /**
     * Send email verification OTP
     */
    protected function sendEmailVerificationOtp($user, $newEmail)
    {
        // Delete any existing OTPs for this email
        OtpVerification::where('identifier', $newEmail)->delete();
        
        // Generate new OTP
        $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Store OTP in database
        DB::table('otp_verifications')->insert([
            'identifier' => $newEmail,
            'token' => hash('sha256', $otp),
            'otp_code' => $otp,  // Store the actual OTP code
            'type' => 'email',  // Required: must be 'email' or 'phone'
            'expires_at' => now()->addMinutes(10),
            'attempts' => 0,  // Initialize attempts counter
            'verified_at' => null,  // Not verified yet
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Send OTP via email
        try {
            \Illuminate\Support\Facades\Mail::to($newEmail)->send(new \App\Mail\EmailChangeOtpMail($otp, $user->name));
        } catch (\Exception $e) {
            \Log::error('Failed to send email change OTP: ' . $e->getMessage());
        }
    }
    
    /**
     * Verify email change OTP
     */
    public function verifyEmailChange(Request $request): RedirectResponse
    {
        $request->validate([
            'otp' => ['required', 'string', 'size:6'],
        ]);
        
        $pendingEmail = session('pending_email');
        
        if (!$pendingEmail) {
            return back()->withErrors(['otp' => 'No pending email change found.']);
        }
        
        // Find the OTP record
        $otpRecord = DB::table('otp_verifications')
            ->where('identifier', $pendingEmail)
            ->where('expires_at', '>', now())
            ->first();
        
        if (!$otpRecord) {
            return back()->withErrors(['otp' => 'Invalid or expired OTP.']);
        }
        
        // Verify OTP
        if (!hash_equals($otpRecord->token, hash('sha256', $request->otp))) {
            return back()->withErrors(['otp' => 'Invalid OTP code.']);
        }
        
        // Update user's email
        $user = $request->user();
        $user->email = $pendingEmail;
        $user->email_verified_at = now();
        $user->save();
        
        // Delete OTP record and clear session
        DB::table('otp_verifications')->where('id', $otpRecord->id)->delete();
        session()->forget('pending_email');
        
        return Redirect::route('profile.edit')->with('status', 'email-updated');
    }
    
    /**
     * Resend email change OTP
     */
    public function resendEmailChangeOtp(Request $request): RedirectResponse
    {
        $pendingEmail = session('pending_email');
        
        if (!$pendingEmail) {
            return back()->withErrors(['error' => 'No pending email change found.']);
        }
        
        // Check rate limiting (max 1 resend per minute)
        $lastOtp = DB::table('otp_verifications')
            ->where('identifier', $pendingEmail)
            ->where('created_at', '>', now()->subMinute())
            ->first();
        
        if ($lastOtp) {
            return back()->withErrors(['error' => 'Please wait a minute before requesting another OTP.']);
        }
        
        // Send new OTP
        $this->sendEmailVerificationOtp($request->user(), $pendingEmail);
        
        return back()->with('status', 'otp-resent');
    }
    
    /**
     * Cancel email change
     */
    public function cancelEmailChange(Request $request): RedirectResponse
    {
        $pendingEmail = session('pending_email');
        
        // Delete any pending OTPs
        if ($pendingEmail) {
            DB::table('otp_verifications')->where('identifier', $pendingEmail)->delete();
        }
        
        session()->forget('pending_email');
        
        return Redirect::route('profile.edit')->with('status', 'email-change-cancelled');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current-password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Update user avatar
     */
    public function updateAvatar(Request $request): RedirectResponse
    {
        try {
            $request->validate([
                'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'], // Max 2MB
            ]);

            $user = $request->user();
            $r2Url = config('filesystems.disks.r2.url');

            // Delete old avatar if exists on R2
            if ($user->avatar_url && str_contains($user->avatar_url, 'cdn.cdielts.org')) {
                $oldPath = str_replace($r2Url . '/', '', $user->avatar_url);
                Storage::disk('r2')->delete($oldPath);
            }
            // Delete old avatar from local storage if exists
            elseif ($user->avatar_url && !filter_var($user->avatar_url, FILTER_VALIDATE_URL)) {
                $oldPath = str_replace('/storage/', '', $user->avatar_url);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            // Generate unique filename
            $filename = 'avatars/' . $user->id . '_' . time() . '.' . $request->file('avatar')->getClientOriginalExtension();

            // Store new avatar on R2
            Storage::disk('r2')->put($filename, file_get_contents($request->file('avatar')), 'public');

            // Get the CDN URL
            $avatarUrl = $r2Url . '/' . $filename;

            $user->update([
                'avatar_url' => $avatarUrl
            ]);

            return back()->with('status', 'profile-updated');

        } catch (\Exception $e) {
            \Log::error('Avatar upload error: ' . $e->getMessage());
            return back()->withErrors(['avatar' => 'Failed to upload avatar. Please try again.']);
        }
    }
    
    /**
     * Update notification preferences
     */
    public function updateNotifications(Request $request): RedirectResponse
    {
        $preferences = [
            'test_reminders' => $request->boolean('test_reminders'),
            'score_updates' => $request->boolean('score_updates'),
            'achievement_alerts' => $request->boolean('achievement_alerts'),
            'marketing_emails' => $request->boolean('marketing_emails'),
        ];
        
        $request->user()->update([
            'notification_preferences' => $preferences
        ]);
        
        return redirect()->route('profile.edit')
            ->with('success', 'Notification preferences updated!');
    }
}
