<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\EventStatistic;
use App\Models\Massa;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RegistrationService
{
    public function __construct(
        protected MassaService $massaService,
        protected NotificationService $notificationService
    ) {}

    /**
     * Register massa to an event
     */
    public function register(Event $event, Massa|array $massaOrData, array $customFields = [], ?int $registeredBy = null): EventRegistration
    {
        // Check if event can accept registrations
        if (!$event->canRegister()) {
            throw new \Exception('Registrasi untuk event ini sudah ditutup.');
        }

        // Find or create massa (deduplication by NIK)
        $massa = $massaOrData instanceof Massa ? $massaOrData : $this->massaService->findOrCreateByNik($massaOrData);

        // Check if already registered
        $existing = EventRegistration::where('event_id', $event->id)
            ->where('massa_id', $massa->id)
            ->first();

        if ($existing) {
            throw new \Exception('Massa sudah terdaftar di event ini.');
        }

        // Determine initial status
        $status = $event->is_full ? 'waitlist' : 'pending';
        
        // Extract wa_consent from customFields (default to true for backwards compatibility)
        $waConsent = $customFields['wa_consent'] ?? true;
        unset($customFields['wa_consent']); // Don't store in custom_field_values

        return DB::transaction(function () use ($event, $massa, $customFields, $registeredBy, $status, $waConsent) {
            // Create registration
            $registration = EventRegistration::create([
                'event_id' => $event->id,
                'massa_id' => $massa->id,
                'registration_status' => $status,
                'custom_field_values' => $customFields,
                'registered_by' => $registeredBy,
            ]);

            // Auto-confirm if not waitlisted
            if ($status === 'pending') {
                $registration->confirm();
            }

            // Generate QR code
            $this->generateQrCode($registration);

            // Update statistics
            $this->updateRegistrationStats($event, $massa);

            // Send notification if enabled AND user consented
            if ($event->send_wa_notification && $massa->no_hp && $waConsent) {
                $this->notificationService->sendTicketNotification($registration);
            }

            return $registration->fresh();
        });
    }

    /**
     * Generate QR code for registration
     */
    public function generateQrCode(EventRegistration $registration): string
    {
        $qrData = json_encode([
            'type' => 'event_registration',
            'ticket' => $registration->ticket_number,
            'event' => $registration->event_id,
        ]);

        $filename = "qrcodes/{$registration->ticket_number}.svg";
        
        $qrCode = QrCode::format('svg')
            ->size(300)
            ->errorCorrection('H')
            ->generate($qrData);

        Storage::disk('public')->put($filename, $qrCode);

        $registration->update(['qr_code_path' => $filename]);

        return $filename;
    }

    /**
     * Generate ticket PDF for a registration
     */
    public function generateTicketPdf(EventRegistration $registration): string
    {
        $registration->loadMissing(['event', 'massa']);

        // Get QR Code as Base64
        $qrContent = Storage::disk('public')->get($registration->qr_code_path);
        $qrCodeBase64 = 'data:image/svg+xml;base64,' . base64_encode($qrContent);

        // Get Logo as Base64
        $logoPath = public_path('img/logo-gerindra.png');
        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $logoData = file_get_contents($logoPath);
            $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
        }

        $pdf = Pdf::loadView('pdf.ticket', [
            'registration' => $registration,
            'event' => $registration->event,
            'massa' => $registration->massa,
            'qrCodeBase64' => $qrCodeBase64,
            'logoBase64' => $logoBase64,
        ]);

        $filename = "tickets/{$registration->ticket_number}.pdf";
        Storage::disk('public')->put($filename, $pdf->output());

        return $filename;
    }

    /**
     * Ensure ticket artifiacts (Number, QR, PDF) are generated
     */
    public function ensureTicketGenerated(EventRegistration $registration): void
    {
        // 1. Ensure Ticket Number
        if (empty($registration->ticket_number)) { 
            // Load event if not loaded
            if (!$registration->relationLoaded('event')) {
                $registration->load('event');
            }
            
            $registration->ticket_number = $this->generateTicketNumber($registration->event);
            $registration->saveQuietly(); // Use saveQuietly to avoid triggering observers if any
        }
        
        // 2. Ensure QR Code
        if (empty($registration->qr_code_path) || !Storage::disk('public')->exists($registration->qr_code_path)) {
            $this->generateQrCode($registration);
        }
        
        // 3. Generate PDF (Overwrite existing to be safe)
        $this->generateTicketPdf($registration);
    }

    /**
     * Generate a unique ticket number
     */
    protected function generateTicketNumber(Event $event): string
    {
        $prefix = $event->code ?? 'EVT' . str_pad($event->id, 3, '0', STR_PAD_LEFT);
        
        do {
            $random = Str::upper(Str::random(6));
            $number = "{$prefix}-{$random}";
        } while (EventRegistration::where('ticket_number', $number)->exists());

        return $number;
    }

    /**
     * Batch generate tickets for an event
     * Now generates for ALL registrations (confirmed, pending, waitlist)
     * and regenerates if requested
     */
    public function batchGenerateTickets(Event $event, bool $regenerate = true): array
    {
        // Get all registrations that need tickets
        $query = $event->registrations()
            ->whereIn('registration_status', ['confirmed', 'pending', 'waitlist']);
        
        // If not regenerating, only get those without QR code
        if (!$regenerate) {
            $query->whereNull('qr_code_path');
        }
        
        $registrations = $query->get();

        $results = ['success' => 0, 'failed' => 0, 'total' => $registrations->count()];

        foreach ($registrations as $registration) {
            try {
                // Ensure ticket number exists
                if (empty($registration->ticket_number)) {
                    $registration->ticket_number = $this->generateTicketNumber($event);
                    $registration->save();
                }
                
                $this->generateQrCode($registration);
                $this->generateTicketPdf($registration);
                $results['success']++;
            } catch (\Exception $e) {
                \Log::error('Ticket generation failed for registration ' . $registration->id . ': ' . $e->getMessage());
                $results['failed']++;
            }
        }

        return $results;
    }

    /**
     * Cancel a registration
     */
    public function cancel(EventRegistration $registration, ?string $reason = null): void
    {
        if ($registration->attendance_status === 'checked_in') {
            throw new \Exception('Tidak dapat membatalkan registrasi yang sudah check-in.');
        }

        DB::transaction(function () use ($registration, $reason) {
            $wasConfirmed = $registration->registration_status === 'confirmed';

            $registration->update([
                'registration_status' => 'cancelled',
                'notes' => $reason,
            ]);

            // Decrement participant count if was confirmed
            if ($wasConfirmed) {
                $registration->event->decrementParticipants();

                // Check waitlist and promote
                $this->promoteFromWaitlist($registration->event);
            }
        });
    }

    /**
     * Promote next person from waitlist
     */
    protected function promoteFromWaitlist(Event $event): void
    {
        if (!$event->enable_waitlist) {
            return;
        }

        $waitlisted = $event->registrations()
            ->where('registration_status', 'waitlist')
            ->orderBy('created_at')
            ->first();

        if ($waitlisted) {
            $waitlisted->confirm();

            // Notify promoted massa
            if ($event->send_wa_notification && $waitlisted->massa->no_hp) {
                $this->notificationService->sendWaitlistPromotionNotification($waitlisted);
            }
        }
    }

    /**
     * Update registration statistics
     */
    protected function updateRegistrationStats(Event $event, Massa $massa): void
    {
        $stat = EventStatistic::getOrCreate($event->id, today(), now()->hour);
        $stat->incrementRegistrations();

        // Check if massa is new (first ever registration)
        $isNewMassa = $massa->registrations()->count() === 1;
        
        if ($isNewMassa) {
            $stat->incrementNewMassa();
        } else {
            $stat->incrementReturningMassa();
        }
    }

    /**
     * Get registration by ticket number
     */
    public function findByTicket(string $ticketNumber): ?EventRegistration
    {
        return EventRegistration::byTicket($ticketNumber)
            ->with(['event', 'massa'])
            ->first();
    }

    /**
     * Validate custom field data
     */
    public function validateCustomFields(Event $event, array $data): array
    {
        $errors = [];
        $customFields = $event->customFields()->active()->get();

        foreach ($customFields as $field) {
            $value = $data[$field->field_name] ?? null;

            if (!$field->validateValue($value)) {
                $errors[$field->field_name] = "Field {$field->field_label} tidak valid.";
            }
        }

        return $errors;
    }
}
