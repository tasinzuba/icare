<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictOfflineStudentRoutes
{
    /**
     * Routes that offline students ARE allowed to access within student.* namespace.
     * All other student.* routes will be blocked for offline students.
     *
     * IMPORTANT: Offline students should ONLY access tests through their offline dashboard.
     * They should NOT be able to browse test lists directly.
     */
    protected array $allowedRoutePatterns = [
        // Full Test routes - but NOT the index (list) page
        // They must access tests through offline dashboard only
        'student.full-test.onboarding',
        'student.full-test.start',
        'student.full-test.continue',
        'student.full-test.section',
        'student.full-test.section-completed',
        'student.full-test.complete-section',
        'student.full-test.results',
        'student.full-test.abandon',
        'student.full-test.request-evaluation',
        'student.full-test.submit-evaluation',
        'student.full-test.evaluation-details',

        // Results viewing (for section tests taken within full tests)
        'student.results',
        'student.results.show',

        // Evaluation routes
        'student.evaluation.*',

        // Test session management (keep-alive, emergency save, etc.)
        'student.test.session.*',

        // Section tests - ONLY test execution routes, NOT index/browse pages
        // These are needed when taking sections within a full test
        'student.listening.start',
        'student.listening.submit',
        'student.listening.auto-save',
        'student.listening.draft-answers',
        'student.listening.onboarding.*',

        'student.reading.start',
        'student.reading.submit',
        'student.reading.auto-save',
        'student.reading.draft-answers',
        'student.reading.onboarding.*',

        'student.writing.start',
        'student.writing.submit',
        'student.writing.auto-save',
        'student.writing.draft-answers',
        'student.writing.autosave',
        'student.writing.onboarding.*',

        'student.speaking.start',
        'student.speaking.submit',
        'student.speaking.auto-save',
        'student.speaking.draft-answers',
        'student.speaking.record',
        'student.speaking.onboarding.*',
    ];

    /**
     * Handle an incoming request.
     * Block offline students from accessing unauthorized student routes.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Only apply to authenticated users
        if (!$user) {
            return $next($request);
        }

        // Only apply to offline students
        if ($user->student_type !== 'offline') {
            return $next($request);
        }

        // Get current route name
        $routeName = $request->route()->getName();

        // If no route name, allow (shouldn't happen normally)
        if (!$routeName) {
            return $next($request);
        }

        // Only check routes that start with 'student.'
        if (!str_starts_with($routeName, 'student.')) {
            return $next($request);
        }

        // Check if route is in allowed list
        if ($this->isRouteAllowed($routeName)) {
            return $next($request);
        }

        // Block access - redirect to offline dashboard with message
        return redirect()->route('offline.dashboard')
            ->with('error', 'This feature is not available for offline students. Please use your offline student dashboard.');
    }

    /**
     * Check if the route matches any allowed pattern.
     */
    protected function isRouteAllowed(string $routeName): bool
    {
        foreach ($this->allowedRoutePatterns as $pattern) {
            // Exact match
            if ($pattern === $routeName) {
                return true;
            }

            // Wildcard match (e.g., 'student.full-test.*' matches 'student.full-test.index')
            if (str_ends_with($pattern, '*')) {
                $prefix = rtrim($pattern, '*');
                if (str_starts_with($routeName, $prefix)) {
                    return true;
                }
            }
        }

        return false;
    }
}
