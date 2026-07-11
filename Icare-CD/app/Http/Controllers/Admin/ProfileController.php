<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Display the admin's profile form.
     */
    public function edit(Request $request): View
    {
        return view('admin.profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the admin's profile information.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone_number' => ['nullable', 'string', 'max:20'],
        ]);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return redirect()->route('admin.profile.edit')->with('success', 'Profile updated successfully!');
    }

    /**
     * Update the admin's password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('admin.profile.edit')->with('success', 'Password updated successfully!');
    }

    /**
     * Update admin avatar
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

            return redirect()->route('admin.profile.edit')->with('success', 'Avatar updated successfully!');

        } catch (\Exception $e) {
            \Log::error('Admin avatar upload error: ' . $e->getMessage());
            return redirect()->route('admin.profile.edit')->withErrors(['avatar' => 'Failed to upload avatar. Please try again.']);
        }
    }

    /**
     * Delete avatar
     */
    public function deleteAvatar(Request $request): RedirectResponse
    {
        $user = $request->user();
        $r2Url = config('filesystems.disks.r2.url');

        // Delete avatar from R2 if exists
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

        $user->update(['avatar_url' => null]);

        return redirect()->route('admin.profile.edit')->with('success', 'Avatar removed successfully!');
    }
}
