<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class PreventDuplicateSubmission
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only check for POST requests
        if (!$request->isMethod('post')) {
            return $next($request);
        }

        // Generate a unique key for this request
        $key = $this->getRequestKey($request);

        // Check if this request was submitted recently (within 3 seconds)
        if (Cache::has($key)) {
            return response()->json([
                'error' => 'Duplicate request detected. Please wait a moment before trying again.'
            ], 429);
        }

        // Store the request key for 3 seconds
        Cache::put($key, true, 3);

        $response = $next($request);

        // Remove the key after successful processing
        Cache::forget($key);

        return $response;
    }

    /**
     * Generate a unique key for the request
     */
    private function getRequestKey(Request $request): string
    {
        $fingerprint = implode('|', [
            $request->ip(),
            $request->path(),
            $request->method(),
            $request->user() ? $request->user()->id : 'guest',
        ]);

        return 'duplicate_submission:' . sha1($fingerprint);
    }
}
