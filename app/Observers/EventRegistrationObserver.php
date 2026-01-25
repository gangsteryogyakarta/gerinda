<?php

namespace App\Observers;

use App\Models\EventRegistration;
use Illuminate\Support\Facades\Cache;

class EventRegistrationObserver
{
    /**
     * Cache keys to invalidate when registration changes
     */
    protected array $cacheKeys = [
        'dashboard_stats',
        'dashboard_trend',
    ];

    /**
     * Handle the EventRegistration "created" event.
     */
    public function created(EventRegistration $registration): void
    {
        $this->invalidateCache($registration);
    }

    /**
     * Handle the EventRegistration "updated" event.
     */
    public function updated(EventRegistration $registration): void
    {
        $this->invalidateCache($registration);
        
        // If check-in status changed
        if ($registration->isDirty('attendance_status')) {
            Cache::forget("event_checkin_stats_{$registration->event_id}");
        }
    }

    /**
     * Handle the EventRegistration "deleted" event.
     */
    public function deleted(EventRegistration $registration): void
    {
        $this->invalidateCache($registration);
    }

    /**
     * Invalidate all related cache entries
     */
    protected function invalidateCache(EventRegistration $registration): void
    {
        foreach ($this->cacheKeys as $key) {
            Cache::forget($key);
        }

        // Invalidate event-specific caches
        Cache::forget("event_stats_{$registration->event_id}");
        Cache::forget("event_checkin_stats_{$registration->event_id}");
    }
}
