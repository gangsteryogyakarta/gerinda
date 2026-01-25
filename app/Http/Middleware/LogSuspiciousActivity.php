<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogSuspiciousActivity
{
    /**
     * Suspicious patterns to detect
     */
    protected array $suspiciousPatterns = [
        // SQL Injection patterns
        '/(\bunion\b.*\bselect\b|\bselect\b.*\bfrom\b.*\bwhere\b)/i',
        '/(\'|\")(\s)*(or|and)(\s)*(\'|\"|\d)/i',
        '/(\bexec\b|\bexecute\b|\bxp_)/i',
        
        // XSS patterns
        '/<script[^>]*>.*?<\/script>/is',
        '/javascript:/i',
        '/on\w+\s*=/i',
        
        // Path traversal
        '/\.\.\/|\.\.\\\\/',
        
        // Command injection
        '/(\||;|`|\$\(|\$\{)/i',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check for suspicious input
        $suspiciousFields = $this->detectSuspiciousInput($request);

        if (!empty($suspiciousFields)) {
            $this->logSuspiciousActivity($request, $suspiciousFields);
        }

        return $next($request);
    }

    /**
     * Detect suspicious input in request
     */
    protected function detectSuspiciousInput(Request $request): array
    {
        $suspicious = [];
        $allInput = $request->all();

        foreach ($this->flattenArray($allInput) as $key => $value) {
            if (!is_string($value)) {
                continue;
            }

            foreach ($this->suspiciousPatterns as $pattern) {
                if (preg_match($pattern, $value)) {
                    $suspicious[$key] = [
                        'value' => $this->truncateValue($value),
                        'pattern' => $pattern,
                    ];
                    break;
                }
            }
        }

        return $suspicious;
    }

    /**
     * Flatten nested array for easier scanning
     */
    protected function flattenArray(array $array, string $prefix = ''): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            $newKey = $prefix ? "{$prefix}.{$key}" : $key;

            if (is_array($value)) {
                $result = array_merge($result, $this->flattenArray($value, $newKey));
            } else {
                $result[$newKey] = $value;
            }
        }

        return $result;
    }

    /**
     * Truncate long values for logging
     */
    protected function truncateValue(string $value, int $maxLength = 100): string
    {
        if (strlen($value) > $maxLength) {
            return substr($value, 0, $maxLength) . '...[truncated]';
        }
        return $value;
    }

    /**
     * Log suspicious activity
     */
    protected function logSuspiciousActivity(Request $request, array $suspiciousFields): void
    {
        Log::channel('security')->warning('Suspicious input detected', [
            'ip' => $request->ip(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'user_id' => $request->user()?->id,
            'user_agent' => $request->userAgent(),
            'suspicious_fields' => $suspiciousFields,
            'timestamp' => now()->toISOString(),
        ]);
    }
}
