<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckBranchAccess
{
    /**
     * Handle an incoming request.
     * Check if user has branch admin/staff access
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role = 'staff'): Response
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Check if user is branch staff
        if (!$user->isBranchStaff()) {
            abort(403, 'You do not have access to the branch admin panel.');
        }

        // If specific role required (admin), check that
        if ($role === 'admin' && !$user->isBranchAdmin()) {
            abort(403, 'This action requires branch admin privileges.');
        }

        // Get user's primary branch and share with all views
        $branch = $user->getPrimaryBranch();
        if (!$branch) {
            abort(403, 'You are not assigned to any branch.');
        }

        // Share branch with all views
        view()->share('currentBranch', $branch);
        view()->share('branchStaffRole', $user->branchStaffRecords()->where('active', true)->first()?->role);

        return $next($request);
    }
}
