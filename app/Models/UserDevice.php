<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jenssegers\Agent\Agent;

class UserDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'device_fingerprint',
        'device_name',
        'browser',
        'browser_version',
        'platform',
        'platform_version',
        'device_type',
        'ip_address',
        'country_code',
        'country_name',
        'city',
        'is_trusted',
        'last_activity',
        'verified_at'
    ];

    protected $casts = [
        'is_trusted' => 'boolean',
        'last_activity' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function markAsTrusted(): void
    {
        $this->update([
            'is_trusted' => true,
            'verified_at' => now()
        ]);
    }

    public function updateActivity(): void
    {
        $this->update(['last_activity' => now()]);
    }

    public static function createFromRequest($request, $userId, $locationData = null)
    {
        $agent = new Agent();
        $agent->setUserAgent($request->userAgent());

        $fingerprint = self::generateFingerprint($request);

        // First try to find existing device
        $existingDevice = self::where('user_id', $userId)
            ->where('device_fingerprint', $fingerprint)
            ->first();

        if ($existingDevice) {
            // Update existing device
            $existingDevice->update([
                'device_name' => $agent->device() ?: 'Unknown Device',
                'browser' => $agent->browser() ?: 'Unknown Browser',
                'browser_version' => $agent->version($agent->browser()) ?: 'Unknown',
                'platform' => $agent->platform() ?: 'Unknown Platform',
                'platform_version' => $agent->version($agent->platform()) ?: 'Unknown',
                'device_type' => $agent->isDesktop() ? 'desktop' : ($agent->isTablet() ? 'tablet' : 'mobile'),
                'ip_address' => $request->ip(),
                'country_code' => $locationData['countryCode'] ?? null,
                'country_name' => $locationData['countryName'] ?? null,
                'city' => $locationData['cityName'] ?? null,
                'last_activity' => now(),
            ]);
            return $existingDevice;
        }

        // Create new device
        return self::create([
            'user_id' => $userId,
            'device_fingerprint' => $fingerprint,
            'device_name' => $agent->device() ?: 'Unknown Device',
            'browser' => $agent->browser() ?: 'Unknown Browser',
            'browser_version' => $agent->version($agent->browser()) ?: 'Unknown',
            'platform' => $agent->platform() ?: 'Unknown Platform',
            'platform_version' => $agent->version($agent->platform()) ?: 'Unknown',
            'device_type' => $agent->isDesktop() ? 'desktop' : ($agent->isTablet() ? 'tablet' : 'mobile'),
            'ip_address' => $request->ip(),
            'country_code' => $locationData['countryCode'] ?? null,
            'country_name' => $locationData['countryName'] ?? null,
            'city' => $locationData['cityName'] ?? null,
            'last_activity' => now(),
        ]);
    }

    public static function generateFingerprint($request): string
    {
        $data = [
            $request->userAgent(),
            $request->ip(),
            $request->header('Accept'),
            $request->header('Accept-Language'),
            $request->header('Accept-Encoding'),
        ];

        return hash('sha256', implode('|', $data));
    }
}