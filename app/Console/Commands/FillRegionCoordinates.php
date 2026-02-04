<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Village;
use App\Models\District;
use App\Models\Regency;
use App\Models\Massa;
use App\Services\MassaService;
use Illuminate\Support\Facades\Log;

class FillRegionCoordinates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'geocoding:fill-regions {--limit=100}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fill missing latitude/longitude for Regions (Village, District, Regency) used by Massa';

    /**
     * Execute the console command.
     */
    public function handle(MassaService $massaService)
    {
        $this->info('Starting Region Coordinate Filling...');
        
        // 1. Fill Villages first (Most precise fallback)
        $this->fillVillages($massaService);
        
        // 2. Fill Districts
        $this->fillDistricts($massaService);
        
        // 3. Fill Regencies
        $this->fillRegencies($massaService);
        
        $this->info('Region Filling Completed!');
    }
    
    protected function fillVillages(MassaService $service)
    {
        $this->info("Scanning Villages...");
        
        // Find villages used by Massa but missing coordinates
        $villages = Village::whereHas('massa')
            ->where(function($q) {
                $q->whereNull('latitude')->orWhereNull('longitude');
            })
            ->with(['district.regency.province'])
            ->limit($this->option('limit'))
            ->get();
            
        $this->info("Found " . $villages->count() . " villages to geocode.");
        $bar = $this->output->createProgressBar($villages->count());
        
        foreach ($villages as $village) {
            $districtName = $village->district->name ?? '';
            $regencyName = $village->district->regency->name ?? '';
            // Clean names (remove "KABUPATEN" prefix if double)
            $regencyName = str_replace('KABUPATEN ', '', $regencyName);
            
            // Attempt 1: Full Strict
            $queries = [
                "Kelurahan {$village->name}, Kecamatan {$districtName}, {$regencyName}",
                "Desa {$village->name}, Kecamatan {$districtName}, {$regencyName}",
                "{$village->name}, {$districtName}, {$regencyName}",
                "{$village->name}, {$regencyName}"
            ];

            $coords = null;
            foreach ($queries as $q) {
                $coords = $service->geocodeAddress($q);
                if ($coords) {
                    $this->info("  [OK] Found: $q");
                    break;
                }
                usleep(500000); // 0.5s pause between retries
            }
            
            if ($coords) {
                $village->update([
                    'latitude' => $coords['latitude'],
                    'longitude' => $coords['longitude']
                ]);
            } else {
                $this->warn("  [FAIL] Could not geocode: {$village->name}");
            }
            
            $bar->advance();
            usleep(1000000); // 1s delay
        }
        
        $bar->finish();
        $this->newLine();
    }
    
    protected function fillDistricts(MassaService $service)
    {
        $this->info("Scanning Districts...");
        
        $districts = District::whereHas('massa')
            ->where(function($q) {
                $q->whereNull('latitude')->orWhereNull('longitude');
            })
            ->with(['regency.province'])
            ->limit($this->option('limit'))
            ->get();
            
        $this->info("Found " . $districts->count() . " districts to geocode.");
        $bar = $this->output->createProgressBar($districts->count());
        
        foreach ($districts as $district) {
            $regencyName = $district->regency->name ?? '';
            $regencyName = str_replace('KABUPATEN ', '', $regencyName);
            
            $queries = [
                "Kecamatan {$district->name}, {$regencyName}",
                "{$district->name}, {$regencyName}"
            ];
            
            $coords = null;
            foreach ($queries as $q) {
                $coords = $service->geocodeAddress($q);
                if ($coords) break;
                usleep(500000);
            }
            
            if ($coords) {
                $district->update([
                    'latitude' => $coords['latitude'],
                    'longitude' => $coords['longitude']
                ]);
                $this->info("  [OK] District: {$district->name}");
            } else {
                $this->warn("  [FAIL] District: {$district->name}");
            }
            
            $bar->advance();
            usleep(1000000);
        }
        
        $bar->finish();
        $this->newLine();
    }
    
    protected function fillRegencies(MassaService $service)
    {
        $this->info("Scanning Regencies...");
        
        $regencies = Regency::whereHas('massa')
            ->where(function($q) {
                $q->whereNull('latitude')->orWhereNull('longitude');
            })
            ->limit($this->option('limit'))
            ->get();
            
        $this->info("Found " . $regencies->count() . " regencies to geocode.");
        $bar = $this->output->createProgressBar($regencies->count());
        
        foreach ($regencies as $regency) {
            $query = "{$regency->name}, Indonesia";
            
            $coords = $service->geocodeAddress($query);
            
            if ($coords) {
                $regency->update([
                    'latitude' => $coords['latitude'],
                    'longitude' => $coords['longitude']
                ]);
            }
            
            $bar->advance();
            usleep(1100000);
        }
        
        $bar->finish();
        $this->newLine();
    }
}
