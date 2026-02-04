<?php

namespace Database\Seeders;

use App\Models\District;
use App\Models\Village;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DiyRegionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Coordinates for DIY Districts (Kecamatan)
        $districts = [
            // SLEMAN
            ['name' => 'DEPOK', 'reg_name' => 'SLEMAN', 'lat' => -7.7607, 'lng' => 110.3956],
            ['name' => 'MLATI', 'reg_name' => 'SLEMAN', 'lat' => -7.7340, 'lng' => 110.3465],
            ['name' => 'GAMPING', 'reg_name' => 'SLEMAN', 'lat' => -7.7959, 'lng' => 110.3279],
            ['name' => 'GODEAN', 'reg_name' => 'SLEMAN', 'lat' => -7.7681, 'lng' => 110.2929],
            ['name' => 'NGAGLIK', 'reg_name' => 'SLEMAN', 'lat' => -7.7121, 'lng' => 110.3949],
            
            // BANTUL
            ['name' => 'KASIHAN', 'reg_name' => 'BANTUL', 'lat' => -7.8282, 'lng' => 110.3347],
            ['name' => 'BANGUNTAPAN', 'reg_name' => 'BANTUL', 'lat' => -7.8340, 'lng' => 110.4138],
            ['name' => 'SEWON', 'reg_name' => 'BANTUL', 'lat' => -7.8540, 'lng' => 110.3598],
            
            // KOTA YOGYAKARTA
            ['name' => 'GONDOKUSUMAN', 'reg_name' => 'YOGYAKARTA', 'lat' => -7.7842, 'lng' => 110.3804],
            ['name' => 'UMBULHARJO', 'reg_name' => 'YOGYAKARTA', 'lat' => -7.8189, 'lng' => 110.3908],
            ['name' => 'TEGALREJO', 'reg_name' => 'YOGYAKARTA', 'lat' => -7.7844, 'lng' => 110.3541],
        ];

        foreach ($districts as $d) {
            $district = District::where('name', $d['name'])
                ->whereHas('regency', function($q) use ($d) {
                    $q->where('name', 'LIKE', "%{$d['reg_name']}%");
                })->first();
            
            if ($district) {
                $district->update(['latitude' => $d['lat'], 'longitude' => $d['lng']]);
            }
        }

        // Coordinates for DIY Villages (Desa/Kel.)
        $villages = [
            // SLEMAN - DEPOK
            ['name' => 'CATURTUNGGAL', 'dist_name' => 'DEPOK', 'lat' => -7.7719, 'lng' => 110.3908],
            ['name' => 'CONDONGCATUR', 'dist_name' => 'DEPOK', 'lat' => -7.7554, 'lng' => 110.4024],
            ['name' => 'MAGUWOHARJO', 'dist_name' => 'DEPOK', 'lat' => -7.7688, 'lng' => 110.4357],
            
            // SLEMAN - MLATI
            ['name' => 'SINDUADI', 'dist_name' => 'MLATI', 'lat' => -7.7479, 'lng' => 110.3644],
            ['name' => 'SENDANGADI', 'dist_name' => 'MLATI', 'lat' => -7.7371, 'lng' => 110.3548],
            
            // BANTUL - KASIHAN
            ['name' => 'TAMANTIRTO', 'dist_name' => 'KASIHAN', 'lat' => -7.8214, 'lng' => 110.3242],
            ['name' => 'NGESTIHARJO', 'dist_name' => 'KASIHAN', 'lat' => -7.8039, 'lng' => 110.3403],
            
            // KOTA - GONDOKUSUMAN
            ['name' => 'KLITREN', 'dist_name' => 'GONDOKUSUMAN', 'lat' => -7.7885, 'lng' => 110.3807],
            ['name' => 'DEMANGAN', 'dist_name' => 'GONDOKUSUMAN', 'lat' => -7.7797, 'lng' => 110.3934],
        ];

        foreach ($villages as $v) {
            $village = Village::where('name', $v['name'])
                ->whereHas('district', function($q) use ($v) {
                    $q->where('name', $v['dist_name']);
                })->first();
            
            if ($village) {
                $village->update(['latitude' => $v['lat'], 'longitude' => $v['lng']]);
            }
        }
    }
}
