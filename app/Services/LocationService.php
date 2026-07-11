<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class LocationService
{
    public function getLocation($ip)
    {
        // For localhost/testing
        if ($ip === '127.0.0.1' || $ip === '::1') {
            return [
                'countryCode' => 'BD',
                'countryName' => 'Bangladesh',
                'cityName' => 'Dhaka',
                'timezone' => 'Asia/Dhaka',
            ];
        }

        // Check cache first
        $cacheKey = 'location_' . $ip;
        $cached = Cache::get($cacheKey);
        if ($cached) {
            return $cached;
        }

        try {
            // Using ip-api.com (free, no API key needed)
            $response = Http::timeout(5)->get("http://ip-api.com/json/{$ip}");
            
            if ($response->successful()) {
                $data = $response->json();
                
                $location = [
                    'countryCode' => $data['countryCode'] ?? 'BD',
                    'countryName' => $data['country'] ?? 'Bangladesh',
                    'cityName' => $data['city'] ?? 'Unknown',
                    'timezone' => $data['timezone'] ?? 'Asia/Dhaka',
                ];
                
                // Cache for 24 hours
                Cache::put($cacheKey, $location, 86400);
                
                return $location;
            }
        } catch (\Exception $e) {
            // Log error but don't break the flow
            \Log::error('Location service error: ' . $e->getMessage());
        }

        // Default fallback
        return [
            'countryCode' => 'BD',
            'countryName' => 'Bangladesh',
            'cityName' => 'Unknown',
            'timezone' => 'Asia/Dhaka',
        ];
    }
}