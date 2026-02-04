<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Massa;
use App\Models\EventRegistration;
use App\Models\CheckinLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Display the reports dashboard
     */
    public function index()
    {
        // Monthly registration stats
        $monthlyStats = EventRegistration::select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as registrations'),
                DB::raw('SUM(CASE WHEN attendance_status = "checked_in" THEN 1 ELSE 0 END) as checkins')
            )
            ->whereYear('created_at', now()->year)
            ->whereHas('massa')
            ->whereHas('event')
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->orderBy('month')
            ->get();

        // Event performance (Include Published)
        $eventPerformance = Event::withCount([
            'registrations' => fn($q) => $q->whereHas('massa'),
            'registrations as confirmed_count' => fn($q) => $q->where('registration_status', 'confirmed')->whereHas('massa'),
            'registrations as checkedin_count' => fn($q) => $q->where('attendance_status', 'checked_in')->whereHas('massa'),
        ])
            ->whereIn('status', ['published', 'ongoing', 'completed'])
            ->orderByRaw("FIELD(status, 'ongoing', 'published', 'completed')")
            ->orderByDesc('event_start')
            ->limit(10)
            ->get();

        // Top Districts (Kecamatan)
        $districtStats = Massa::select('district_id', DB::raw('count(*) as total'))
            ->whereNotNull('district_id')
            ->groupBy('district_id')
            ->with('district:id,name')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // Top Villages (Kelurahan)
        $villageStats = Massa::select('village_id', DB::raw('count(*) as total'))
            ->whereNotNull('village_id')
            ->groupBy('village_id')
            ->with('village:id,name')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // Daily checkins for current week
        $weeklyCheckins = CheckinLog::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total')
            )
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->whereHas('massa')
            ->whereHas('event')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        // Summary stats
        $stats = [
            'total_events' => Event::count(),
            'total_massa' => Massa::count(),
            'total_registrations' => EventRegistration::whereHas('massa')->whereHas('event')->count(),
            'total_checkins' => CheckinLog::whereHas('massa')->whereHas('event')->count(),
            'avg_attendance_rate' => $this->calculateAverageAttendanceRate(),
            'conversion_rate' => $this->calculateConversionRate(),
        ];

        return view('reports.index', compact(
            'monthlyStats',
            'eventPerformance',
            'districtStats',
            'villageStats',
            'weeklyCheckins',
            'stats'
        ));
    }

    /**
     * Export registrations to CSV
     */
    public function exportRegistrations(Request $request)
    {
        $eventId = $request->input('event_id');
        
        $query = EventRegistration::with(['massa', 'event'])
            ->whereHas('massa')
            ->whereHas('event');
        
        if ($eventId) {
            $query->where('event_id', $eventId);
        }

        $registrations = $query->get();

        $filename = 'registrations-' . now()->format('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($registrations) {
            $file = fopen('php://output', 'w');
            
            // Header row
            fputcsv($file, [
                'No Tiket',
                'Nama',
                'NIK',
                'No HP',
                'Event',
                'Status',
                'Kehadiran',
                'Waktu Registrasi',
                'Waktu Check-in',
            ]);

            // Data rows
            foreach ($registrations as $reg) {
                fputcsv($file, [
                    $reg->ticket_number,
                    $reg->massa->nama_lengkap,
                    $reg->massa->nik,
                    $reg->massa->no_hp,
                    $reg->event->name,
                    $reg->registration_status,
                    $reg->attendance_status,
                    $reg->created_at->format('Y-m-d H:i'),
                    $reg->checked_in_at?->format('Y-m-d H:i'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function calculateAverageAttendanceRate(): float
    {
        $events = Event::withCount([
            'registrations as confirmed' => fn($q) => $q->confirmed()->whereHas('massa'),
            'registrations as checkedin' => fn($q) => $q->checkedIn()->whereHas('massa'),
        ])->whereIn('status', ['ongoing', 'completed'])->get();

        if ($events->isEmpty()) return 0;

        $totalRate = 0;
        $count = 0;

        foreach ($events as $event) {
            if ($event->confirmed > 0) {
                $totalRate += ($event->checkedin / $event->confirmed) * 100;
                $count++;
            }
        }

        return $count > 0 ? round($totalRate / $count, 1) : 0;
    }

    private function calculateConversionRate(): float
    {
        $totalMassa = Massa::count();
        $massaWithRegistration = Massa::has('registrations')->count();

        if ($totalMassa === 0) return 0;

        return round(($massaWithRegistration / $totalMassa) * 100, 1);
    }
}
