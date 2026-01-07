<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Services\CheckinService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CheckinController extends Controller
{
    public function __construct(
        protected CheckinService $checkinService
    ) {}

    /**
     * Check in by QR code scan
     */
    public function scanQr(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'qr_data' => 'required|string',
        ]);

        // Parse QR code
        $qrData = $this->checkinService->parseQrCode($validated['qr_data']);

        if (!$qrData) {
            return response()->json([
                'success' => false,
                'message' => 'QR Code tidak valid.',
            ], 422);
        }

        try {
            $registration = $this->checkinService->checkinByTicket(
                $qrData['ticket'],
                auth()->id(),
                'qr_scan',
                [
                    'device_info' => $request->header('User-Agent'),
                    'ip_address' => $request->ip(),
                    'latitude' => $request->input('latitude'),
                    'longitude' => $request->input('longitude'),
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Check-in berhasil!',
                'data' => [
                    'registration' => $registration,
                    'massa' => $registration->massa,
                    'checked_in_at' => $registration->checked_in_at->format('H:i:s'),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Check in by ticket number (manual input)
     */
    public function checkinByTicket(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ticket_number' => 'required|string',
        ]);

        try {
            $registration = $this->checkinService->checkinByTicket(
                $validated['ticket_number'],
                auth()->id(),
                'manual_input',
                [
                    'device_info' => $request->header('User-Agent'),
                    'ip_address' => $request->ip(),
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Check-in berhasil!',
                'data' => [
                    'registration' => $registration,
                    'massa' => $registration->massa,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Check in by NIK
     */
    public function checkinByNik(Request $request, Event $event): JsonResponse
    {
        $validated = $request->validate([
            'nik' => 'required|string|size:16',
        ]);

        $registration = EventRegistration::where('event_id', $event->id)
            ->whereHas('massa', fn($q) => $q->where('nik', $validated['nik']))
            ->first();

        if (!$registration) {
            return response()->json([
                'success' => false,
                'message' => 'NIK tidak terdaftar di event ini.',
            ], 404);
        }

        try {
            $registration = $this->checkinService->checkinByRegistration(
                $registration,
                auth()->id(),
                'nik_input',
                [
                    'device_info' => $request->header('User-Agent'),
                    'ip_address' => $request->ip(),
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Check-in berhasil!',
                'data' => [
                    'registration' => $registration,
                    'massa' => $registration->massa,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Manual override check-in status
     */
    public function manualOverride(Request $request, EventRegistration $registration): JsonResponse
    {
        $validated = $request->validate([
            'action' => 'required|in:check_in,undo_checkin',
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $this->checkinService->manualOverride(
                $registration,
                $validated['action'],
                auth()->id(),
                $validated['reason'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Status check-in berhasil diupdate.',
                'data' => $registration->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get real-time attendance stats
     */
    public function attendanceStats(Event $event): JsonResponse
    {
        $stats = $this->checkinService->getAttendanceStats($event);

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Get hourly check-in data for graph
     */
    public function hourlyCheckins(Request $request, Event $event): JsonResponse
    {
        $date = $request->input('date', today()->toDateString());
        
        $hourlyData = $this->checkinService->getHourlyCheckins($event, $date);

        return response()->json([
            'success' => true,
            'data' => [
                'date' => $date,
                'hourly' => $hourlyData,
                'labels' => array_map(fn($h) => sprintf('%02d:00', $h), array_keys($hourlyData)),
                'values' => array_values($hourlyData),
            ],
        ]);
    }

    /**
     * Get recent check-ins for live feed
     */
    public function recentCheckins(Request $request, Event $event): JsonResponse
    {
        $limit = $request->input('limit', 10);
        
        $recent = $this->checkinService->getRecentCheckins($event, $limit);

        return response()->json([
            'success' => true,
            'data' => $recent,
        ]);
    }

    /**
     * Validate ticket without check-in (preview)
     */
    public function validateTicket(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ticket_number' => 'required|string',
        ]);

        $registration = EventRegistration::byTicket($validated['ticket_number'])
            ->with(['event', 'massa'])
            ->first();

        if (!$registration) {
            return response()->json([
                'success' => false,
                'message' => 'Tiket tidak ditemukan.',
                'valid' => false,
            ], 404);
        }

        $canCheckIn = $registration->canCheckIn();
        $reason = null;

        if (!$canCheckIn) {
            if ($registration->registration_status !== 'confirmed') {
                $reason = 'Registrasi belum dikonfirmasi';
            } elseif ($registration->attendance_status === 'checked_in') {
                $reason = 'Sudah check-in pada ' . $registration->checked_in_at->format('d M Y H:i');
            }
        }

        return response()->json([
            'success' => true,
            'valid' => $canCheckIn,
            'reason' => $reason,
            'data' => [
                'registration' => $registration,
                'event' => $registration->event,
                'massa' => $registration->massa,
            ],
        ]);
    }
}
