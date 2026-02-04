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
        // Get DIY ID
        $diyId = Province::where('name', 'LIKE', '%YOGYAKARTA%')->value('id');

        $stats = [
            'total_massa' => Massa::where('province_id', $diyId)->count(),
            'geocoded' => Massa::where('province_id', $diyId)->whereNotNull('latitude')->count(),
            'total_events' => Event::where('province_id', $diyId)->count(), // Assuming Event also has province_id or filtering logic
            'provinces_covered' => 1, // Hardcoded since we restrict to DIY
        ];

        // Top Districts (Kecamatan) - DIY Only
        $districtStats = Massa::select('district_id', DB::raw('count(*) as total'))
            ->where('province_id', $diyId)
            ->whereNotNull('district_id')
            ->groupBy('district_id')
            ->with('district:id,name')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // Top Villages (Kelurahan) - DIY Only
        $villageStats = Massa::select('village_id', DB::raw('count(*) as total'))
            ->where('province_id', $diyId)
            ->whereNotNull('village_id')
            ->groupBy('village_id')
            ->with('village:id,name')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        return view('maps.index', compact('stats', 'districtStats', 'villageStats'));
    }

    /**
     * Get massa markers for map (AJAX) - With Fallback Coordinates
     */
    public function markers(Request $request)
    {
        // Disable caching temporarily for debugging
        $provinceId = $request->input('province');
        $regencyId = $request->input('regency');
        
        // Get regency center coordinates for fallback
        $regencyCoords = DB::table('regencies')
            ->select('id', 'latitude', 'longitude')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->pluck('longitude', 'id')
            ->toArray();
        
        $regencyLats = DB::table('regencies')
            ->select('id', 'latitude')
            ->whereNotNull('latitude')
            ->pluck('latitude', 'id')
            ->toArray();
        
        // Build query - include ALL massa, not just those with coordinates
        $query = DB::table('massa')
            ->join('provinces', 'massa.province_id', '=', 'provinces.id')
            ->leftJoin('regencies', 'massa.regency_id', '=', 'regencies.id')
            ->leftJoin('districts', 'massa.district_id', '=', 'districts.id')
            ->leftJoin('villages', 'massa.village_id', '=', 'villages.id')
            ->whereNull('massa.deleted_at');

        // STRICTLY RESTRICT TO YOGYAKARTA
        $query->where('provinces.name', 'LIKE', '%YOGYAKARTA%');

        // Optional: Filter by regency within Yogyakarta
        if ($regencyId) {
            $query->where('massa.regency_id', $regencyId);
        }

        $massaData = $query->select(
                'massa.id', 
                'massa.nama_lengkap as name', 
                'massa.latitude as lat', 
                'massa.longitude as lng',
                'massa.regency_id',
                DB::raw("CONCAT(COALESCE(villages.name, ''), ', ', COALESCE(districts.name, ''), ', ', COALESCE(regencies.name, ''), ', ', provinces.name) as location"),
                'villages.latitude as village_lat',
                'villages.longitude as village_lng',
                'districts.latitude as district_lat',
                'districts.longitude as district_lng',
                'regencies.latitude as regency_lat',
                'regencies.longitude as regency_lng'
            )
            ->limit(5000)
            ->get();
        
        // Process markers with CASCADING FALLBACK coordinates
        $markers = $massaData->map(function($m) {
            $lat = $m->lat;
            $lng = $m->lng;
            
            // Fallback Logic: Village -> District -> Regency
            if (empty($lat) || empty($lng)) {
                if (!empty($m->village_lat) && !empty($m->village_lng)) {
                    // Fallback to Village (Most precise)
                    $lat = $m->village_lat + (rand(-20, 20) / 10000); // Small jitter ~20m
                    $lng = $m->village_lng + (rand(-20, 20) / 10000);
                } elseif (!empty($m->district_lat) && !empty($m->district_lng)) {
                    // Fallback to District
                    $lat = $m->district_lat + (rand(-50, 50) / 10000); // Medium jitter ~50m
                    $lng = $m->district_lng + (rand(-50, 50) / 10000);
                } elseif (!empty($m->regency_lat) && !empty($m->regency_lng)) {
                    // Fallback to Regency
                    $lat = $m->regency_lat + (rand(-100, 100) / 10000); // Large jitter ~100m
                    $lng = $m->regency_lng + (rand(-100, 100) / 10000);
                } else {
                    return null; // No coordinates available anywhere
                }
            }
            
            return [
                'id' => $m->id,
                'name' => $m->name,
                'lat' => (float) $lat,
                'lng' => (float) $lng,
                'location' => trim($m->location, ', '),
            ];
        })->filter()->values();
        
        return response()->json($markers);
    }

    /**
     * Get heatmap data (AJAX) - Optimized
     */
    public function heatmap(Request $request)
    {
        return cache()->remember('maps_heatmap_diy', 3600, function() {
            return DB::table('massa')
                ->join('provinces', 'massa.province_id', '=', 'provinces.id')
                ->where('provinces.name', 'LIKE', '%YOGYAKARTA%') // Restrict to DIY
                ->whereNotNull('massa.latitude')
                ->whereNotNull('massa.longitude')
                ->select('massa.latitude', 'massa.longitude')
                ->limit(10000)
                ->get()
                ->map(fn($m) => [$m->latitude, $m->longitude, 1]);
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
