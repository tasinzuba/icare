<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!Auth::check()) {
            return redirect('login');
        }

        if ($role === 'admin' && !Auth::user()->is_admin) {
            abort(403, 'Unauthorized action.');
        }

        if ($role === 'student') {
            // Redirect admin to admin dashboard
            if (Auth::user()->is_admin) {
                return redirect()->route('admin.dashboard');
            }

            // Redirect offline students away from student dashboard only
            // They can still access test routes, results, etc.
            if (Auth::user()->student_type === 'offline') {
                $routeName = $request->route()->getName();

                // Block access to student dashboard and related non-test routes
                $blockedRoutes = [
                    'student.dashboard',
                    'student.dashboard.progress-data',
                ];

                if (in_array($routeName, $blockedRoutes)) {
                    return redirect()->route('offline.dashboard');
                }
            }
        }

        return $next($request);
    }
}