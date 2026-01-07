<?php

namespace App\Services;

use App\Models\EventRegistration;
use App\Models\LotteryPrize;
use App\Models\NotificationLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    protected ?string $waGatewayUrl;
    protected ?string $waGatewayToken;

    public function __construct()
    {
        $this->waGatewayUrl = config('services.wa_gateway.url');
        $this->waGatewayToken = config('services.wa_gateway.token');
    }

    /**
     * Send ticket notification via WhatsApp
     */
    public function sendTicketNotification(EventRegistration $registration): NotificationLog
    {
        $registration->load(['event', 'massa']);
        
        $message = $this->buildTicketMessage($registration);

        return $this->sendWhatsApp(
            $registration->massa->no_hp,
            $message,
            'ticket',
            $registration
        );
    }

    /**
     * Send lottery win notification
     */
    public function sendLotteryWinNotification(EventRegistration $registration, LotteryPrize $prize): NotificationLog
    {
        $registration->load(['event', 'massa']);
        
        $message = $this->buildLotteryWinMessage($registration, $prize);

        return $this->sendWhatsApp(
            $registration->massa->no_hp,
            $message,
            'lottery_win',
            $registration
        );
    }

    /**
     * Send waitlist promotion notification
     */
    public function sendWaitlistPromotionNotification(EventRegistration $registration): NotificationLog
    {
        $registration->load(['event', 'massa']);
        
        $message = $this->buildWaitlistPromotionMessage($registration);

        return $this->sendWhatsApp(
            $registration->massa->no_hp,
            $message,
            'reminder',
            $registration
        );
    }

    /**
     * Send event reminder
     */
    public function sendEventReminder(EventRegistration $registration): NotificationLog
    {
        $registration->load(['event', 'massa']);
        
        $message = $this->buildReminderMessage($registration);

        return $this->sendWhatsApp(
            $registration->massa->no_hp,
            $message,
            'reminder',
            $registration
        );
    }

    /**
     * Send WhatsApp message via gateway
     */
    protected function sendWhatsApp(
        string $phoneNumber,
        string $message,
        string $type,
        ?EventRegistration $registration = null
    ): NotificationLog {
        // Format phone number
        $phoneNumber = $this->formatPhoneNumber($phoneNumber);

        // Create log entry
        $log = NotificationLog::create([
            'event_id' => $registration?->event_id,
            'massa_id' => $registration?->massa_id,
            'event_registration_id' => $registration?->id,
            'channel' => 'whatsapp',
            'recipient' => $phoneNumber,
            'type' => $type,
            'message' => $message,
            'status' => 'pending',
        ]);

        // If no gateway configured, just log
        if (empty($this->waGatewayUrl)) {
            Log::info('WA Gateway not configured', [
                'recipient' => $phoneNumber,
                'message' => $message,
            ]);
            return $log;
        }

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->waGatewayToken,
                    'Content-Type' => 'application/json',
                ])
                ->post($this->waGatewayUrl . '/send', [
                    'phone' => $phoneNumber,
                    'message' => $message,
                ]);

            if ($response->successful()) {
                $log->markAsSent();
            } else {
                $log->markAsFailed($response->body());
            }
        } catch (\Exception $e) {
            Log::error('WA Gateway error', [
                'error' => $e->getMessage(),
                'recipient' => $phoneNumber,
            ]);
            $log->markAsFailed($e->getMessage());
        }

        return $log;
    }

    /**
     * Format phone number to international format
     */
    protected function formatPhoneNumber(string $phone): string
    {
        // Remove non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Convert 08xx to 628xx
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }
        
        // Add 62 if not present
        if (!str_starts_with($phone, '62')) {
            $phone = '62' . $phone;
        }

        return $phone;
    }

    /**
     * Build ticket notification message
     */
    protected function buildTicketMessage(EventRegistration $registration): string
    {
        $event = $registration->event;
        $massa = $registration->massa;

        return <<<MSG
🎫 *TIKET KEHADIRAN*

Halo *{$massa->nama_lengkap}*,

Anda telah terdaftar untuk event:
📌 *{$event->name}*

📅 Tanggal: {$event->event_start->format('d M Y')}
⏰ Waktu: {$event->event_start->format('H:i')} WIB
📍 Lokasi: {$event->venue_name}
{$event->venue_address}

🎟️ Nomor Tiket: *{$registration->ticket_number}*

Tunjukkan tiket ini saat check-in di lokasi event.

---
Partai Gerindra
MSG;
    }

    /**
     * Build lottery win message
     */
    protected function buildLotteryWinMessage(EventRegistration $registration, LotteryPrize $prize): string
    {
        $event = $registration->event;
        $massa = $registration->massa;

        return <<<MSG
🎉 *SELAMAT! ANDA MENANG UNDIAN!*

Halo *{$massa->nama_lengkap}*,

Anda memenangkan undian di event *{$event->name}*!

🏆 Hadiah: *{$prize->name}*

Segera klaim hadiah Anda di meja panitia dengan menunjukkan tiket atau KTP.

---
Partai Gerindra
MSG;
    }

    /**
     * Build waitlist promotion message
     */
    protected function buildWaitlistPromotionMessage(EventRegistration $registration): string
    {
        $event = $registration->event;
        $massa = $registration->massa;

        return <<<MSG
✅ *KABAR BAIK!*

Halo *{$massa->nama_lengkap}*,

Anda telah dipromosikan dari daftar tunggu dan sekarang terdaftar untuk event:
📌 *{$event->name}*

📅 Tanggal: {$event->event_start->format('d M Y')}
⏰ Waktu: {$event->event_start->format('H:i')} WIB
📍 Lokasi: {$event->venue_name}

🎟️ Nomor Tiket: *{$registration->ticket_number}*

---
Partai Gerindra
MSG;
    }

    /**
     * Build reminder message
     */
    protected function buildReminderMessage(EventRegistration $registration): string
    {
        $event = $registration->event;
        $massa = $registration->massa;

        return <<<MSG
⏰ *PENGINGAT EVENT*

Halo *{$massa->nama_lengkap}*,

Jangan lupa event besok:
📌 *{$event->name}*

📅 Tanggal: {$event->event_start->format('d M Y')}
⏰ Waktu: {$event->event_start->format('H:i')} WIB
📍 Lokasi: {$event->venue_name}
{$event->venue_address}

Pastikan membawa tiket digital atau KTP untuk proses check-in.

---
Partai Gerindra
MSG;
    }

    /**
     * Retry failed notifications
     */
    public function retryFailed(int $limit = 50): array
    {
        $failed = NotificationLog::failed()
            ->where('created_at', '>', now()->subHours(24))
            ->limit($limit)
            ->get();

        $results = ['success' => 0, 'failed' => 0];

        foreach ($failed as $log) {
            try {
                $response = Http::timeout(30)
                    ->withHeaders([
                        'Authorization' => 'Bearer ' . $this->waGatewayToken,
                    ])
                    ->post($this->waGatewayUrl . '/send', [
                        'phone' => $log->recipient,
                        'message' => $log->message,
                    ]);

                if ($response->successful()) {
                    $log->markAsSent();
                    $results['success']++;
                } else {
                    $results['failed']++;
                }
            } catch (\Exception $e) {
                $results['failed']++;
            }
        }

        return $results;
    }
}
