<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Services\RegistrationService;
use App\Services\MassaService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class RegistrationController extends Controller
{
    public function __construct(
        protected RegistrationService $registrationService,
        protected MassaService $massaService
    ) {}

    /**
     * List registrations for an event
     */
    public function index(Request $request, Event $event): JsonResponse
    {
        $query = $event->registrations()
            ->with(['massa:id,nik,nama_lengkap,no_hp,jenis_kelamin']);

        // Status filter
        if ($status = $request->input('registration_status')) {
            $query->where('registration_status', $status);
        }

        if ($attendance = $request->input('attendance_status')) {
            $query->where('attendance_status', $attendance);
        }

        // Search by name or ticket
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                    ->orWhereHas('massa', fn($mq) => 
                        $mq->where('nama_lengkap', 'like', "%{$search}%")
                            ->orWhere('nik', 'like', "%{$search}%")
                    );
            });
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        $registrations = $query->paginate($request->input('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $registrations,
        ]);
    }

    /**
     * Register massa to event
     */
    public function store(Request $request, Event $event): JsonResponse
    {
        $validated = $request->validate([
            'nik' => 'required|string|size:16',
            'nama_lengkap' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'no_hp' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'alamat' => 'nullable|string',
            'rt' => 'nullable|string|max:5',
            'rw' => 'nullable|string|max:5',
            'province_id' => 'nullable|exists:provinces,id',
            'regency_id' => 'nullable|exists:regencies,id',
            'district_id' => 'nullable|exists:districts,id',
            'village_id' => 'nullable|exists:villages,id',
            'kode_pos' => 'nullable|string|max:10',
            'pekerjaan' => 'nullable|string|max:100',
            'custom_fields' => 'nullable|array',
        ]);

        // Validate NIK format
        if (!$this->massaService->validateNik($validated['nik'])) {
            return response()->json([
                'success' => false,
                'message' => 'Format NIK tidak valid. NIK harus 16 digit angka.',
            ], 422);
        }

        // Validate custom fields
        $customFieldErrors = $this->registrationService->validateCustomFields(
            $event, 
            $validated['custom_fields'] ?? []
        );

        if (!empty($customFieldErrors)) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi field kustom gagal.',
                'errors' => $customFieldErrors,
            ], 422);
        }

        try {
            $registration = $this->registrationService->register(
                $event,
                collect($validated)->except('custom_fields')->toArray(),
                $validated['custom_fields'] ?? [],
                auth()->id()
            );

            return response()->json([
                'success' => true,
                'message' => 'Registrasi berhasil.',
                'data' => $registration->load(['massa', 'event']),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get registration details
     */
    public function show(EventRegistration $registration): JsonResponse
    {
        $registration->load(['event', 'massa', 'checkinLogs']);

        return response()->json([
            'success' => true,
            'data' => $registration,
            'qr_code_url' => $registration->qr_code_path 
                ? Storage::disk('public')->url($registration->qr_code_path)
                : null,
        ]);
    }

    /**
     * Find registration by ticket number
     */
    public function findByTicket(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ticket_number' => 'required|string',
        ]);

        $registration = $this->registrationService->findByTicket($validated['ticket_number']);

        if (!$registration) {
            return response()->json([
                'success' => false,
                'message' => 'Tiket tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $registration,
        ]);
    }

    /**
     * Cancel registration
     */
    public function cancel(Request $request, EventRegistration $registration): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $this->registrationService->cancel($registration, $validated['reason'] ?? null);

            return response()->json([
                'success' => true,
                'message' => 'Registrasi berhasil dibatalkan.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Download ticket PDF
     */
    public function downloadTicket(EventRegistration $registration): mixed
    {
        if (!$registration->qr_code_path) {
            $this->registrationService->generateQrCode($registration);
        }

        $pdfPath = $this->registrationService->generateTicketPdf($registration);

        return Storage::disk('public')->download($pdfPath, "tiket-{$registration->ticket_number}.pdf");
    }

    /**
     * Batch generate tickets for event
     */
    public function batchGenerateTickets(Event $event): JsonResponse
    {
        $results = $this->registrationService->batchGenerateTickets($event);

        return response()->json([
            'success' => true,
            'message' => "Berhasil generate {$results['success']} tiket, {$results['failed']} gagal.",
            'data' => $results,
        ]);
    }

    /**
     * Check NIK availability
     */
    public function checkNik(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nik' => 'required|string|size:16',
            'event_id' => 'nullable|exists:events,id',
        ]);

        $nikExists = $this->massaService->nikExists($validated['nik']);
        
        $alreadyRegistered = false;
        if ($nikExists && isset($validated['event_id'])) {
            $massa = \App\Models\Massa::findByNik($validated['nik']);
            $alreadyRegistered = EventRegistration::where('event_id', $validated['event_id'])
                ->where('massa_id', $massa->id)
                ->exists();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'nik_exists' => $nikExists,
                'already_registered_in_event' => $alreadyRegistered,
            ],
        ]);
    }
}
