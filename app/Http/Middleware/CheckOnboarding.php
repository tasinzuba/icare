<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckOnboarding
{
    /**
     * Handle an incoming request.
     * Sets a flag for showing onboarding modal on dashboard.
     * No longer redirects - modal will show on dashboard instead.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Skip if not authenticated
        if (!$user) {
            return $next($request);
        }

        // Determine if onboarding should be shown
        $showOnboarding = false;

        // Only show for regular online students who haven't completed onboarding
        if (!$user->is_admin &&
            !$user->isOfflineStudent() &&
            !$user->teacher &&
            !$user->onboarding_completed) {
            $showOnboarding = true;
        }

        // Share with all views
        view()->share('showOnboarding', $showOnboarding);

        return $next($request);
    }
}
