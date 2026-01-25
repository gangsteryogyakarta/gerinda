<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Province;
use App\Services\RegistrationService;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function __construct(
        protected RegistrationService $registrationService
    ) {}

    /**
     * Display a listing of events.
     */
    public function index(Request $request)
    {
        $query = Event::with(['category', 'province', 'regency'])
            ->withCount(['registrations as total_registrations'])
            ->withCount(['registrations as confirmed_count' => fn($q) => $q->confirmed()])
            ->withCount(['registrations as checkedin_count' => fn($q) => $q->checkedIn()]);

        // Filters
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($categoryId = $request->input('category')) {
            $query->where('event_category_id', $categoryId);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('venue_name', 'like', "%{$search}%");
            });
        }

        $events = $query->orderByDesc('created_at')->paginate(12);
        
        // Cache categories for 1 hour (rarely changes)
        $categories = cache()->remember('event_categories_active', 3600, function () {
            return EventCategory::active()->ordered()->get(['id', 'name', 'icon']);
        });

        return view('events.index', compact('events', 'categories'));
    }

    /**
     * Show the form for creating a new event.
     */
    public function create()
    {
        // Cache categories for 1 hour
        $categories = cache()->remember('event_categories_active', 3600, function () {
            return EventCategory::active()->ordered()->get(['id', 'name', 'icon']);
        });
        
        // Cache provinces for 24 hours (static data)
        $provinces = cache()->remember('provinces_list', 86400, function () {
            return Province::select(['id', 'name'])->orderBy('name')->get();
        });

        return view('events.create', compact('categories', 'provinces'));
    }

    /**
     * Store a newly created event.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'event_category_id' => 'nullable|exists:event_categories,id',
            'event_category_id' => 'nullable|exists:event_categories,id',
            'description' => 'nullable|string',
            'copywriting' => 'nullable|string',
            'banner_image' => 'nullable|image|max:2048',
            'venue_name' => 'required|string|max:255',
            'venue_address' => 'required|string',
            'province_id' => 'nullable|exists:provinces,id',
            'regency_id' => 'nullable|exists:regencies,id',
            'district_id' => 'nullable|exists:districts,id',
            'village_id' => 'nullable|exists:villages,id',
            'postal_code' => 'nullable|string|max:10',
            'event_start' => 'required|date',
            'event_end' => 'required|date|after_or_equal:event_start',
            'registration_start' => 'nullable|date',
            'registration_end' => 'nullable|date',
            'max_participants' => 'nullable|integer|min:1',
            'enable_waitlist' => 'boolean',
            'require_ticket' => 'boolean',
            'enable_checkin' => 'boolean',
            'send_wa_notification' => 'boolean',
        ]);

        if ($request->hasFile('banner_image')) {
            $path = $request->file('banner_image')->store('events/banners', 'public');
            $validated['banner_image'] = $path;
        }

        $validated['created_by'] = auth()->id();
        $validated['status'] = 'draft';

        $event = Event::create($validated);

        return redirect()->route('events.show', $event)
            ->with('success', 'Event berhasil dibuat!');
    }

    /**
     * Display the specified event.
     */
    public function show(Event $event)
    {
        $event->load([
            'category',
            'province',
            'regency',
            'district',
            'customFields' => fn($q) => $q->active()->ordered(),
        ]);

        $event->loadCount([
            'registrations as total_registrations',
            'registrations as confirmed_count' => fn($q) => $q->confirmed(),
            'registrations as checkedin_count' => fn($q) => $q->checkedIn(),
            'registrations as pending_count' => fn($q) => $q->where('registration_status', 'pending'),
            'registrations as waitlist_count' => fn($q) => $q->where('registration_status', 'waitlist'),
        ]);

        // Recent registrations
        $recentRegistrations = $event->registrations()
            ->with('massa:id,nama_lengkap,no_hp')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('events.show', compact('event', 'recentRegistrations'));
    }

    /**
     * Show the form for editing the specified event.
     */
    public function edit(Event $event)
    {
        $categories = EventCategory::active()->ordered()->get();
        $provinces = Province::orderBy('name')->get();

        return view('events.edit', compact('event', 'categories', 'provinces'));
    }

    /**
     * Update the specified event.
     */
    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'event_category_id' => 'nullable|exists:event_categories,id',
            'event_category_id' => 'nullable|exists:event_categories,id',
            'description' => 'nullable|string',
            'copywriting' => 'nullable|string',
            'banner_image' => 'nullable|image|max:2048',
            'venue_name' => 'sometimes|string|max:255',
            'venue_address' => 'sometimes|string',
            'province_id' => 'nullable|exists:provinces,id',
            'regency_id' => 'nullable|exists:regencies,id',
            'district_id' => 'nullable|exists:districts,id',
            'village_id' => 'nullable|exists:villages,id',
            'postal_code' => 'nullable|string|max:10',
            'event_start' => 'sometimes|date',
            'event_end' => 'sometimes|date',
            'registration_start' => 'nullable|date',
            'registration_end' => 'nullable|date',
            'max_participants' => 'nullable|integer|min:1',
            'enable_waitlist' => 'boolean',
            'require_ticket' => 'boolean',
            'enable_checkin' => 'boolean',
            'send_wa_notification' => 'boolean',
            'status' => 'sometimes|in:draft,published,ongoing,completed,cancelled',
        ]);

        if ($request->hasFile('banner_image')) {
            $path = $request->file('banner_image')->store('events/banners', 'public');
            $validated['banner_image'] = $path;
        }

        $event->update($validated);

        return redirect()->route('events.show', $event)
            ->with('success', 'Event berhasil diperbarui!');
    }

    /**
     * Remove the specified event.
     */
    public function destroy(Event $event)
    {
        if ($event->registrations()->exists()) {
            return back()->with('error', 'Event tidak dapat dihapus karena sudah memiliki peserta.');
        }

        $event->delete();

        return redirect()->route('events.index')
            ->with('success', 'Event berhasil dihapus!');
    }

    /**
     * Update event status.
     */
    public function updateStatus(Request $request, Event $event)
    {
        $validated = $request->validate([
            'status' => 'required|in:draft,published,ongoing,completed,cancelled',
        ]);

        $event->update($validated);

        return back()->with('success', 'Status event berhasil diperbarui!');
    }

    /**
     * Show registrations for an event.
     */
    public function registrations(Request $request, Event $event)
    {
        $query = $event->registrations()->with('massa');

        if ($status = $request->input('status')) {
            $query->where('registration_status', $status);
        }

        if ($attendance = $request->input('attendance')) {
            $query->where('attendance_status', $attendance);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                    ->orWhereHas('massa', fn($mq) => 
                        $mq->where('nama_lengkap', 'like', "%{$search}%")
                            ->orWhere('nik', 'like', "%{$search}%")
                    );
            });
        }

        $registrations = $query->orderByDesc('created_at')->paginate(20);

        return view('events.registrations', compact('event', 'registrations'));
    }

    /**
     * Batch generate tickets for an event.
     */
    public function batchGenerateTickets(Event $event)
    {
        $results = $this->registrationService->batchGenerateTickets($event);

        return back()->with('success', "Berhasil generate {$results['success']} tiket, {$results['failed']} gagal.");
    }
    /**
     * Print all tickets for an event.
     */
    public function printAllTickets(Event $event)
    {
        // Get all confirmed registrations with ticket numbers
        $registrations = $event->registrations()
            ->confirmed()
            ->whereNotNull('ticket_number')
            ->orderBy('id')
            ->with('massa')
            ->get();

        if ($registrations->isEmpty()) {
            return back()->with('error', 'Belum ada tiket yang terkonfirmasi untuk event ini.');
        }

        // Prepare QR Codes and Logo
        $qrCodes = [];
        $logoPath = public_path('img/logo-gerindra.png');
        $logoBase64 = '';
        
        if (file_exists($logoPath)) {
            $logoData = file_get_contents($logoPath);
            $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
        }

        foreach ($registrations as $reg) {
            if ($reg->qr_code_path && Storage::disk('public')->exists($reg->qr_code_path)) {
                $qrContent = Storage::disk('public')->get($reg->qr_code_path);
                $qrCodes[$reg->id] = 'data:image/svg+xml;base64,' . base64_encode($qrContent);
            }
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.tickets_batch', [
            'event' => $event,
            'registrations' => $registrations,
            'qrCodes' => $qrCodes,
            'logoBase64' => $logoBase64,
        ]);
        
        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream("tickets-event-{$event->code}.pdf");
    }
}
