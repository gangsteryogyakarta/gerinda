<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Security headers to add to all responses
     */
    protected array $headers = [
        // Prevent clickjacking
        'X-Frame-Options' => 'SAMEORIGIN',
        
        // Prevent MIME type sniffing
        'X-Content-Type-Options' => 'nosniff',
        
        // Enable XSS filter in old browsers
        'X-XSS-Protection' => '1; mode=block',
        
        // Control referrer information
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        
        // Permissions Policy (formerly Feature Policy)
        'Permissions-Policy' => 'camera=(self), microphone=(), geolocation=(self), payment=()',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Add security headers
        foreach ($this->headers as $header => $value) {
            $response->headers->set($header, $value);
        }

        // Add Content Security Policy for HTML responses
        if ($this->isHtmlResponse($response)) {
            $response->headers->set('Content-Security-Policy', $this->getContentSecurityPolicy());
        }

        // Add HSTS header in production
        if (app()->environment('production')) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains'
            );
        }

        return $response;
    }

    /**
     * Check if response is HTML
     */
    protected function isHtmlResponse(Response $response): bool
    {
        $contentType = $response->headers->get('Content-Type', '');
        return str_contains($contentType, 'text/html') || empty($contentType);
    }

    /**
     * Get Content Security Policy
     */
    protected function getContentSecurityPolicy(): string
    {
        $policies = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://unpkg.com",
            "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com",
            "img-src 'self' data: https: blob:",
            "font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net",
            "connect-src 'self' https://nominatim.openstreetmap.org https://*.tile.openstreetmap.org",
            "frame-src 'self'",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
        ];

        return implode('; ', $policies);
    }
}
