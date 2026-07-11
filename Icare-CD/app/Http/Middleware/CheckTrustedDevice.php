<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckTrustedDevice
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // If user is logged in and has a device trust token
        if (Auth::check() && $request->cookie('device_trust_token')) {
            $token = $request->cookie('device_trust_token');
            $user = Auth::user();
            
            // Check if this device is still trusted
            $trustedDevice = DB::table('trusted_devices')
                ->where('user_id', $user->id)
                ->where('device_token', $token)
                ->where('trusted_until', '>', now())
                ->first();
            
            if ($trustedDevice) {
                // Extend session lifetime for trusted device
                config(['session.lifetime' => 60 * 24 * 60]); // 60 days
                
                // Update last activity
                DB::table('trusted_devices')
                    ->where('id', $trustedDevice->id)
                    ->update(['updated_at' => now()]);
            } else {
                // Remove invalid cookie
                cookie()->queue(cookie()->forget('device_trust_token'));
            }
        }
        
        return $next($request);
    }
}