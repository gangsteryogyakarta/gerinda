<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SanitizeInput
{
    /**
     * Fields that should NOT be sanitized (passwords, etc.)
     */
    protected array $except = [
        'password',
        'password_confirmation',
        'current_password',
        'new_password',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get all input
        $input = $request->all();

        // Sanitize
        $sanitized = $this->sanitize($input);

        // Replace request input
        $request->replace($sanitized);

        return $next($request);
    }

    /**
     * Recursively sanitize input array
     */
    protected function sanitize(array $input): array
    {
        foreach ($input as $key => $value) {
            // Skip excepted fields
            if (in_array($key, $this->except)) {
                continue;
            }

            if (is_array($value)) {
                $input[$key] = $this->sanitize($value);
            } elseif (is_string($value)) {
                $input[$key] = $this->sanitizeString($value);
            }
        }

        return $input;
    }

    /**
     * Sanitize a single string value
     */
    protected function sanitizeString(string $value): string
    {
        // Trim whitespace
        $value = trim($value);

        // Convert special characters to HTML entities
        // This is a basic protection - Blade also auto-escapes output
        $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8', false);

        // Remove null bytes
        $value = str_replace("\0", '', $value);

        // Decode HTML entities back (we just want to clean, not double-encode)
        // Laravel Blade will handle output escaping
        $value = html_entity_decode($value, ENT_QUOTES, 'UTF-8');

        return $value;
    }
}
