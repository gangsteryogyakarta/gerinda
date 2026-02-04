<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\District;
use App\Models\Province;
use App\Models\Regency;
use App\Models\Village;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function provinces(Request $request)
    {
        $search = $request->input('search');
        
        $query = Province::query()->orderBy('name');
        
        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }
        
        return response()->json($query->get(['id', 'code', 'name']));
    }

    public function regencies(Request $request, $provinceId)
    {
        $search = $request->input('search');
        
        $query = Regency::where('province_id', $provinceId)->orderBy('name');
        
        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }
        
        return response()->json($query->get(['id', 'province_id', 'code', 'name']));
    }

    public function districts(Request $request, $regencyId)
    {
        $search = $request->input('search');
        
        $query = District::where('regency_id', $regencyId)->orderBy('name');
        
        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }
        
        return response()->json($query->get(['id', 'regency_id', 'code', 'name']));
    }

    public function villages(Request $request, $districtId)
    {
        $search = $request->input('search');
        
        $query = Village::where('district_id', $districtId)->orderBy('name');
        
        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }
        
        return response()->json($query->get(['id', 'district_id', 'code', 'name', 'postal_code']));
    }
    
    public function getPostalCode($villageId)
    {
        $village = Village::find($villageId);
        
        if (!$village) {
            return response()->json(['postal_code' => null], 404);
        }
        
        return response()->json(['postal_code' => $village->postal_code]);
    }
}
