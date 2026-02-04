<?php

namespace App\Jobs;

use App\Models\Event;
use App\Services\RegistrationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateBatchTicketsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 600; // 10 minutes

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected Event $event,
        protected bool $regenerate = true
    ) {}

    /**
     * Execute the job.
     */
    public function handle(RegistrationService $registrationService): void
    {
        Log::info("Starting batch ticket generation for event: {$this->event->name} ({$this->event->id})");

        // Get all registrations that need tickets
        $query = $this->event->registrations()
            ->whereIn('registration_status', ['confirmed', 'pending', 'waitlist']);
        
        // If not regenerating, only get those without QR code
        if (!$this->regenerate) {
            $query->whereNull('qr_code_path');
        }

        // Process in chunks to manage memory
        $query->with(['event', 'massa'])->chunk(50, function ($registrations) use ($registrationService) {
            foreach ($registrations as $registration) {
                try {
                    // Ensure ticket number exists
                    if (empty($registration->ticket_number)) {
                        // We need to generate a ticket number if it doesn't exist.
                        // Ideally this logic should be in the service or model, but calling the private/protected method from here is hard.
                        // Assuming the service handles this or we rely on the service's methods.
                        // Let's defer to the logic we saw in the service, but we'll need to adapt it.
                        // Actually, the service has `generateQrCode` and `generateTicketPdf`.
                        // But `generateTicketNumber` was protected in the service context or not shown? 
                        // Looking back at the service code provided in earlier turn (Step 817), 
                        // `batchGenerateTickets` calls `$this->generateTicketNumber($event)`.
                        // BUT `generateTicketNumber` was NOT visible in the file view (it might be in a trait or updated/missing in the view).
                        // Wait, I missed checking `generateTicketNumber` implementation.
                        // However, the original `batchGenerateTickets` in Service used `$this->generateTicketNumber`.
                        // To avoid duplicating logic or breaking encapsulation, it's best if we assume the registration already has a ticket number OR we let the service handle it.
                        
                        // Refactor strategy: 
                        // Instead of reimplementing loop here entirely and missing dependencies, 
                        // Let's modify the Service to have a public method `generateTicketForRegistration` 
                        // or similar that handles one registration, including number generation.
                        
                        // For now, let's look at `generateQrCode` and `generateTicketPdf` in the service.
                        // They require `ticket_number` to be set.
                        
                        // Let's assume for this job we will just call a public method on the service 
                        // that does the work for a single registration.
                        // I will update the service to expose a method for single generation.
                        
                    }
                    
                    // We will update the service to handle "ensure ticket number" if needed.
                    // For now, let's just generate the PDF and QR.
                    
                    // But wait, the previous code was:
                    // if (empty($registration->ticket_number)) { 
                    //    $registration->ticket_number = $this->generateTicketNumber($event);
                    //    $registration->save();
                    // }
                    
                    // I need `generateTicketNumber`. 
                    // Let's check the Service again or add a method to Service.
                    
                } catch (\Exception $e) {
                    Log::error('Ticket generation failed for registration ' . $registration->id . ': ' . $e->getMessage());
                }
            }
            
            // Actually, better approach:
            // The logic to generate for ONE registration should be encapsulated in the Service.
            // I will add `generateTicket(EventRegistration $registration)` to the service.
            // And calling it from here.
            
            foreach ($registrations as $registration) {
                try {
                    $registrationService->ensureTicketGenerated($registration);
                } catch (\Exception $e) {
                    Log::error("Failed to generate ticket for registration {$registration->id}: {$e->getMessage()}");
                }
            }
        });

        Log::info("Completed batch ticket generation for event: {$this->event->id}");
    }
}
