<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;

class SystemHealthCheck extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'system:health 
                            {--detailed : Show detailed information}
                            {--json : Output as JSON}';

    /**
     * The console command description.
     */
    protected $description = 'Check system health status for all services';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $checks = [];
        $allPassed = true;

        // Database check
        $checks['database'] = $this->checkDatabase();
        if (!$checks['database']['healthy']) $allPassed = false;

        // Redis check
        $checks['redis'] = $this->checkRedis();
        if (!$checks['redis']['healthy']) $allPassed = false;

        // Cache check
        $checks['cache'] = $this->checkCache();
        if (!$checks['cache']['healthy']) $allPassed = false;

        // Queue check
        $checks['queue'] = $this->checkQueue();
        if (!$checks['queue']['healthy']) $allPassed = false;

        // Storage check
        $checks['storage'] = $this->checkStorage();
        if (!$checks['storage']['healthy']) $allPassed = false;

        // Disk space check
        $checks['disk'] = $this->checkDiskSpace();
        if (!$checks['disk']['healthy']) $allPassed = false;

        // Output results
        if ($this->option('json')) {
            $this->line(json_encode([
                'status' => $allPassed ? 'healthy' : 'unhealthy',
                'timestamp' => now()->toISOString(),
                'checks' => $checks,
            ], JSON_PRETTY_PRINT));
        } else {
            $this->displayResults($checks, $allPassed);
        }

        return $allPassed ? self::SUCCESS : self::FAILURE;
    }

    protected function checkDatabase(): array
    {
        $start = microtime(true);
        
        try {
            DB::select('SELECT 1');
            $responseTime = round((microtime(true) - $start) * 1000, 2);
            
            $info = [];
            if ($this->option('detailed')) {
                $info['connection'] = config('database.default');
                $info['database'] = config('database.connections.mysql.database');
            }
            
            return [
                'healthy' => true,
                'response_time_ms' => $responseTime,
                'info' => $info,
            ];
        } catch (\Exception $e) {
            return [
                'healthy' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    protected function checkRedis(): array
    {
        $start = microtime(true);
        
        try {
            Redis::ping();
            $responseTime = round((microtime(true) - $start) * 1000, 2);
            
            $info = [];
            if ($this->option('detailed')) {
                $redisInfo = Redis::info();
                $info['version'] = $redisInfo['redis_version'] ?? 'unknown';
                $info['memory_used'] = $redisInfo['used_memory_human'] ?? 'unknown';
                $info['connected_clients'] = $redisInfo['connected_clients'] ?? 'unknown';
            }
            
            return [
                'healthy' => true,
                'response_time_ms' => $responseTime,
                'info' => $info,
            ];
        } catch (\Exception $e) {
            return [
                'healthy' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    protected function checkCache(): array
    {
        try {
            $key = 'health_check_' . uniqid();
            Cache::put($key, true, 10);
            $value = Cache::get($key);
            Cache::forget($key);
            
            return [
                'healthy' => $value === true,
                'driver' => config('cache.default'),
            ];
        } catch (\Exception $e) {
            return [
                'healthy' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    protected function checkQueue(): array
    {
        try {
            $connection = config('queue.default');
            $size = 0;
            
            if ($connection === 'redis') {
                $size = Redis::llen('queues:default') ?? 0;
            }
            
            // Get failed jobs count
            $failedCount = DB::table('failed_jobs')->count();
            
            return [
                'healthy' => true,
                'connection' => $connection,
                'pending_jobs' => $size,
                'failed_jobs' => $failedCount,
            ];
        } catch (\Exception $e) {
            return [
                'healthy' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    protected function checkStorage(): array
    {
        try {
            $storagePath = storage_path();
            $writable = is_writable($storagePath);
            $logsWritable = is_writable(storage_path('logs'));
            $cacheWritable = is_writable(storage_path('framework/cache'));
            
            return [
                'healthy' => $writable && $logsWritable && $cacheWritable,
                'storage_writable' => $writable,
                'logs_writable' => $logsWritable,
                'cache_writable' => $cacheWritable,
            ];
        } catch (\Exception $e) {
            return [
                'healthy' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    protected function checkDiskSpace(): array
    {
        $storagePath = storage_path();
        $freeSpace = disk_free_space($storagePath);
        $totalSpace = disk_total_space($storagePath);
        $usedPercent = round((($totalSpace - $freeSpace) / $totalSpace) * 100, 2);
        
        // Warning if less than 10% free
        $healthy = $usedPercent < 90;
        
        return [
            'healthy' => $healthy,
            'free_gb' => round($freeSpace / 1073741824, 2),
            'total_gb' => round($totalSpace / 1073741824, 2),
            'used_percent' => $usedPercent,
        ];
    }

    protected function displayResults(array $checks, bool $allPassed): void
    {
        $this->newLine();
        $this->info('╔════════════════════════════════════════════════════════╗');
        $this->info('║           GERINDRA EMS - SYSTEM HEALTH CHECK           ║');
        $this->info('╚════════════════════════════════════════════════════════╝');
        $this->newLine();
        
        foreach ($checks as $name => $check) {
            $status = $check['healthy'] ? '<fg=green>✓ PASS</>' : '<fg=red>✗ FAIL</>';
            $this->line("  {$status}  " . ucfirst($name));
            
            if ($this->option('detailed') || !$check['healthy']) {
                foreach ($check as $key => $value) {
                    if ($key === 'healthy') continue;
                    if (is_array($value)) {
                        foreach ($value as $subKey => $subValue) {
                            $this->line("         <fg=gray>{$subKey}: {$subValue}</>");
                        }
                    } else {
                        $this->line("         <fg=gray>{$key}: {$value}</>");
                    }
                }
            }
        }
        
        $this->newLine();
        
        if ($allPassed) {
            $this->info('  Overall Status: ✓ All checks passed');
        } else {
            $this->error('  Overall Status: ✗ Some checks failed');
        }
        
        $this->newLine();
    }
}
