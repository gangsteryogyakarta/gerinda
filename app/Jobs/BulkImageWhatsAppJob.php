<?php

namespace App\Jobs;

use App\Services\WhatsAppService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class BulkImageWhatsAppJob implements ShouldQueue
{
    use Queueable;

    public $tries = 1;
    public $timeout = 3600; // 1 hour max

    protected array $phones;
    protected string $imageUrl;
    protected string $caption;

    public function __construct(array $phones, string $imageUrl, string $caption = '')
    {
        $this->phones = $phones;
        $this->imageUrl = $imageUrl;
        $this->caption = $caption;
    }

    public function handle(WhatsAppService $whatsapp): void
    {
        $total = count($this->phones);
        $sent = 0;
        $failed = 0;

        Log::info("Starting bulk image send to {$total} recipients");

        foreach ($this->phones as $index => $phone) {
            try {
                cache()->put('bulk_whatsapp_status', [
                    'type' => 'image',
                    'total' => $total,
                    'sent' => $sent,
                    'failed' => $failed,
                    'current' => $phone,
                    'progress' => round(($index / $total) * 100),
                ], 3600);

                $result = $whatsapp->sendImage($phone, $this->imageUrl, $this->caption);

                if ($result['success'] ?? false) {
                    $sent++;
                } else {
                    $failed++;
                    Log::warning("Failed to send image to {$phone}", $result);
                }

                // Rate limiting: 3-5 second delay
                usleep(rand(3000000, 5000000));

            } catch (\Exception $e) {
                $failed++;
                Log::error("Error sending image to {$phone}: " . $e->getMessage());
            }
        }

        cache()->put('bulk_whatsapp_last_result', [
            'type' => 'image',
            'total' => $total,
            'sent' => $sent,
            'failed' => $failed,
            'completed_at' => now()->toIso8601String(),
        ], 86400);

        cache()->forget('bulk_whatsapp_status');

        Log::info("Bulk image send completed: {$sent} sent, {$failed} failed");
    }
}
