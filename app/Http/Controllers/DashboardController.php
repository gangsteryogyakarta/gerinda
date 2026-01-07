<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Massa;
use App\Models\EventRegistration;
use App\Models\EventCategory;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Summary stats (Cache for 60 minutes)
        $stats = cache()->remember('dashboard_stats', 3600, function() {
            return [
                'total_massa' => Massa::active()->count(),
                'total_events' => Event::count(),
                'active_events' => Event::whereIn('status', ['published', 'ongoing'])->count(),
                'total_registrations' => EventRegistration::confirmed()->count(),
                'total_checkins' => EventRegistration::checkedIn()->count(),
            ];
        });

        // 2. Recent events (Cache for 10 minutes)
        $recentEvents = cache()->remember('dashboard_recent_events', 600, function() {
            return Event::with(['category'])
                ->latest()
                ->limit(5)
                ->get();
        });

        // 3. Upcoming events (Cache for 10 minutes)
        $upcomingEvents = cache()->remember('dashboard_upcoming_events', 600, function() {
            return Event::with(['category'])
                ->upcoming()
                ->limit(5)
                ->get();
        });

        // 4. Top attended events (Cache for 60 minutes)
        $topEvents = cache()->remember('dashboard_top_events', 3600, function() {
            return Event::withCount(['registrations as checkin_count' => fn($q) => $q->checkedIn()])
                ->whereIn('status', ['completed', 'ongoing'])
                ->orderByDesc('checkin_count')
                ->limit(5)
                ->get();
        });

        // 5. Registration trend (Cache for 6 hours)
        $registrationTrend = cache()->remember('dashboard_trend', 21600, function() {
            return EventRegistration::selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->where('created_at', '>=', now()->subDays(7))
                ->groupBy('date')
                ->orderBy('date')
                ->pluck('count', 'date')
                ->toArray();
        });

        // 6. Massa by province (Cache for 24 hours)
        $massaByProvince = cache()->remember('dashboard_massa_province', 86400, function() {
            return Massa::active()
                ->selectRaw('province_id, COUNT(*) as total')
                ->groupBy('province_id')
                ->with('province:id,name')
                ->orderByDesc('total')
                ->limit(10)
                ->get();
        });

        return view('dashboard', compact(
            'stats',
            'recentEvents',
            'upcomingEvents',
            'topEvents',
            'registrationTrend',
            'massaByProvince'
        ));
    }
}
