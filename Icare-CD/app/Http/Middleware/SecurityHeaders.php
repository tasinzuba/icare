<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Security headers to protect against common web vulnerabilities
     * - XSS attacks
     * - Clickjacking
     * - MIME sniffing
     * - And more
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Prevent Clickjacking - deny framing from any origin
        $response->headers->set('X-Frame-Options', 'DENY');

        // Prevent MIME type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Enable XSS filter in older browsers
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Referrer Policy - don't leak referrer info
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Permissions Policy - restrict browser features
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(self), geolocation=(), payment=()');

        // Content Security Policy - prevent XSS and injection attacks
        $csp = $this->buildContentSecurityPolicy();
        $response->headers->set('Content-Security-Policy', $csp);

        // HSTS - enforce HTTPS (only in production)
        if (config('app.env') === 'production') {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }

    /**
     * Build Content Security Policy header
     * Note: Using permissive policy to avoid breaking site functionality
     * while still providing security against the most common attacks
     */
    protected function buildContentSecurityPolicy(): string
    {
        $isLocal = config('app.env') !== 'production';
        $devHosts = $isLocal ? ' http://127.0.0.1:5173 http://localhost:5173 ws://127.0.0.1:5173 ws://localhost:5173' : '';

        $policies = [
            // Default fallback - allow self and https
            "default-src 'self' https:" . $devHosts,

            // Scripts - allow self, inline, eval, and all https sources
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https: blob:" . $devHosts,

            // Styles - allow self, inline, and all https sources
            "style-src 'self' 'unsafe-inline' https:" . $devHosts,

            // Images - allow self, data URIs, blob, and all sources
            "img-src 'self' data: blob: https: http:",

            // Fonts - allow self, data, and all https sources
            "font-src 'self' data: https:",

            // Connect - API calls, websockets
            "connect-src 'self' https: wss: ws:" . $devHosts,

            // Media - audio/video
            "media-src 'self' blob: https: data:",

            // Frames - allow https sources for payment gateways, embeds, etc.
            "frame-src 'self' https:",

            // Form actions - allow self and https (needed for payment gateway redirects like bKash)
            "form-action 'self' https:",

            // Base URI - prevent base tag hijacking
            "base-uri 'self'",

            // Object/embed - block plugins (Flash, Java, etc.)
            "object-src 'none'",

            // Upgrade insecure requests in production
            config('app.env') === 'production' ? "upgrade-insecure-requests" : "",
        ];

        return implode('; ', array_filter($policies));
    }
}
