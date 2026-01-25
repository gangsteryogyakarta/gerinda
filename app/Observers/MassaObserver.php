<?php

namespace App\Observers;

use App\Models\Massa;
use Illuminate\Support\Facades\Cache;

class MassaObserver
{
    /**
     * Cache keys to invalidate when massa changes
     */
    protected array $cacheKeys = [
        'dashboard_stats',
        'dashboard_massa_province',
    ];

    /**
     * Handle the Massa "created" event.
     */
    public function created(Massa $massa): void
    {
        $this->invalidateCache();
    }

    /**
     * Handle the Massa "updated" event.
     */
    public function updated(Massa $massa): void
    {
        $this->invalidateCache();
        
        // Check if province changed
        if ($massa->isDirty('province_id')) {
            Cache::forget('dashboard_massa_province');
        }
    }

    /**
     * Handle the Massa "deleted" event.
     */
    public function deleted(Massa $massa): void
    {
        $this->invalidateCache();
    }

    /**
     * Invalidate all related cache entries
     */
    protected function invalidateCache(): void
    {
        foreach ($this->cacheKeys as $key) {
            Cache::forget($key);
        }
    }
}
