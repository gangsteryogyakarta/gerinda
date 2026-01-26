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
        // 1. Summary stats (Cache for 5 minutes - reduced for near real-time)
        $stats = cache()->remember('dashboard_stats', 300, function() {
            return [
                'total_massa' => Massa::active()->count(),
                'total_events' => Event::count(),
                'active_events' => Event::whereIn('status', ['published', 'ongoing'])->count(),
                'total_registrations' => EventRegistration::confirmed()->count(),
                'total_checkins' => EventRegistration::checkedIn()->count(),
                'checkin_rate' => 0 // To be calculated
            ];
        });

        if ($stats['total_registrations'] > 0) {
            $stats['checkin_rate'] = round(($stats['total_checkins'] / $stats['total_registrations']) * 100);
        }

        // 2. Recent & Upcoming Events (Cache for 10 minutes)
        $recentEvents = cache()->remember('dashboard_recent_events', 600, function() {
            return Event::with(['category'])->latest()->limit(5)->get();
        });

        $upcomingEvents = cache()->remember('dashboard_upcoming_events', 600, function() {
            return Event::with(['category'])->upcoming()->limit(5)->get();
        });

        // 3. Top Attended Events
        $topEvents = cache()->remember('dashboard_top_events', 3600, function() {
            return Event::withCount(['registrations as checkin_count' => fn($q) => $q->checkedIn()])
                ->whereIn('status', ['completed', 'ongoing'])
                ->orderByDesc('checkin_count')
                ->limit(5)
                ->get();
        });

        // 4. Registration & Check-in Trend (Last 7 Months)
        $trends = cache()->remember('dashboard_trends_combined', 21600, function() {
            $months = collect(range(5, 0))->map(function($i) {
                return now()->subMonths($i)->format('Y-m');
            });

            $registrations = EventRegistration::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
                ->where('created_at', '>=', now()->subMonths(6)->startOfMonth())
                ->groupBy('month')
                ->pluck('count', 'month');

            $checkins = \App\Models\CheckinLog::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
                ->where('created_at', '>=', now()->subMonths(6)->startOfMonth())
                ->groupBy('month')
                ->pluck('count', 'month');
            
            return [
                'labels' => $months->map(fn($m) => \Carbon\Carbon::createFromFormat('Y-m', $m)->translatedFormat('F'))->toArray(),
                'registrations' => $months->map(fn($m) => $registrations[$m] ?? 0)->toArray(),
                'checkins' => $months->map(fn($m) => $checkins[$m] ?? 0)->toArray(),
            ];
        });

        // 5. Massa by Province (For Distribution Chart)
        $demographics = cache()->remember('dashboard_demographics', 86400, function() {
            $data = Massa::active()
                ->selectRaw('province_id, COUNT(*) as total')
                ->whereNotNull('province_id')
                ->groupBy('province_id')
                ->with('province:id,name')
                ->orderByDesc('total')
                ->limit(5)
                ->get();
                
            return [
                'labels' => $data->pluck('province.name')->toArray(),
                'data' => $data->pluck('total')->toArray(),
            ];
        });

        // 6. Recent Activities (Merged Log)
        $recentActivities = cache()->remember('dashboard_activities', 60, function() {
            $registrations = EventRegistration::with(['massa', 'event'])
                ->latest()
                ->limit(5)
                ->get()
                ->map(function($reg) {
                    return [
                        'type' => 'registration',
                        'name' => $reg->massa->nama_lengkap ?? 'Guest',
                        'action' => 'Mendaftar di event ' . ($reg->event->name ?? 'Unknown'),
                        'time' => $reg->created_at->diffForHumans(),
                        'timestamp' => $reg->created_at->timestamp,
                    ];
                });

            $checkins = \App\Models\CheckinLog::with(['registration.massa', 'event'])
                ->latest('created_at')
                ->limit(5)
                ->get()
                ->map(function($log) {
                    return [
                        'type' => 'checkin',
                        'name' => $log->registration->massa->nama_lengkap ?? 'Guest',
                        'action' => 'Check-in di ' . ($log->event->name ?? 'Unknown'),
                        'time' => $log->created_at ? $log->created_at->diffForHumans() : 'Baru saja',
                        'timestamp' => $log->created_at ? $log->created_at->timestamp : now()->timestamp,
                    ];
                });

            return $registrations->merge($checkins)
                ->sortByDesc('timestamp')
                ->take(6)
                ->values()
                ->toArray();
        });

        return view('dashboard', compact(
            'stats',
            'recentEvents',
            'upcomingEvents',
            'topEvents',
            'trends',
            'demographics',
            'recentActivities'
        ));
    }
}
