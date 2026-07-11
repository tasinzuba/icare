<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Please login to continue.');
        }

        $user = auth()->user();

        // Super Admin (is_admin = true) always has access — even if also branch staff
        if ($user->is_admin) {
            return $next($request);
        }

        // SECURITY: Branch staff (non-admin) should NEVER access main admin panel
        if ($user->isBranchStaff()) {
            abort(403, 'Branch staff cannot access the main admin panel. Please use the Branch Admin panel.');
        }

        // Role-based admin (role_id is set AND not branch staff) has access
        if ($user->role_id !== null) {
            return $next($request);
        }

        // Student/Teacher (no role_id) - no admin access
        abort(403, 'You do not have permission to access the admin panel.');
    }
}
