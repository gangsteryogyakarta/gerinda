<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WhatsappMessageLog;
use Illuminate\Support\Facades\Log;

class WhatsappWebhookController extends Controller
{
    /**
     * Handle incoming webhooks from WAHA
     */
    public function handle(Request $request)
    {
        $data = $request->all();
        
        // Log incoming webhook for debugging
        Log::info('WhatsApp Webhook:', $data);

        if (!isset($data['event'])) {
            return response()->json(['status' => 'ignored', 'reason' => 'no_event']);
        }

        switch ($data['event']) {
            case 'message.ack':
                $this->handleMessageAck($data['payload']);
                break;
                
            case 'message':
                // Handle incoming messages if needed in future
                break;
        }
        
        return response()->json(['status' => 'success']);
    }

    protected function handleMessageAck($payload)
    {
        $messageId = $payload['id'];
        
        // WAHA Ack values:
        // 0: pending
        // 1: sent (server ack)
        // 2: delivered
        // 3: read
        // 4: played
        // -1: failed
        
        $ack = $payload['ack'];
        
        $statusMap = [
            -1 => 'failed',
            0 => 'pending',
            1 => 'sent',
            2 => 'delivered',
            3 => 'read',
            4 => 'read'
        ];

        $status = $statusMap[$ack] ?? 'sent';
        
        $log = WhatsappMessageLog::where('message_id', $messageId)->first();
        
        if ($log) {
            $log->status = $status;
            
            // Update timestamps based on status
            $now = now();
            
            if ($status === 'sent' && !$log->sent_at) {
                $log->sent_at = $now; // Usually set on creation, but update if null
            }
            
            if ($status === 'delivered' && !$log->delivered_at) {
                $log->delivered_at = $now;
            }
            
            if ($status === 'read' && !$log->read_at) {
                $log->read_at = $now;
                // If we missed delivered event, ensure delivered_at is set
                if (!$log->delivered_at) {
                    $log->delivered_at = $now;
                }
            }
            
            if ($status === 'failed' && isset($payload['error'])) {
                $log->failure_reason = json_encode($payload['error']);
            }

            $log->save();
        }
    }
}
