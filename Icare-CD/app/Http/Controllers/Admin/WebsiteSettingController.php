<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WebsiteSetting;
use App\Services\CloudflareR2Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class WebsiteSettingController extends Controller
{
    protected CloudflareR2Service $r2Service;

    public function __construct(CloudflareR2Service $r2Service)
    {
        $this->r2Service = $r2Service;
    }
    /**
     * Show the settings form.
     */
    public function index()
    {
        $settings = WebsiteSetting::getSettings();
        return view('admin.settings.website', compact('settings'));
    }

    /**
     * Update the website settings.
     */
    public function update(Request $request)
    {
        Log::info('Website settings update request', ['request' => $request->all()]);
        
        $validated = $request->validate([
            'site_title' => 'required|string|max:255',
            'site_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'dark_mode_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'favicon' => 'nullable|image|mimes:ico,png|max:512',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'facebook_url' => 'nullable|url|max:255',
            'twitter_url' => 'nullable|url|max:255',
            'instagram_url' => 'nullable|url|max:255',
            'youtube_url' => 'nullable|url|max:255',
            'linkedin_url' => 'nullable|url|max:255',
            'footer_text' => 'nullable|string|max:1000',
            'copyright_text' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:160',
            'meta_keywords' => 'nullable|string|max:255',
            'human_evaluation_enabled' => 'nullable|boolean',
        ]);

        $settings = WebsiteSetting::getSettings();

        // Handle logo upload to R2 CDN
        if ($request->hasFile('site_logo')) {
            // Delete old logo from R2 if exists
            if ($settings->site_logo && str_starts_with($settings->site_logo, 'http')) {
                try {
                    $this->r2Service->deleteImage($settings->site_logo);
                } catch (\Exception $e) {
                    Log::warning('Failed to delete old logo from R2', ['error' => $e->getMessage()]);
                }
            }

            // Upload new logo to R2
            $logoUrl = $this->r2Service->uploadImage($request->file('site_logo'), 'branding/logos');
            $validated['site_logo'] = $logoUrl;

            Log::info('Logo uploaded to R2 CDN', ['url' => $logoUrl]);
        }

        // Handle dark mode logo upload to R2 CDN
        if ($request->hasFile('dark_mode_logo')) {
            // Delete old dark mode logo from R2 if exists
            if ($settings->dark_mode_logo && str_starts_with($settings->dark_mode_logo, 'http')) {
                try {
                    $this->r2Service->deleteImage($settings->dark_mode_logo);
                } catch (\Exception $e) {
                    Log::warning('Failed to delete old dark logo from R2', ['error' => $e->getMessage()]);
                }
            }

            // Upload new dark mode logo to R2
            $darkLogoUrl = $this->r2Service->uploadImage($request->file('dark_mode_logo'), 'branding/logos');
            $validated['dark_mode_logo'] = $darkLogoUrl;

            Log::info('Dark mode logo uploaded to R2 CDN', ['url' => $darkLogoUrl]);
        }

        // Handle favicon upload to R2 CDN
        if ($request->hasFile('favicon')) {
            // Delete old favicon from R2 if exists
            if ($settings->favicon && str_starts_with($settings->favicon, 'http')) {
                try {
                    $this->r2Service->deleteImage($settings->favicon);
                } catch (\Exception $e) {
                    Log::warning('Failed to delete old favicon from R2', ['error' => $e->getMessage()]);
                }
            }

            // Upload new favicon to R2
            $faviconUrl = $this->r2Service->uploadImage($request->file('favicon'), 'branding/favicons');
            $validated['favicon'] = $faviconUrl;

            Log::info('Favicon uploaded to R2 CDN', ['url' => $faviconUrl]);
        }

        // Handle meta tags
        $validated['meta_tags'] = [
            'description' => $request->input('meta_description'),
            'keywords' => $request->input('meta_keywords'),
        ];

        $settings->update($validated);
        
        // Clear cache
        Cache::forget('website_settings');

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Website settings updated successfully!']);
        }
        
        return redirect()->route('admin.settings.website')
            ->with('success', 'Website settings updated successfully!');
    }

    /**
     * Remove logo
     */
    public function removeLogo()
    {
        $settings = WebsiteSetting::getSettings();

        if ($settings->site_logo) {
            // Delete from R2 CDN if it's a CDN URL
            if (str_starts_with($settings->site_logo, 'http')) {
                try {
                    $this->r2Service->deleteImage($settings->site_logo);
                } catch (\Exception $e) {
                    Log::warning('Failed to delete logo from R2', ['error' => $e->getMessage()]);
                }
            }

            $settings->update(['site_logo' => null]);
            Cache::forget('website_settings');

            Log::info('Logo removed successfully');
        }

        return redirect()->route('admin.settings.website')
            ->with('success', 'Logo removed successfully!');
    }

    /**
     * Remove dark mode logo
     */
    public function removeDarkModeLogo()
    {
        $settings = WebsiteSetting::getSettings();

        if ($settings->dark_mode_logo) {
            // Delete from R2 CDN if it's a CDN URL
            if (str_starts_with($settings->dark_mode_logo, 'http')) {
                try {
                    $this->r2Service->deleteImage($settings->dark_mode_logo);
                } catch (\Exception $e) {
                    Log::warning('Failed to delete dark logo from R2', ['error' => $e->getMessage()]);
                }
            }

            $settings->update(['dark_mode_logo' => null]);
            Cache::forget('website_settings');

            Log::info('Dark mode logo removed successfully');
        }

        return redirect()->route('admin.settings.website')
            ->with('success', 'Dark mode logo removed successfully!');
    }

    /**
     * Remove favicon
     */
    public function removeFavicon()
    {
        $settings = WebsiteSetting::getSettings();

        if ($settings->favicon) {
            // Delete from R2 CDN if it's a CDN URL
            if (str_starts_with($settings->favicon, 'http')) {
                try {
                    $this->r2Service->deleteImage($settings->favicon);
                } catch (\Exception $e) {
                    Log::warning('Failed to delete favicon from R2', ['error' => $e->getMessage()]);
                }
            }

            $settings->update(['favicon' => null]);
            Cache::forget('website_settings');

            Log::info('Favicon removed successfully');
        }

        return redirect()->route('admin.settings.website')
            ->with('success', 'Favicon removed successfully!');
    }
}
