<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\EventCustomField;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class EventController extends Controller
{
    /**
     * List all events with filters
     */
    public function index(Request $request): JsonResponse
    {
        $query = Event::with(['category', 'province', 'regency'])
            ->withCount(['registrations as total_registrations'])
            ->withCount(['registrations as confirmed_registrations' => fn($q) => $q->confirmed()])
            ->withCount(['registrations as checkedin_registrations' => fn($q) => $q->checkedIn()]);

        // Status filter
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Category filter
        if ($categoryId = $request->input('category_id')) {
            $query->where('event_category_id', $categoryId);
        }

        // Date range filter
        if ($from = $request->input('from_date')) {
            $query->where('event_start', '>=', $from);
        }
        if ($to = $request->input('to_date')) {
            $query->where('event_start', '<=', $to);
        }

        // Location filter
        if ($provinceId = $request->input('province_id')) {
            $query->where('province_id', $provinceId);
        }

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('venue_name', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'event_start');
        $sortDir = $request->input('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        $events = $query->paginate($request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $events,
        ]);
    }

    /**
     * Get single event details
     */
    public function show(Event $event): JsonResponse
    {
        $event->load([
            'category',
            'province',
            'regency',
            'district',
            'customFields' => fn($q) => $q->active()->ordered(),
            'lotteryPrizes' => fn($q) => $q->active()->ordered(),
            'creator:id,name',
        ]);

        $event->loadCount([
            'registrations as total_registrations',
            'registrations as confirmed_registrations' => fn($q) => $q->confirmed(),
            'registrations as checkedin_registrations' => fn($q) => $q->checkedIn(),
        ]);

        return response()->json([
            'success' => true,
            'data' => $event,
            'meta' => [
                'is_full' => $event->is_full,
                'available_slots' => $event->available_slots,
                'registration_open' => $event->registration_open,
                'is_ongoing' => $event->is_ongoing,
            ],
        ]);
    }

    /**
     * Create new event
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'event_category_id' => 'nullable|exists:event_categories,id',
            'description' => 'nullable|string',
            'venue_name' => 'required|string|max:255',
            'venue_address' => 'required|string',
            'province_id' => 'nullable|exists:provinces,id',
            'regency_id' => 'nullable|exists:regencies,id',
            'district_id' => 'nullable|exists:districts,id',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'registration_start' => 'nullable|date',
            'registration_end' => 'nullable|date|after_or_equal:registration_start',
            'event_start' => 'required|date',
            'event_end' => 'required|date|after_or_equal:event_start',
            'max_participants' => 'nullable|integer|min:1',
            'enable_waitlist' => 'boolean',
            'require_ticket' => 'boolean',
            'enable_checkin' => 'boolean',
            'enable_lottery' => 'boolean',
            'send_wa_notification' => 'boolean',
            'custom_fields' => 'nullable|array',
            'custom_fields.*.field_name' => 'required_with:custom_fields|string|max:50',
            'custom_fields.*.field_label' => 'required_with:custom_fields|string|max:100',
            'custom_fields.*.field_type' => 'required_with:custom_fields|in:text,textarea,number,select,checkbox,radio,date,file',
            'custom_fields.*.field_options' => 'nullable|array',
            'custom_fields.*.is_required' => 'boolean',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['slug'] = Str::slug($validated['name']) . '-' . Str::random(6);

        $event = Event::create($validated);

        // Create custom fields
        if (!empty($validated['custom_fields'])) {
            foreach ($validated['custom_fields'] as $index => $field) {
                EventCustomField::create([
                    'event_id' => $event->id,
                    'field_name' => $field['field_name'],
                    'field_label' => $field['field_label'],
                    'field_type' => $field['field_type'],
                    'field_options' => $field['field_options'] ?? null,
                    'is_required' => $field['is_required'] ?? false,
                    'sort_order' => $index,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Event berhasil dibuat.',
            'data' => $event->fresh(['customFields']),
        ], 201);
    }

    /**
     * Update event
     */
    public function update(Request $request, Event $event): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'event_category_id' => 'nullable|exists:event_categories,id',
            'description' => 'nullable|string',
            'banner_image' => 'nullable|string',
            'venue_name' => 'sometimes|string|max:255',
            'venue_address' => 'sometimes|string',
            'province_id' => 'nullable|exists:provinces,id',
            'regency_id' => 'nullable|exists:regencies,id',
            'district_id' => 'nullable|exists:districts,id',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'registration_start' => 'nullable|date',
            'registration_end' => 'nullable|date',
            'event_start' => 'sometimes|date',
            'event_end' => 'sometimes|date',
            'max_participants' => 'nullable|integer|min:1',
            'enable_waitlist' => 'boolean',
            'require_ticket' => 'boolean',
            'enable_checkin' => 'boolean',
            'enable_lottery' => 'boolean',
            'send_wa_notification' => 'boolean',
            'status' => 'sometimes|in:draft,published,ongoing,completed,cancelled',
        ]);

        $event->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Event berhasil diupdate.',
            'data' => $event->fresh(),
        ]);
    }

    /**
     * Delete event (soft delete)
     */
    public function destroy(Event $event): JsonResponse
    {
        // Check if event has registrations
        if ($event->registrations()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Event tidak dapat dihapus karena sudah memiliki peserta.',
            ], 422);
        }

        $event->delete();

        return response()->json([
            'success' => true,
            'message' => 'Event berhasil dihapus.',
        ]);
    }

    /**
     * Update event status
     */
    public function updateStatus(Request $request, Event $event): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:draft,published,ongoing,completed,cancelled',
        ]);

        $event->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Status event berhasil diupdate.',
            'data' => $event,
        ]);
    }

    /**
     * Get event categories
     */
    public function categories(): JsonResponse
    {
        $categories = EventCategory::active()->ordered()->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    /**
     * Get event statistics summary
     */
    public function statistics(Event $event): JsonResponse
    {
        $registrations = $event->registrations();

        $stats = [
            'registration' => [
                'total' => $registrations->count(),
                'confirmed' => $registrations->confirmed()->count(),
                'pending' => $registrations->where('registration_status', 'pending')->count(),
                'waitlist' => $registrations->where('registration_status', 'waitlist')->count(),
                'cancelled' => $registrations->where('registration_status', 'cancelled')->count(),
            ],
            'attendance' => [
                'checked_in' => $registrations->checkedIn()->count(),
                'not_arrived' => $registrations->notArrived()->count(),
                'attendance_rate' => $this->calculateAttendanceRate($event),
            ],
            'quota' => [
                'max_participants' => $event->max_participants,
                'current_participants' => $event->current_participants,
                'available_slots' => $event->available_slots,
                'is_full' => $event->is_full,
            ],
            'lottery' => [
                'total_prizes' => $event->lotteryPrizes()->sum('quantity'),
                'prizes_given' => $event->lotteryDraws()->count(),
                'prizes_remaining' => $event->lotteryPrizes()->sum('remaining_quantity'),
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    protected function calculateAttendanceRate(Event $event): float
    {
        $confirmed = $event->registrations()->confirmed()->count();
        if ($confirmed === 0) return 0;
        
        $checkedIn = $event->registrations()->checkedIn()->count();
        return round(($checkedIn / $confirmed) * 100, 2);
    }
}
