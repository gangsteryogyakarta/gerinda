<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventRegistration;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    /**
     * Display a listing of tickets.
     */
    public function index(Request $request)
    {
        // Get all events for filter
        $events = Event::orderBy('event_start', 'desc')->get();
        
        // Build query
        // Build query
        $query = EventRegistration::with(['event', 'massa'])
            ->whereNotNull('ticket_number')
            ->whereHas('event')
            ->whereHas('massa');

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhereHas('massa', function($q) use ($search) {
                      $q->where('nama_lengkap', 'like', "%{$search}%")
                        ->orWhere('nik', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by Event
        if ($request->has('event_id') && $request->event_id) {
            $query->where('event_id', $request->event_id);
        }

        // Filter by Status
        if ($request->has('status') && $request->status) {
            if ($request->status === 'checked_in') {
                $query->where('attendance_status', 'checked_in');
            } else {
                $query->where('registration_status', $request->status);
            }
        }

        $tickets = $query->latest()->paginate(15)->withQueryString();

        return view('tickets.index', compact('tickets', 'events'));
    }
}
