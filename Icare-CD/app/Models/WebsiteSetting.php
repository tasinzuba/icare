<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class WebsiteSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_title',
        'site_logo',
        'dark_mode_logo',
        'favicon',
        'contact_email',
        'contact_phone',
        'address',
        'facebook_url',
        'twitter_url',
        'instagram_url',
        'youtube_url',
        'linkedin_url',
        'footer_text',
        'copyright_text',
        'meta_tags',
        'human_evaluation_enabled',
    ];

    protected $casts = [
        'meta_tags' => 'array',
        'human_evaluation_enabled' => 'boolean',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Clear cache when settings are updated
        static::saved(function () {
            Cache::forget('website_settings');
        });

        static::deleted(function () {
            Cache::forget('website_settings');
        });
        
        static::updated(function () {
            Cache::forget('website_settings');
        });
    }

    /**
     * Get the settings instance
     */
    public static function getSettings()
    {
        return Cache::remember('website_settings', 60, function () {
            return self::first() ?? self::create([
                'site_title' => 'IELTS Mock Platform',
                'copyright_text' => '© ' . date('Y') . ' IELTS Mock Platform. All rights reserved.'
            ]);
        });
    }

    /**
     * Get logo URL
     * Supports both CDN URLs (R2) and legacy local paths
     */
    public function getLogoUrlAttribute()
    {
        if (!$this->site_logo) {
            return null;
        }

        // If already a full URL (CDN), return as-is
        if (str_starts_with($this->site_logo, 'http')) {
            return $this->site_logo;
        }

        // Legacy local storage path
        return asset('storage/' . $this->site_logo);
    }

    /**
     * Get dark mode logo URL
     * Supports both CDN URLs (R2) and legacy local paths
     */
    public function getDarkModeLogoUrlAttribute()
    {
        if (!$this->dark_mode_logo) {
            return null;
        }

        // If already a full URL (CDN), return as-is
        if (str_starts_with($this->dark_mode_logo, 'http')) {
            return $this->dark_mode_logo;
        }

        // Legacy local storage path
        return asset('storage/' . $this->dark_mode_logo);
    }

    /**
     * Get favicon URL
     * Supports both CDN URLs (R2) and legacy local paths
     */
    public function getFaviconUrlAttribute()
    {
        if (!$this->favicon) {
            return null;
        }

        // If already a full URL (CDN), return as-is
        if (str_starts_with($this->favicon, 'http')) {
            return $this->favicon;
        }

        // Legacy local storage path
        return asset('storage/' . $this->favicon);
    }

    /**
     * Check if social media links exist
     */
    public function hasSocialLinks()
    {
        return $this->facebook_url || $this->twitter_url || $this->instagram_url || 
               $this->youtube_url || $this->linkedin_url;
    }

    /**
     * Get site name for backward compatibility
     */
    public function getSiteNameAttribute()
    {
        return $this->site_title;
    }

    /**
     * Get social media links array
     */
    public function getSocialLinksAttribute()
    {
        $links = [];

        if ($this->facebook_url) {
            $links[] = ['name' => 'Facebook', 'url' => $this->facebook_url, 'icon' => 'fab fa-facebook-f'];
        }
        if ($this->twitter_url) {
            $links[] = ['name' => 'Twitter', 'url' => $this->twitter_url, 'icon' => 'fab fa-twitter'];
        }
        if ($this->instagram_url) {
            $links[] = ['name' => 'Instagram', 'url' => $this->instagram_url, 'icon' => 'fab fa-instagram'];
        }
        if ($this->youtube_url) {
            $links[] = ['name' => 'YouTube', 'url' => $this->youtube_url, 'icon' => 'fab fa-youtube'];
        }
        if ($this->linkedin_url) {
            $links[] = ['name' => 'LinkedIn', 'url' => $this->linkedin_url, 'icon' => 'fab fa-linkedin-in'];
        }

        return $links;
    }
}
