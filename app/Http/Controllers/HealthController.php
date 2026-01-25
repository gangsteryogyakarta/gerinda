<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class HealthController extends Controller
{
    /**
     * Basic health check
     * 
     * Used by load balancers and monitoring tools
     */
    public function check(): JsonResponse
    {
        $status = 'ok';
        $checks = [];

        // Check database
        try {
            DB::connection()->getPdo();
            $checks['database'] = 'ok';
        } catch (\Exception $e) {
            $checks['database'] = 'error';
            $status = 'error';
        }

        // Check Redis
        try {
            Redis::ping();
            $checks['redis'] = 'ok';
        } catch (\Exception $e) {
            $checks['redis'] = 'error';
            $status = 'error';
        }

        // Check storage
        try {
            $storagePath = storage_path('app');
            if (is_writable($storagePath)) {
                $checks['storage'] = 'ok';
            } else {
                $checks['storage'] = 'error';
                $status = 'error';
            }
        } catch (\Exception $e) {
            $checks['storage'] = 'error';
            $status = 'error';
        }

        $httpCode = $status === 'ok' ? 200 : 500;

        return response()->json([
            'status' => $status,
            'timestamp' => now()->toISOString(),
            'checks' => $checks,
        ], $httpCode);
    }

    /**
     * Detailed health check (authenticated)
     * 
     * Provides more details for debugging
     */
    public function detailed(): JsonResponse
    {
        $checks = [];
        
        // Database check with query time
        $dbStart = microtime(true);
        try {
            DB::select('SELECT 1');
            $checks['database'] = [
                'status' => 'ok',
                'response_time_ms' => round((microtime(true) - $dbStart) * 1000, 2),
            ];
        } catch (\Exception $e) {
            $checks['database'] = [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }

        // Redis check with ping time
        $redisStart = microtime(true);
        try {
            Redis::ping();
            $checks['redis'] = [
                'status' => 'ok',
                'response_time_ms' => round((microtime(true) - $redisStart) * 1000, 2),
            ];
        } catch (\Exception $e) {
            $checks['redis'] = [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }

        // Cache check
        try {
            Cache::put('health_check', true, 10);
            $checks['cache'] = [
                'status' => Cache::get('health_check') ? 'ok' : 'error',
                'driver' => config('cache.default'),
            ];
        } catch (\Exception $e) {
            $checks['cache'] = [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }

        // Queue check
        $checks['queue'] = [
            'driver' => config('queue.default'),
            'status' => 'ok',
        ];

        // System info
        $checks['system'] = [
            'php_version' => phpversion(),
            'laravel_version' => app()->version(),
            'environment' => app()->environment(),
            'debug_mode' => config('app.debug'),
            'timezone' => config('app.timezone'),
            'memory_usage_mb' => round(memory_get_usage(true) / 1048576, 2),
            'memory_peak_mb' => round(memory_get_peak_usage(true) / 1048576, 2),
        ];

        // Storage info
        $storagePath = storage_path();
        $checks['storage'] = [
            'path' => $storagePath,
            'writable' => is_writable($storagePath),
            'free_space_gb' => round(disk_free_space($storagePath) / 1073741824, 2),
        ];

        $overallStatus = collect($checks)
            ->filter(fn($check) => is_array($check) && isset($check['status']))
            ->every(fn($check) => $check['status'] === 'ok');

        return response()->json([
            'status' => $overallStatus ? 'ok' : 'degraded',
            'timestamp' => now()->toISOString(),
            'checks' => $checks,
        ], $overallStatus ? 200 : 503);
    }
}
