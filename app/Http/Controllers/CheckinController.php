<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Services\CheckinService;
use Illuminate\Http\Request;

class CheckinController extends Controller
{
    public function __construct(
        protected CheckinService $checkinService
    ) {}

    /**
     * Show check-in dashboard
     */
    public function index()
    {
        $activeEvents = Event::whereIn('status', ['published', 'ongoing'])
            ->where('enable_checkin', true)
            ->orderBy('event_start')
            ->get();

        return view('checkin.index', compact('activeEvents'));
    }

    /**
     * Show check-in page for specific event
     */
    public function event(Event $event)
    {
        if (!$event->enable_checkin) {
            return redirect()->route('checkin.index')
                ->with('error', 'Check-in tidak diaktifkan untuk event ini.');
        }

        $stats = $this->checkinService->getAttendanceStats($event);
        $recentCheckins = $this->checkinService->getRecentCheckins($event, 10);

        return view('checkin.event', compact('event', 'stats', 'recentCheckins'));
    }

    /**
     * Process check-in
     */
    public function process(Request $request)
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
            ], 404);
        }

        try {
            $this->checkinService->checkinByRegistration(
                $registration,
                auth()->id(),
                'qr_scan',
                [
                    'device_info' => $request->header('User-Agent'),
                    'ip_address' => $request->ip(),
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Check-in berhasil!',
                'data' => [
                    'nama' => $registration->massa->nama_lengkap,
                    'ticket' => $registration->ticket_number,
                    'checked_in_at' => now()->format('H:i:s'),
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
     * Manual check-in from registration list
     */
    public function manualCheckin(EventRegistration $registration)
    {
        try {
            $this->checkinService->checkinByRegistration(
                $registration,
                auth()->id(),
                'manual',
                []
            );

            return back()->with('success', 'Check-in berhasil untuk ' . $registration->massa->nama_lengkap);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Get real-time stats via AJAX
     */
    public function liveStats(Event $event)
    {
        $stats = $this->checkinService->getAttendanceStats($event);
        $recentCheckins = $this->checkinService->getRecentCheckins($event, 5);

        return response()->json([
            'stats' => $stats,
            'recent' => $recentCheckins->map(fn($log) => [
                'nama' => $log->massa->nama_lengkap,
                'time' => $log->created_at->format('H:i:s'),
            ]),
        ]);
    }
}
