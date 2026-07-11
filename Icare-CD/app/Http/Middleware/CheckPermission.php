<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$permissions - Permission slugs to check
     */
    public function handle(Request $request, Closure $next, ...$permissions): Response
    {
        $user = auth()->user();

        // SECURITY: Branch staff should NEVER access main admin routes
        if ($user->isBranchStaff()) {
            abort(403, 'Branch staff cannot access this resource.');
        }

        // Super Admin (is_admin = true) always has access - bypass permission check
        if ($user->is_admin) {
            return $next($request);
        }

        // Check if user has admin panel access
        if ($user->role_id === null) {
            abort(403, 'You do not have permission to access this resource.');
        }

        // Check if user has any of the required permissions
        if (!empty($permissions) && !$user->hasAnyPermission($permissions)) {
            abort(403, 'You do not have permission to perform this action.');
        }

        return $next($request);
    }
}
