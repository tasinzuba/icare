<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OfflineStudentAccess
{
    /**
     * Handle an incoming request.
     * Only allow offline students to access these routes.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Check if user is an offline student
        if ($user->student_type !== 'offline') {
            // If online student, redirect to normal dashboard
            return redirect()->route('student.dashboard');
        }

        return $next($request);
    }
}
