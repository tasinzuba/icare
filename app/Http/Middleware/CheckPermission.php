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

        if (empty($permissions)) {
            return $next($request);
        }

        // SECURITY: route groups list every verb at once, e.g.
        //   permission:questions.view,questions.create,questions.edit,questions.delete
        // and this used to pass on hasAnyPermission(), so a read-only role holding just
        // questions.view could also create, edit and delete — the group's write routes were
        // effectively unguarded. Pick the permission that matches the request method instead, so
        // .view only ever grants reads.
        $required = $this->permissionForMethod($request->method(), $permissions);

        if ($required !== null) {
            if (!$user->hasPermission($required)) {
                abort(403, 'You do not have permission to perform this action.');
            }

            return $next($request);
        }

        // No verb-specific permission is listed for this group (for example the attempts group has
        // no *.create), so fall back to the original any-of check rather than locking staff out.
        if (!$user->hasAnyPermission($permissions)) {
            abort(403, 'You do not have permission to perform this action.');
        }

        return $next($request);
    }

    /**
     * The listed permission that matches this HTTP method, or null when the group does not list one.
     *
     * @param  array<int, string>  $permissions
     */
    private function permissionForMethod(string $method, array $permissions): ?string
    {
        // POST covers both creating and updating (e.g. settings are saved with POST), so accept a
        // create permission first and fall back to edit before giving up.
        $suffixes = match (strtoupper($method)) {
            'GET', 'HEAD' => ['view'],
            'POST' => ['create', 'edit'],
            'PUT', 'PATCH' => ['edit'],
            'DELETE' => ['delete'],
            default => [],
        };

        foreach ($suffixes as $suffix) {
            foreach ($permissions as $permission) {
                if (str_ends_with($permission, '.' . $suffix)) {
                    return $permission;
                }
            }
        }

        return null;
    }
}
