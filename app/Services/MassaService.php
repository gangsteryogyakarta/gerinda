<?php

namespace App\Services;

use App\Models\Massa;
use App\Models\MassaLoyalty;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MassaService
{
    /**
     * Find or create massa by NIK (deduplication)
     */
    public function findOrCreateByNik(array $data): Massa
    {
        $nik = $data['nik'];
        
        $massa = Massa::findByNik($nik);
        
        if ($massa) {
            // Update existing massa with new data (optional fields only)
            $updateData = array_filter([
                'no_hp' => $data['no_hp'] ?? null,
                'email' => $data['email'] ?? null,
            ], fn($v) => !empty($v));
            
            if (!empty($updateData)) {
                $massa->update($updateData);
            }
            
            return $massa;
        }
        
        // Create new massa
        return DB::transaction(function () use ($data) {
            $massa = Massa::create($data);
            
            // Create loyalty record
            MassaLoyalty::create(['massa_id' => $massa->id]);
            
            return $massa;
        });
    }

    /**
     * Validate NIK format (16 digits)
     */
    public function validateNik(string $nik): bool
    {
        // NIK harus 16 digit angka
        return preg_match('/^\d{16}$/', $nik);
    }

    /**
     * Check if NIK already exists
     */
    public function nikExists(string $nik): bool
    {
        return Massa::where('nik', $nik)->exists();
    }

    /**
     * Geocode address to coordinates using configured provider
     */
    public function geocodeAddress(string $address): ?array
    {
        $provider = config('services.geocoding.provider', 'nominatim');
        
        try {
            if ($provider === 'google') {
                return $this->geocodeWithGoogle($address);
            }
            
            return $this->geocodeWithNominatim($address);
        } catch (\Exception $e) {
            Log::error('Geocoding failed', [
                'address' => $address,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    protected function geocodeWithNominatim(string $address): ?array
    {
        $response = Http::timeout(10)
            ->withHeaders(['User-Agent' => 'GerindraEventApp/1.0'])
            ->get('https://nominatim.openstreetmap.org/search', [
                'q' => $address . ', Indonesia',
                'format' => 'json',
                'limit' => 1,
            ]);

        if ($response->successful() && !empty($response->json())) {
            $result = $response->json()[0];
            return [
                'latitude' => (float) $result['lat'],
                'longitude' => (float) $result['lon'],
                'display_name' => $result['display_name'] ?? null,
            ];
        }

        return null;
    }

    protected function geocodeWithGoogle(string $address): ?array
    {
        $apiKey = config('services.google.maps_api_key');
        
        if (empty($apiKey)) {
            throw new \Exception('Google Maps API key not configured');
        }

        $response = Http::get('https://maps.googleapis.com/maps/api/geocode/json', [
            'address' => $address,
            'key' => $apiKey,
            'region' => 'id',
        ]);

        if ($response->successful()) {
            $data = $response->json();
            if ($data['status'] === 'OK' && !empty($data['results'])) {
                $location = $data['results'][0]['geometry']['location'];
                return [
                    'latitude' => $location['lat'],
                    'longitude' => $location['lng'],
                    'display_name' => $data['results'][0]['formatted_address'] ?? null,
                ];
            }
        }

        return null;
    }

    /**
     * Update massa coordinates from address
     */
    public function updateCoordinates(Massa $massa): bool
    {
        $address = $massa->full_address;
        
        if (empty($address)) {
            return false;
        }

        $coords = $this->geocodeAddress($address);
        
        if ($coords) {
            $massa->update([
                'latitude' => $coords['latitude'],
                'longitude' => $coords['longitude'],
            ]);
            return true;
        }

        return false;
    }

    /**
     * Alias for updateCoordinates for backward compatibility
     */
    public function geocodeMassa(Massa $massa): bool
    {
        return $this->updateCoordinates($massa);
    }

    /**
     * Batch geocode multiple massa
     */
    public function batchGeocode(array $massaIds, int $delayMs = 1000): array
    {
        $results = ['success' => 0, 'failed' => 0];
        
        $massaList = Massa::whereIn('id', $massaIds)
            ->whereNull('latitude')
            ->get();

        foreach ($massaList as $massa) {
            if ($this->updateCoordinates($massa)) {
                $results['success']++;
            } else {
                $results['failed']++;
            }
            
            // Rate limiting - wait between requests
            usleep($delayMs * 1000);
        }

        return $results;
    }

    /**
     * Get massa statistics by location
     */
    public function getLocationStats(): array
    {
        return Massa::active()
            ->selectRaw('province_id, COUNT(*) as total')
            ->groupBy('province_id')
            ->with('province:id,name')
            ->get()
            ->map(fn($item) => [
                'province' => $item->province?->name ?? 'Unknown',
                'total' => $item->total,
            ])
            ->toArray();
    }
}
