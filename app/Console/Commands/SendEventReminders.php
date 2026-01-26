<?php

namespace App\Console\Commands;

use App\Jobs\BulkWhatsAppJob;
use App\Models\Event;
use App\Models\EventRegistration;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendEventReminders extends Command
{
    protected $signature = 'whatsapp:event-reminders';

    protected $description = 'Send H-1 reminders and H+1 thank you messages for events';

    public function handle()
    {
        $this->sendReminders();
        $this->sendThankYou();
        
        return 0;
    }

    protected function sendReminders()
    {
        // Find events happening tomorrow (H-1)
        $tomorrow = now()->addDay()->format('Y-m-d');
        
        $events = Event::where('status', 'published')
            ->whereDate('event_date', $tomorrow)
            ->get();

        foreach ($events as $event) {
            $registrations = EventRegistration::where('event_id', $event->id)
                ->where('status', 'confirmed')
                ->with('massa')
                ->get();

            if ($registrations->isEmpty()) {
                $this->info("No registrations for event: {$event->name}");
                continue;
            }

            $phones = $registrations->map(fn($r) => $r->massa?->no_hp)
                ->filter()
                ->unique()
                ->values()
                ->toArray();

            if (empty($phones)) {
                continue;
            }

            $message = "Halo Kader Gerindra! 🇮🇩\n\n";
            $message .= "🔔 PENGINGAT - Event Besok!\n\n";
            $message .= "📌 {$event->name}\n";
            $message .= "📅 " . $event->event_date->format('d M Y, H:i') . " WIB\n";
            $message .= "📍 {$event->location}\n\n";
            $message .= "Jangan lupa membawa KTP dan tiket registrasi.\n\n";
            $message .= "Sampai jumpa! ✊\nDPD Gerindra DIY";

            BulkWhatsAppJob::dispatch($phones, $message);

            $this->info("Dispatched H-1 reminder for {$event->name} to " . count($phones) . " recipients");
            
            Log::info('H-1 reminder sent', [
                'event_id' => $event->id,
                'recipient_count' => count($phones),
            ]);
        }
    }

    protected function sendThankYou()
    {
        // Find events that happened yesterday (H+1)
        $yesterday = now()->subDay()->format('Y-m-d');
        
        $events = Event::whereIn('status', ['completed', 'ongoing'])
            ->whereDate('event_date', $yesterday)
            ->get();

        foreach ($events as $event) {
            $registrations = EventRegistration::where('event_id', $event->id)
                ->where('checked_in', true)
                ->with('massa')
                ->get();

            if ($registrations->isEmpty()) {
                $this->info("No check-ins for event: {$event->name}");
                continue;
            }

            $phones = $registrations->map(fn($r) => $r->massa?->no_hp)
                ->filter()
                ->unique()
                ->values()
                ->toArray();

            if (empty($phones)) {
                continue;
            }

            $message = "Terima Kasih! 🙏\n\n";
            $message .= "Kami mengucapkan terima kasih atas kehadiran Anda di:\n";
            $message .= "📌 {$event->name}\n\n";
            $message .= "Partisipasi Anda sangat berarti bagi perjuangan kita bersama.\n\n";
            $message .= "Sampai jumpa di acara berikutnya!\n\n";
            $message .= "Salam Perjuangan! ✊\nDPD Gerindra DIY";

            BulkWhatsAppJob::dispatch($phones, $message);

            $this->info("Dispatched H+1 thank you for {$event->name} to " . count($phones) . " recipients");
            
            Log::info('H+1 thank you sent', [
                'event_id' => $event->id,
                'recipient_count' => count($phones),
            ]);
        }
    }
}
