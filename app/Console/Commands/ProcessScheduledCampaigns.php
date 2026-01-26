<?php

namespace App\Console\Commands;

use App\Jobs\BulkWhatsAppJob;
use App\Models\WhatsappCampaign;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessScheduledCampaigns extends Command
{
    protected $signature = 'whatsapp:process-campaigns';

    protected $description = 'Process scheduled WhatsApp campaigns that are due';

    public function handle()
    {
        $campaigns = WhatsappCampaign::dueToSend()->get();

        if ($campaigns->isEmpty()) {
            $this->info('No campaigns due to send.');
            return 0;
        }

        foreach ($campaigns as $campaign) {
            $this->info("Processing campaign: {$campaign->name}");

            try {
                // Update status to sending
                $campaign->update([
                    'status' => 'sending',
                    'started_at' => now(),
                ]);

                // Get recipients
                $recipients = $campaign->recipients;
                
                if (empty($recipients)) {
                    $this->warn("Campaign {$campaign->id} has no recipients.");
                    $campaign->update(['status' => 'completed', 'completed_at' => now()]);
                    continue;
                }

                // Dispatch bulk job
                BulkWhatsAppJob::dispatch(
                    $recipients,
                    $campaign->message,
                    $campaign->id
                );

                $this->info("Dispatched {$campaign->recipient_count} messages for campaign: {$campaign->name}");

                Log::info('Scheduled campaign dispatched', [
                    'campaign_id' => $campaign->id,
                    'recipient_count' => $campaign->recipient_count,
                ]);

            } catch (\Exception $e) {
                $this->error("Failed to process campaign {$campaign->id}: {$e->getMessage()}");
                
                $campaign->update(['status' => 'cancelled']);
                
                Log::error('Scheduled campaign failed', [
                    'campaign_id' => $campaign->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return 0;
    }
}
