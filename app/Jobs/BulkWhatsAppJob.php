<?php

namespace App\Jobs;

use App\Services\WhatsAppService;
use App\Services\WhatsAppRateLimiter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class BulkWhatsAppJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 1;

    /**
     * The number of seconds the job can run before timing out.
     * Increased to 6 hours due to batch processing pauses.
     */
    public int $timeout = 21600;

    protected array $phones;
    protected string $message;
    protected ?string $batchId;

    /**
     * Create a new job instance.
     */
    public function __construct(array $phones, string $message, ?string $batchId = null)
    {
        $this->phones = $phones;
        $this->message = $message;
        $this->batchId = $batchId ?? uniqid('blast_');
    }

    /**
     * Execute the job.
     */
    public function handle(WhatsAppService $whatsapp, WhatsAppRateLimiter $rateLimiter): void
    {
        $total = count($this->phones);
        $success = 0;
        $failed = 0;
        $skipped = 0;
        $safetyEnabled = config('whatsapp.safety.enabled', true);

        Log::info("BulkWhatsApp [{$this->batchId}]: Starting to send {$total} messages", [
            'safety_enabled' => $safetyEnabled,
            'batch_id' => $this->batchId,
        ]);

        // Check daily limit before starting
        if ($safetyEnabled && !$rateLimiter->canSend($total)) {
            $remaining = $rateLimiter->getRemainingQuota();
            Log::warning("BulkWhatsApp [{$this->batchId}]: Daily limit would be exceeded", [
                'requested' => $total,
                'remaining_quota' => $remaining,
            ]);
            
            // Only send what we can
            if ($remaining <= 0) {
                $rateLimiter->logLimitReached();
                $this->updateStatus('limit_reached', $total, 0, 0, 0, $total);
                return;
            }
            
            // Truncate phones to remaining quota
            $this->phones = array_slice($this->phones, 0, $remaining);
            $total = count($this->phones);
            $skipped = count($this->phones) - $total;
        }

        // Cache initial status
        $this->updateStatus('running', $total, 0, 0, 0, $skipped);

        $batchConfig = WhatsAppRateLimiter::getBatchConfig();

        // Pre-fetch massa data to avoid N+1 queries
        $massaMap = \App\Models\Massa::whereIn('no_hp', $this->phones)
            ->with(['regency', 'province'])
            ->get()
            ->keyBy('no_hp');

        foreach ($this->phones as $index => $phone) {
            try {
                // Personalize message if massa data exists
                $massa = $massaMap->get($phone);
                $personalizedMessage = $massa 
                    ? $this->formatMessage($this->message, $massa) 
                    : $this->cleanMessage($this->message);

                $result = $whatsapp->sendText($phone, $personalizedMessage);

                if ($result['success']) {
                    $success++;
                    $rateLimiter->increment(1);
                } else {
                    $failed++;
                    Log::warning("BulkWhatsApp [{$this->batchId}]: Failed to send to {$phone}", [
                        'error' => $result['error'] ?? 'Unknown error',
                    ]);
                }
            } catch (\Exception $e) {
                $failed++;
                Log::error("BulkWhatsApp [{$this->batchId}]: Exception sending to {$phone}", [
                    'error' => $e->getMessage(),
                ]);
            }

            // Update status in real-time (every message)
            $this->updateStatus('running', $total, $index + 1, $success, $failed, $skipped);

            // Safety delay between messages (if not last message)
            if ($index < $total - 1) {
                if ($safetyEnabled) {
                    // Humanized random delay
                    $delay = WhatsAppRateLimiter::getRandomDelay();
                    usleep($delay * 1000);

                    // Batch pause check
                    if (WhatsAppRateLimiter::shouldPauseBatch($index, $total)) {
                        $pauseSeconds = $batchConfig['pause_seconds'];
                        Log::info("BulkWhatsApp [{$this->batchId}]: Batch pause for {$pauseSeconds} seconds", [
                            'completed_batch' => floor(($index + 1) / $batchConfig['size']),
                            'messages_sent' => $index + 1,
                        ]);
                        
                        $this->updateStatus('batch_pause', $total, $index + 1, $success, $failed, $skipped, $pauseSeconds);
                        sleep($pauseSeconds);
                    }
                } else {
                    // Legacy fixed delay (2 seconds)
                    usleep(2000 * 1000);
                }
            }

            // Log progress every 25 messages
            if (($index + 1) % 25 === 0) {
                $progress = $index + 1;
                $percentage = round(($progress / $total) * 100);
                Log::info("BulkWhatsApp [{$this->batchId}]: Progress {$progress}/{$total} ({$percentage}%)");
            }
        }

        Log::info("BulkWhatsApp [{$this->batchId}]: Completed", [
            'total' => $total,
            'success' => $success,
            'failed' => $failed,
            'skipped' => $skipped,
        ]);

        // Store final result
        $this->updateStatus('completed', $total, $total, $success, $failed, $skipped);
        
        cache()->put('bulk_whatsapp_last_result', [
            'batch_id' => $this->batchId,
            'total' => $total,
            'success' => $success,
            'failed' => $failed,
            'skipped' => $skipped,
            'completed_at' => now()->toIso8601String(),
        ], now()->addHours(24));
    }

    /**
     * Update status in cache for real-time monitoring
     */
    protected function updateStatus(
        string $status,
        int $total,
        int $processed,
        int $success,
        int $failed,
        int $skipped,
        ?int $pauseSeconds = null
    ): void {
        $data = [
            'batch_id' => $this->batchId,
            'status' => $status,
            'total' => $total,
            'processed' => $processed,
            'success' => $success,
            'failed' => $failed,
            'skipped' => $skipped,
            'percentage' => $total > 0 ? round(($processed / $total) * 100) : 0,
            'updated_at' => now()->toIso8601String(),
        ];

        if ($pauseSeconds !== null) {
            $data['pause_until'] = now()->addSeconds($pauseSeconds)->toIso8601String();
        }

        cache()->put('bulk_whatsapp_status', $data, now()->addHours(6));
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("BulkWhatsApp [{$this->batchId}]: Job failed", [
            'total_phones' => count($this->phones),
            'error' => $exception->getMessage(),
        ]);

        $this->updateStatus('failed', count($this->phones), 0, 0, 0, 0);
    }

    /**
     * Replace variables in message with massa data
     */
    protected function formatMessage(string $message, \App\Models\Massa $massa): string
    {
        $vars = [
            '{nama}' => $massa->nama_lengkap,
            '{name}' => $massa->nama_lengkap,
            '{nik}' => $massa->nik,
            '{no_hp}' => $massa->no_hp,
            '{panggilan}' => $massa->jenis_kelamin === 'L' ? 'Bapak' : 'Ibu',
            '{lokasi}' => $massa->regency?->name ?? ($massa->province?->name ?? ''),
        ];

        return str_replace(array_keys($vars), array_values($vars), $message);
    }

    /**
     * Clean variables if no data found (fallback)
     */
    protected function cleanMessage(string $message): string
    {
        $vars = ['{nama}', '{name}', '{nik}', '{no_hp}', '{panggilan}', '{lokasi}'];
        // Replace with generic or empty
        return str_replace($vars, '', $message);
    }
}
