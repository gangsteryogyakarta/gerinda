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
    public $timeout = 1800; // 30 minutes

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
        // Prevent timeout if running synchronously
        set_time_limit(0);
        ini_set('memory_limit', '512M');

        Log::info("Starting batch ticket generation for event: {$this->event->name} ({$this->event->id})");

        // Get all registrations that need tickets
        $query = $this->event->registrations()
            ->whereIn('registration_status', ['confirmed', 'pending', 'waitlist']);
        
        // If not regenerating, only get those without QR code
        if (!$this->regenerate) {
            $query->whereNull('qr_code_path');
        }

        // Process in smaller chunks to manage memory and prevent N+1 issues
        // Eager load event and massa to avoid N+1 in the loop
        $query->with(['event', 'massa'])->chunk(20, function ($registrations) use ($registrationService) {
            foreach ($registrations as $registration) {
                try {
                    // Re-assign the eager loaded event to avoid reloading it inside logic
                    // (Though loadMissing checks this, explicit set helps safety)
                    if (!$registration->relationLoaded('event')) {
                        $registration->setRelation('event', $this->event);
                    }

                    $registrationService->ensureTicketGenerated($registration);
                } catch (\Exception $e) {
                    Log::error("Failed to generate ticket for registration {$registration->id}: {$e->getMessage()}");
                }
            }
        });

        Log::info("Completed batch ticket generation for event: {$this->event->id}");
    }
}
