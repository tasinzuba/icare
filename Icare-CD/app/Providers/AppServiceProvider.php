<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Configure rate limiters for test operations
        $this->configureRateLimiting();

        // Register Blade directives for permissions
        \Illuminate\Support\Facades\Blade::if('hasPermission', function ($permission) {
            return auth()->check() && auth()->user()->hasPermission($permission);
        });
        
        \Illuminate\Support\Facades\Blade::if('hasAnyPermission', function (...$permissions) {
            return auth()->check() && auth()->user()->hasAnyPermission($permissions);
        });
        
        \Illuminate\Support\Facades\Blade::if('hasAllPermissions', function (...$permissions) {
            return auth()->check() && auth()->user()->hasAllPermissions($permissions);
        });
    }

    /**
     * Configure rate limiting for test operations.
     *
     * This prevents:
     * - Double-click on "Start Test" button
     * - Rapid form resubmission
     * - Bot attacks on test endpoints
     */
    protected function configureRateLimiting(): void
    {
        // Rate limiter for test start - 1 request per 3 seconds per user
        // Prevents double-click creating duplicate attempts
        // Using perMinute(20) ≈ 1 request per 3 seconds
        RateLimiter::for('test-start', function (Request $request) {
            return Limit::perMinute(20)
                ->by('test-start:' . ($request->user()?->id ?: $request->ip()))
                ->response(function (Request $request, array $headers) {
                    return back()->with('error', 'অনুগ্রহ করে কয়েক সেকেন্ড অপেক্ষা করুন। / Please wait a few seconds before trying again.');
                });
        });

        // Rate limiter for test submit - 1 request per 5 seconds per user
        // Prevents accidental double submission
        // Using perMinute(12) ≈ 1 request per 5 seconds
        RateLimiter::for('test-submit', function (Request $request) {
            return Limit::perMinute(12)
                ->by('test-submit:' . ($request->user()?->id ?: $request->ip()))
                ->response(function (Request $request, array $headers) {
                    return back()->with('error', 'আপনার টেস্ট সাবমিট হচ্ছে, অনুগ্রহ করে অপেক্ষা করুন। / Your test is being submitted, please wait.');
                });
        });

        // Rate limiter for full test complete section - 1 request per 2 seconds
        // Prevents race condition on section completion
        // Using perMinute(30) ≈ 1 request per 2 seconds
        RateLimiter::for('section-complete', function (Request $request) {
            return Limit::perMinute(30)
                ->by('section-complete:' . ($request->user()?->id ?: $request->ip()))
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'message' => 'Processing your request, please wait.',
                        'retry_after' => 2
                    ], 429);
                });
        });

        // General API rate limiter - 60 requests per minute
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}