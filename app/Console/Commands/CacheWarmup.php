<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Massa;
use App\Models\Province;
use App\Models\Regency;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class CacheWarmup extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'cache:warmup 
                            {--force : Force refresh even if cache exists}';

    /**
     * The console command description.
     */
    protected $description = 'Warm up frequently accessed caches';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting cache warmup...');
        $startTime = microtime(true);
        $warmedCount = 0;

        // Warmup static data
        $this->warmupStaticData($warmedCount);

        // Warmup dashboard data
        $this->warmupDashboardData($warmedCount);

        // Warmup wilayah data
        $this->warmupWilayahData($warmedCount);

        $duration = round((microtime(true) - $startTime) * 1000, 2);

        $this->newLine();
        $this->info("✓ Cache warmup completed!");
        $this->line("  Caches warmed: {$warmedCount}");
        $this->line("  Duration: {$duration}ms");

        return self::SUCCESS;
    }

    protected function warmupStaticData(int &$count): void
    {
        $this->line('  Warming up static data...');

        // Event categories
        if ($this->shouldWarmup('event_categories_active')) {
            Cache::put('event_categories_active', 
                EventCategory::active()->ordered()->get(['id', 'name', 'icon']),
                now()->addHour()
            );
            $count++;
            $this->line('    ✓ Event categories cached');
        }

        // Provinces list
        if ($this->shouldWarmup('provinces_list')) {
            Cache::put('provinces_list',
                Province::select(['id', 'name'])->orderBy('name')->get(),
                now()->addDay()
            );
            $count++;
            $this->line('    ✓ Provinces list cached');
        }
    }

    protected function warmupDashboardData(int &$count): void
    {
        $this->line('  Warming up dashboard data...');

        // Dashboard stats
        if ($this->shouldWarmup('dashboard_stats')) {
            Cache::put('dashboard_stats', [
                'total_massa' => Massa::active()->count(),
                'total_events' => Event::count(),
                'active_events' => Event::whereIn('status', ['published', 'ongoing'])->count(),
                'total_registrations' => \App\Models\EventRegistration::confirmed()->count(),
                'total_checkins' => \App\Models\EventRegistration::checkedIn()->count(),
            ], now()->addHour());
            $count++;
            $this->line('    ✓ Dashboard stats cached');
        }

        // Recent events
        if ($this->shouldWarmup('dashboard_recent_events')) {
            Cache::put('dashboard_recent_events',
                Event::with(['category'])->latest()->limit(5)->get(),
                now()->addMinutes(10)
            );
            $count++;
            $this->line('    ✓ Recent events cached');
        }

        // Upcoming events
        if ($this->shouldWarmup('dashboard_upcoming_events')) {
            Cache::put('dashboard_upcoming_events',
                Event::with(['category'])->upcoming()->limit(5)->get(),
                now()->addMinutes(10)
            );
            $count++;
            $this->line('    ✓ Upcoming events cached');
        }

        // Massa by province
        if ($this->shouldWarmup('dashboard_massa_province')) {
            Cache::put('dashboard_massa_province',
                Massa::active()
                    ->selectRaw('province_id, COUNT(*) as total')
                    ->groupBy('province_id')
                    ->with('province:id,name')
                    ->orderByDesc('total')
                    ->limit(10)
                    ->get(),
                now()->addDay()
            );
            $count++;
            $this->line('    ✓ Massa by province cached');
        }
    }

    protected function warmupWilayahData(int &$count): void
    {
        $this->line('  Warming up wilayah data...');

        // Cache popular provinces' regencies (DI Yogyakarta, Jawa Tengah, Jawa Timur)
        $popularProvinces = Province::whereIn('name', [
            'DI Yogyakarta',
            'Jawa Tengah', 
            'Jawa Timur',
            'DKI Jakarta',
            'Jawa Barat'
        ])->get();

        foreach ($popularProvinces as $province) {
            $cacheKey = "wilayah_regencies_{$province->id}";
            
            if ($this->shouldWarmup($cacheKey)) {
                Cache::put($cacheKey,
                    Regency::select(['id', 'name'])
                        ->where('province_id', $province->id)
                        ->orderBy('name')
                        ->get(),
                    now()->addDay()
                );
                $count++;
            }
        }
        
        $this->line('    ✓ Popular provinces regencies cached');
    }

    protected function shouldWarmup(string $key): bool
    {
        if ($this->option('force')) {
            return true;
        }

        return !Cache::has($key);
    }
}
