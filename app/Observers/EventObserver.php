<?php

namespace App\Observers;

use App\Models\Event;
use Illuminate\Support\Facades\Cache;

class EventObserver
{
    /**
     * Cache keys to invalidate when event changes
     */
    protected array $cacheKeys = [
        'dashboard_stats',
        'dashboard_recent_events',
        'dashboard_upcoming_events',
        'dashboard_top_events',
    ];

    /**
     * Handle the Event "created" event.
     */
    public function created(Event $event): void
    {
        $this->invalidateCache();
    }

    /**
     * Handle the Event "updated" event.
     */
    public function updated(Event $event): void
    {
        $this->invalidateCache();
        
        // Invalidate specific event cache
        Cache::forget("event_{$event->id}");
        Cache::forget("event_stats_{$event->id}");
    }

    /**
     * Handle the Event "deleted" event.
     */
    public function deleted(Event $event): void
    {
        $this->invalidateCache();
        
        // Invalidate specific event cache
        Cache::forget("event_{$event->id}");
        Cache::forget("event_stats_{$event->id}");
    }

    /**
     * Handle the Event "restored" event.
     */
    public function restored(Event $event): void
    {
        $this->invalidateCache();
    }

    /**
     * Handle the Event "force deleted" event.
     */
    public function forceDeleted(Event $event): void
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
