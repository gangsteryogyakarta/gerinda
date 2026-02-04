<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Province;
use App\Services\RegistrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
            ->withCount(['registrations as total_registrations' => fn($q) => $q->whereHas('massa')])
            ->withCount(['registrations as confirmed_count' => fn($q) => $q->confirmed()->whereHas('massa')])
            ->withCount(['registrations as checkedin_count' => fn($q) => $q->checkedIn()->whereHas('massa')]);

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
            'registrations as total_registrations' => fn($q) => $q->whereHas('massa'),
            'registrations as confirmed_count' => fn($q) => $q->confirmed()->whereHas('massa'),
            'registrations as checkedin_count' => fn($q) => $q->checkedIn()->whereHas('massa'),
            'registrations as pending_count' => fn($q) => $q->where('registration_status', 'pending')->whereHas('massa'),
            'registrations as waitlist_count' => fn($q) => $q->where('registration_status', 'waitlist')->whereHas('massa'),
        ]);

        // Recent registrations
        $recentRegistrations = $event->registrations()
            ->whereHas('massa')
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
        // if ($event->registrations()->exists()) {
        //     return back()->with('error', 'Event tidak dapat dihapus karena sudah memiliki peserta.');
        // }

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
        $query = $event->registrations()->whereHas('massa')->with('massa');

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

        // Calculate stats ensuring we only count active massa
        $stats = [
            'confirmed' => $event->registrations()->whereHas('massa')->confirmed()->count(),
            'checked_in' => $event->registrations()->whereHas('massa')->checkedIn()->count(),
            'waitlist' => $event->registrations()->whereHas('massa')->where('registration_status', 'waitlist')->count(),
        ];

        return view('events.registrations', compact('event', 'registrations', 'stats'));
    }

    /**
     * Batch generate tickets for an event.
     */
    /**
     * Batch generate tickets for an event.
     */
    public function batchGenerateTickets(Event $event)
    {
        // Dispatch the job effectively moving processing to background
        \App\Jobs\GenerateBatchTicketsJob::dispatch($event, true);
        
        return back()->with('success', 'Proses generate tiket sedang berjalan di background. Silakan cek kembali beberapa saat lagi.');
    }
    
    /**
     * Print all tickets for an event.
     */
    /**
     * Trigger background job for batch ticket generation.
     */
    public function generateBatchTickets(Request $request, Event $event)
    {
        $perPage = 50; // Stable chunk size for background processing
        
        $query = $event->registrations()
            ->whereHas('massa')
            ->whereIn('registration_status', ['confirmed', 'pending', 'waitlist'])
            ->whereNotNull('ticket_number');

        $total = $query->count();
        $batches = ceil($total / $perPage);

        for ($i = 0; $i < $batches; $i++) {
            $offset = $i * $perPage;
            $start = $offset + 1;
            $end = min(($i + 1) * $perPage, $total);
            $batchNo = $i + 1;

            // Check if job already exists for this batch to prevent duplicates
            $exists = \App\Models\PrintJob::where('event_id', $event->id)
                ->where('batch_no', $batchNo)
                ->where('status', '!=', 'failed')
                ->exists();

            if (!$exists) {
                // Create PrintJob record
                $printJob = \App\Models\PrintJob::create([
                    'user_id' => auth()->id(),
                    'event_id' => $event->id,
                    'batch_no' => $batchNo,
                    'ticket_range' => "{$start}-{$end}",
                    'status' => 'pending'
                ]);

                // Dispatch Job
                \App\Jobs\GenerateTicketPdfJob::dispatch($event, $printJob, $offset, $perPage);
            }
        }

        return redirect()->route('events.print-history', $event)
            ->with('success', "Proses cetak {$batches} batch telah antri di background.");
    }

    /**
     * Show print job history implementation.
     */
    public function printHistory(Event $event)
    {
        $jobs = \App\Models\PrintJob::where('event_id', $event->id)
            ->orderBy('batch_no')
            ->get();
            
        return view('events.print_history', compact('event', 'jobs'));
    }

    /**
     * Print all tickets for an event (Legacy/Direct Download).
     */
    public function printAllTickets(Request $request, Event $event)
    {
        // Redirect to print history/generate page if many tickets
        $total = $event->registrations()
            ->whereHas('massa')
            ->whereIn('registration_status', ['confirmed', 'pending', 'waitlist'])
            ->whereNotNull('ticket_number')
            ->count();

        if ($total > 50) {
            return redirect()->route('events.print-history', $event);
        }

        // ... Keep existing small-batch logic for < 50 tickets if needed, or just redirect always ...
        return redirect()->route('events.print-history', $event);
    }
}

