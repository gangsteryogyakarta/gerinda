<?php

namespace Database\Seeders;

use App\Models\Province;
use App\Models\Regency;
use App\Models\District;
use App\Models\Village;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IndonesiaWilayahSeeder extends Seeder
{
    /**
     * Import complete Indonesia administrative regions from wilayah.id API.
     * Source: Kepmendagri No 300.2.2-2138 Tahun 2025
     * 
     * Codes from API use dot-separated format: 34.01, 34.01.01, 34.01.01.2001
     */
    public function run(): void
    {
        $this->command->info('Starting Indonesia Wilayah Import...');
        
        // Step 1: Import Provinces
        $this->importProvinces();
        
        // Step 2: Import Regencies for each Province
        $this->importAllRegencies();
        
        // Step 3: Import Districts for each Regency
        $this->importAllDistricts();
        
        // Step 4: Import Villages for each District
        $this->importAllVillages();
        
        $this->command->info('Indonesia Wilayah Import Complete!');
        $this->command->info('Provinces: ' . Province::count());
        $this->command->info('Regencies: ' . Regency::count());
        $this->command->info('Districts: ' . District::count());
        $this->command->info('Villages: ' . Village::count());
    }

    private function importProvinces(): void
    {
        $this->command->info('Importing provinces...');
        
        $response = Http::timeout(30)->get('https://wilayah.id/api/provinces.json');
        
        if (!$response->successful()) {
            $this->command->error('Failed to fetch provinces data');
            return;
        }
        
        $data = $response->json('data') ?? [];
        
        foreach ($data as $item) {
            Province::updateOrCreate(
                ['code' => $item['code']],
                ['name' => $item['name']]
            );
        }
        
        $this->command->info('Imported ' . count($data) . ' provinces');
    }

    private function importAllRegencies(): void
    {
        $this->command->info('Importing regencies...');
        
        $provinces = Province::all();
        $totalRegencies = 0;
        
        foreach ($provinces as $province) {
            // API expects dot-separated province code
            $response = Http::timeout(30)->get("https://wilayah.id/api/regencies/{$province->code}.json");
            
            if (!$response->successful()) {
                $this->command->warn("Failed to fetch regencies for province {$province->code}");
                continue;
            }
            
            $data = $response->json('data') ?? [];
            
            foreach ($data as $item) {
                // Store the dot-separated code from API
                Regency::updateOrCreate(
                    ['code' => $item['code']],
                    [
                        'province_id' => $province->id,
                        'name' => $item['name']
                    ]
                );
                $totalRegencies++;
            }
            
            // Small delay to avoid rate limiting
            usleep(100000); // 100ms
        }
        
        $this->command->info("Imported {$totalRegencies} regencies");
    }

    private function importAllDistricts(): void
    {
        $this->command->info('Importing districts (this may take a while)...');
        
        $regencies = Regency::all();
        $totalDistricts = 0;
        $processed = 0;
        
        foreach ($regencies as $regency) {
            // Use the dot-separated code directly from regency
            $response = Http::timeout(30)->get("https://wilayah.id/api/districts/{$regency->code}.json");
            
            if (!$response->successful()) {
                $this->command->warn("Failed to fetch districts for regency {$regency->code}");
                continue;
            }
            
            $data = $response->json('data') ?? [];
            
            foreach ($data as $item) {
                District::updateOrCreate(
                    ['code' => $item['code']],
                    [
                        'regency_id' => $regency->id,
                        'name' => $item['name']
                    ]
                );
                $totalDistricts++;
            }
            
            $processed++;
            if ($processed % 50 == 0) {
                $this->command->info("Processed {$processed}/{$regencies->count()} regencies...");
            }
            
            // Small delay to avoid rate limiting
            usleep(100000); // 100ms
        }
        
        $this->command->info("Imported {$totalDistricts} districts");
    }

    private function importAllVillages(): void
    {
        $this->command->info('Importing villages (this will take a while)...');
        
        $districts = District::all();
        $totalVillages = 0;
        $processed = 0;
        
        foreach ($districts as $district) {
            $response = Http::timeout(30)->get("https://wilayah.id/api/villages/{$district->code}.json");
            
            if (!$response->successful()) {
                // Log but don't spam console
                Log::warning("Failed to fetch villages for district {$district->code}");
                continue;
            }
            
            $data = $response->json('data') ?? [];
            
            foreach ($data as $item) {
                Village::updateOrCreate(
                    ['code' => $item['code']],
                    [
                        'district_id' => $district->id,
                        'name' => $item['name'],
                        'postal_code' => null // API doesn't include postal code
                    ]
                );
                $totalVillages++;
            }
            
            $processed++;
            if ($processed % 500 == 0) {
                $this->command->info("Processed {$processed}/{$districts->count()} districts, {$totalVillages} villages so far...");
            }
            
            // Small delay to avoid rate limiting
            usleep(50000); // 50ms
        }
        
        $this->command->info("Imported {$totalVillages} villages");
    }
}
