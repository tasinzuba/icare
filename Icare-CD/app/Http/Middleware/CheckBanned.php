<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckBanned
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && Auth::user()->isBanned()) {
            // Allow access to banned page, appeal routes, and logout
            if ($request->routeIs('banned.*') || 
                $request->routeIs('logout') || 
                $request->is('banned') || 
                $request->is('banned/*')) {
                return $next($request);
            }
            
            // For any other route, redirect to banned page
            return redirect()->route('banned.index');
        }

        return $next($request);
    }
}
