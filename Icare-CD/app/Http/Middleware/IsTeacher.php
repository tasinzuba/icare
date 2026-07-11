<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Teacher;

class IsTeacher
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }
        
        // Check if user is a teacher
        if (!Teacher::where('user_id', auth()->id())->exists()) {
            abort(403, 'Unauthorized. You must be a registered teacher to access this area.');
        }
        
        return $next($request);
    }
}
