<?php

namespace App\Jobs;

use App\Services\WhatsAppService;
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
     */
    public int $timeout = 7200; // 2 hours for large batches

    protected array $phones;
    protected string $message;
    protected int $delayMs;

    /**
     * Create a new job instance.
     */
    public function __construct(array $phones, string $message, int $delayMs = 1500)
    {
        $this->phones = $phones;
        $this->message = $message;
        $this->delayMs = $delayMs;
    }

    /**
     * Execute the job.
     */
    public function handle(WhatsAppService $whatsapp): void
    {
        $total = count($this->phones);
        $success = 0;
        $failed = 0;

        Log::info("BulkWhatsApp: Starting to send {$total} messages");
        
        // Cache initial status
        cache()->put('bulk_whatsapp_status', [
            'status' => 'running',
            'total' => $total,
            'processed' => 0,
            'success' => 0,
            'failed' => 0,
            'start_time' => now()->timestamp,
        ], now()->addHours(2));

        foreach ($this->phones as $index => $phone) {
            try {
                $result = $whatsapp->sendText($phone, $this->message);
                
                if ($result['success']) {
                    $success++;
                } else {
                    $failed++;
                    Log::warning("BulkWhatsApp: Failed to send to {$phone}", [
                        'error' => $result['error'] ?? 'Unknown error'
                    ]);
                }
            } catch (\Exception $e) {
                $failed++;
                Log::error("BulkWhatsApp: Exception sending to {$phone}", [
                    'error' => $e->getMessage()
                ]);
            }

            // Delay between messages
            if ($index < $total - 1) {
                usleep($this->delayMs * 1000);
            }

            // Log progress every 50 messages
            if (($index + 1) % 50 === 0) {
                $progress = $index + 1;
                Log::info("BulkWhatsApp: Progress {$progress}/{$total}");
            }
        }

        Log::info("BulkWhatsApp: Completed. Success: {$success}, Failed: {$failed}");

        // Store result in cache for dashboard display
        cache()->put('bulk_whatsapp_last_result', [
            'total' => $total,
            'success' => $success,
            'failed' => $failed,
            'completed_at' => now()->toISOString(),
        ], now()->addHours(24));
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('BulkWhatsApp: Job failed', [
            'total_phones' => count($this->phones),
            'error' => $exception->getMessage(),
        ]);
    }
}
