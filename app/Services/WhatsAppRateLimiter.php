<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * WhatsApp Rate Limiter Service
 * 
 * Manages daily sending limits to prevent WhatsApp account bans.
 * Uses Redis/Cache to track message counts per day.
 */
class WhatsAppRateLimiter
{
    protected string $cachePrefix = 'whatsapp_rate_limit';

    /**
     * Get the cache key for today's counter
     */
    protected function getDailyKey(): string
    {
        return $this->cachePrefix . ':daily:' . now()->format('Y-m-d');
    }

    /**
     * Get the current count of messages sent today
     */
    public function getSentToday(): int
    {
        return (int) Cache::get($this->getDailyKey(), 0);
    }

    /**
     * Get the daily limit from configuration
     */
    public function getDailyLimit(): int
    {
        return (int) config('whatsapp.safety.daily_limit', 500);
    }

    /**
     * Get remaining quota for today
     */
    public function getRemainingQuota(): int
    {
        return max(0, $this->getDailyLimit() - $this->getSentToday());
    }

    /**
     * Check if we can send more messages today
     */
    public function canSend(int $count = 1): bool
    {
        if (!config('whatsapp.safety.enabled', true)) {
            return true;
        }

        return $this->getRemainingQuota() >= $count;
    }

    /**
     * Increment the daily counter
     */
    public function increment(int $count = 1): int
    {
        $key = $this->getDailyKey();
        $current = $this->getSentToday();
        $newCount = $current + $count;

        // Cache until end of day (midnight)
        $secondsUntilMidnight = now()->endOfDay()->diffInSeconds(now());
        Cache::put($key, $newCount, $secondsUntilMidnight);

        return $newCount;
    }

    /**
     * Get rate limit status summary
     */
    public function getStatus(): array
    {
        return [
            'enabled' => config('whatsapp.safety.enabled', true),
            'daily_limit' => $this->getDailyLimit(),
            'sent_today' => $this->getSentToday(),
            'remaining' => $this->getRemainingQuota(),
            'can_send' => $this->canSend(),
            'resets_at' => now()->endOfDay()->toIso8601String(),
        ];
    }

    /**
     * Log rate limit warning
     */
    public function logLimitReached(): void
    {
        Log::warning('WhatsApp daily limit reached', [
            'limit' => $this->getDailyLimit(),
            'sent_today' => $this->getSentToday(),
            'date' => now()->toDateString(),
        ]);
    }

    /**
     * Calculate humanized random delay (in milliseconds)
     */
    public static function getRandomDelay(): int
    {
        $minDelay = (int) config('whatsapp.safety.min_delay_ms', 5000);
        $maxDelay = (int) config('whatsapp.safety.max_delay_ms', 15000);

        return rand($minDelay, $maxDelay);
    }

    /**
     * Get batch configuration
     */
    public static function getBatchConfig(): array
    {
        return [
            'size' => (int) config('whatsapp.safety.batch_size', 25),
            'pause_seconds' => (int) config('whatsapp.safety.batch_pause_seconds', 300),
        ];
    }

    /**
     * Check if batch pause is needed
     */
    public static function shouldPauseBatch(int $currentIndex, int $total): bool
    {
        if (!config('whatsapp.safety.enabled', true)) {
            return false;
        }

        $batchSize = (int) config('whatsapp.safety.batch_size', 25);
        
        // Only pause if we've completed a batch and not at the end
        return ($currentIndex + 1) % $batchSize === 0 && $currentIndex < $total - 1;
    }
}
