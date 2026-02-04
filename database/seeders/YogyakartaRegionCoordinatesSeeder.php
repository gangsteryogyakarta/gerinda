<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class YogyakartaRegionCoordinatesSeeder extends Seeder
{
    /**
     * Seed coordinates for all Yogyakarta regencies, districts, and key villages.
     */
    public function run(): void
    {
        // ================================================
        // REGENCIES (Kabupaten/Kota) - DI Yogyakarta
        // ================================================
        $regencies = [
            // Code => [name, lat, lng]
            '3401' => ['KULON PROGO', -7.8211, 110.1528],
            '3402' => ['BANTUL', -7.8893, 110.3278],
            '3403' => ['GUNUNGKIDUL', -7.9631, 110.6136],
            '3404' => ['SLEMAN', -7.7167, 110.3556],
            '3471' => ['YOGYAKARTA', -7.7956, 110.3695],
        ];
        
        foreach ($regencies as $code => $data) {
            DB::table('regencies')
                ->where('code', $code)
                ->update([
                    'latitude' => $data[1],
                    'longitude' => $data[2],
                ]);
        }
        $this->command->info('✓ Updated coordinates for 5 Yogyakarta regencies');
        
        // ================================================
        // DISTRICTS (Kecamatan) - Sleman
        // ================================================
        $slemanDistricts = [
            '340401' => ['GAMPING', -7.7833, 110.3167],
            '340402' => ['GODEAN', -7.7667, 110.2833],
            '340403' => ['MOYUDAN', -7.7500, 110.2333],
            '340404' => ['MINGGIR', -7.7167, 110.2333],
            '340405' => ['SEYEGAN', -7.7167, 110.2833],
            '340406' => ['MLATI', -7.7500, 110.3500],
            '340407' => ['DEPOK', -7.7667, 110.3833],
            '340408' => ['BERBAH', -7.8000, 110.4333],
            '340409' => ['PRAMBANAN', -7.7500, 110.5000],
            '340410' => ['KALASAN', -7.7667, 110.4667],
            '340411' => ['NGEMPLAK', -7.7167, 110.4333],
            '340412' => ['NGAGLIK', -7.7000, 110.3833],
            '340413' => ['SLEMAN', -7.7167, 110.3500],
            '340414' => ['TEMPEL', -7.6833, 110.3333],
            '340415' => ['TURI', -7.6500, 110.3500],
            '340416' => ['PAKEM', -7.6500, 110.4167],
            '340417' => ['CANGKRINGAN', -7.6167, 110.4500],
        ];
        
        foreach ($slemanDistricts as $code => $data) {
            DB::table('districts')
                ->where('code', $code)
                ->update([
                    'latitude' => $data[1],
                    'longitude' => $data[2],
                ]);
        }
        $this->command->info('✓ Updated coordinates for ' . count($slemanDistricts) . ' Sleman districts');
        
        // ================================================
        // DISTRICTS (Kecamatan) - Bantul
        // ================================================
        $bantulDistricts = [
            '340201' => ['SRANDAKAN', -7.9333, 110.2500],
            '340202' => ['SANDEN', -7.9500, 110.2833],
            '340203' => ['KRETEK', -7.9667, 110.3167],
            '340204' => ['PUNDONG', -7.9333, 110.3500],
            '340205' => ['BAMBANGLIPURO', -7.9167, 110.3333],
            '340206' => ['PANDAK', -7.9000, 110.3000],
            '340207' => ['BANTUL', -7.8833, 110.3333],
            '340208' => ['JETIS', -7.8667, 110.3500],
            '340209' => ['IMOGIRI', -7.9333, 110.3833],
            '340210' => ['DLINGO', -7.9167, 110.4333],
            '340211' => ['PLERET', -7.8833, 110.4000],
            '340212' => ['PIYUNGAN', -7.8500, 110.4333],
            '340213' => ['BANGUNTAPAN', -7.8333, 110.4000],
            '340214' => ['SEWON', -7.8333, 110.3500],
            '340215' => ['KASIHAN', -7.8167, 110.3167],
            '340216' => ['PAJANGAN', -7.8667, 110.2833],
            '340217' => ['SEDAYU', -7.8333, 110.2500],
        ];
        
        foreach ($bantulDistricts as $code => $data) {
            DB::table('districts')
                ->where('code', $code)
                ->update([
                    'latitude' => $data[1],
                    'longitude' => $data[2],
                ]);
        }
        $this->command->info('✓ Updated coordinates for ' . count($bantulDistricts) . ' Bantul districts');
        
        // ================================================
        // DISTRICTS (Kecamatan) - Kota Yogyakarta
        // ================================================
        $yogyaDistricts = [
            '347101' => ['MANTRIJERON', -7.8167, 110.3500],
            '347102' => ['KRATON', -7.8000, 110.3583],
            '347103' => ['MERGANGSAN', -7.8083, 110.3667],
            '347104' => ['UMBULHARJO', -7.8167, 110.3833],
            '347105' => ['KOTAGEDE', -7.8167, 110.4000],
            '347106' => ['GONDOKUSUMAN', -7.7833, 110.3833],
            '347107' => ['DANUREJAN', -7.7917, 110.3667],
            '347108' => ['PAKUALAMAN', -7.7917, 110.3750],
            '347109' => ['GONDOMANAN', -7.7917, 110.3583],
            '347110' => ['NGAMPILAN', -7.7917, 110.3500],
            '347111' => ['WIROBRAJAN', -7.7917, 110.3333],
            '347112' => ['GEDONGTENGEN', -7.7833, 110.3583],
            '347113' => ['JETIS', -7.7750, 110.3667],
            '347114' => ['TEGALREJO', -7.7667, 110.3500],
        ];
        
        foreach ($yogyaDistricts as $code => $data) {
            DB::table('districts')
                ->where('code', $code)
                ->update([
                    'latitude' => $data[1],
                    'longitude' => $data[2],
                ]);
        }
        $this->command->info('✓ Updated coordinates for ' . count($yogyaDistricts) . ' Yogyakarta City districts');
        
        // ================================================
        // KEY VILLAGES (Desa/Kelurahan) - Sleman Popular Areas
        // ================================================
        $keyVillages = [
            // Depok
            '3404070001' => ['CATURTUNGGAL', -7.7708, 110.3917],
            '3404070002' => ['MAGUWOHARJO', -7.7833, 110.4167],
            '3404070003' => ['CONDONGCATUR', -7.7583, 110.3917],
            // Mlati
            '3404060001' => ['SENDANGADI', -7.7417, 110.3583],
            '3404060002' => ['SINDUADI', -7.7583, 110.3667],
            '3404060003' => ['TLOGOADI', -7.7500, 110.3333],
            '3404060004' => ['SUMBERADI', -7.7333, 110.3417],
            '3404060005' => ['TIRTOADI', -7.7583, 110.3417],
            // Ngaglik
            '3404120001' => ['SARIHARJO', -7.7167, 110.3833],
            '3404120002' => ['SINDUHARJO', -7.7083, 110.3917],
            '3404120003' => ['MINOMARTANI', -7.7000, 110.4000],
            '3404120004' => ['SUKOHARJO', -7.6917, 110.3833],
            '3404120005' => ['DONOHARJO', -7.6917, 110.3667],
            '3404120006' => ['SARDONOHARJO', -7.6833, 110.3917],
            // Gamping
            '3404010001' => ['AMBARKETAWANG', -7.7917, 110.3250],
            '3404010002' => ['BANYURADEN', -7.7833, 110.3167],
            '3404010003' => ['NOGOTIRTO', -7.7750, 110.3333],
            '3404010004' => ['TRIHANGGO', -7.7667, 110.3167],
            '3404010005' => ['BALECATUR', -7.7917, 110.2917],
        ];
        
        foreach ($keyVillages as $code => $data) {
            DB::table('villages')
                ->where('code', $code)
                ->update([
                    'latitude' => $data[1],
                    'longitude' => $data[2],
                ]);
        }
        $this->command->info('✓ Updated coordinates for ' . count($keyVillages) . ' key villages in Sleman');
        
        $this->command->info('');
        $this->command->info('🗺️  Yogyakarta region coordinates seeding completed!');
    }
}
