<?php

namespace App\Http\Controllers;

use App\Models\Massa;
use App\Models\Province;
use App\Models\Regency;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MapController extends Controller
{
    /**
     * Display the WebGIS dashboard
     */
    public function index()
    {
        $stats = [
            'total_massa' => Massa::count(),
            'geocoded' => Massa::whereNotNull('latitude')->count(),
            'total_events' => Event::count(),
            'provinces_covered' => Massa::distinct('province_id')->count('province_id'),
        ];

        $provinceStats = Massa::select('province_id', DB::raw('count(*) as total'))
            ->whereNotNull('province_id')
            ->groupBy('province_id')
            ->with('province')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        return view('maps.index', compact('stats', 'provinceStats'));
    }

    /**
     * Get massa markers for map (AJAX)
     */
    /**
     * Get massa markers for map (AJAX) - Optimized
     */
    public function markers(Request $request)
    {
        // Cache key based on filters
        $cacheKey = 'maps_markers_' . md5(json_encode($request->all()));
        
        return cache()->remember($cacheKey, 3600, function() use ($request) {
            $query = DB::table('massa')
                ->join('provinces', 'massa.province_id', '=', 'provinces.id')
                ->leftJoin('regencies', 'massa.regency_id', '=', 'regencies.id')
                ->whereNotNull('massa.latitude')
                ->whereNotNull('massa.longitude');
    
            // Filter by province
            if ($provinceId = $request->input('province')) {
                $query->where('massa.province_id', $provinceId);
            }
    
            // Filter by regency
            if ($regencyId = $request->input('regency')) {
                $query->where('massa.regency_id', $regencyId);
            }
    
            return $query->select(
                    'massa.id', 
                    'massa.nama_lengkap as name', 
                    'massa.latitude as lat', 
                    'massa.longitude as lng',
                    DB::raw("CONCAT(COALESCE(regencies.name, ''), ', ', provinces.name) as location")
                )
                ->limit(5000)
                ->get();
        });
    }

    /**
     * Get heatmap data (AJAX) - Optimized
     */
    public function heatmap(Request $request)
    {
        return cache()->remember('maps_heatmap', 3600, function() {
            return DB::table('massa')
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->select('latitude', 'longitude')
                ->limit(10000)
                ->get()
                ->map(fn($m) => [$m->latitude, $m->longitude, 1]);
        });
    }

    /**
     * Get province statistics for choropleth - Optimized
     */
    public function provinceStats()
    {
        return cache()->remember('maps_province_stats', 3600, function() {
            return DB::table('massa')
                ->join('provinces', 'massa.province_id', '=', 'provinces.id')
                ->select('massa.province_id', 'provinces.name', DB::raw('count(*) as total'))
                ->groupBy('massa.province_id', 'provinces.name')
                ->orderByDesc('total')
                ->get();
        });
    }

    /**
     * Get event locations
     */
    public function eventMarkers()
    {
        $events = Event::whereNotNull('venue_latitude')
            ->whereNotNull('venue_longitude')
            ->select('id', 'name', 'venue_name', 'venue_latitude', 'venue_longitude', 'event_start', 'status')
            ->withCount('registrations')
            ->get()
            ->map(fn($e) => [
                'id' => $e->id,
                'lat' => $e->venue_latitude,
                'lng' => $e->venue_longitude,
                'name' => $e->name,
                'venue' => $e->venue_name,
                'date' => $e->event_start->format('d M Y'),
                'status' => $e->status,
                'registrations' => $e->registrations_count,
            ]);

        return response()->json($events);
    }
}
