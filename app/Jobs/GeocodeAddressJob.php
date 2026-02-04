<?php

namespace App\Jobs;

use App\Models\Massa;
use App\Services\MassaService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GeocodeAddressJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying.
     */
    public int $backoff = 60;

    /**
     * The Massa ID to geocode.
     */
    protected int $massaId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $massaId)
    {
        $this->massaId = $massaId;
        $this->onQueue('geocoding');
    }

    /**
     * Execute the job.
     */
    public function handle(MassaService $massaService): void
    {
        $massa = Massa::with(['village', 'district', 'regency', 'province'])->find($this->massaId);
        
        if (!$massa) {
            Log::warning("GeocodeAddressJob: Massa ID {$this->massaId} not found");
            return;
        }
        
        // Skip if already has coordinates from GPS or user input
        if ($massa->latitude && $massa->longitude && in_array($massa->geocode_source, ['gps', 'user_input'])) {
            Log::info("GeocodeAddressJob: Massa ID {$this->massaId} already has user coordinates, skipping");
            return;
        }
        
        // Build full address for geocoding
        $addressParts = array_filter([
            $massa->alamat,
            $massa->village?->name,
            $massa->district?->name,
            $massa->regency?->name,
            $massa->province?->name,
            'Indonesia'
        ]);
        $fullAddress = implode(', ', $addressParts);
        
        Log::info("GeocodeAddressJob: Geocoding address for Massa ID {$this->massaId}: {$fullAddress}");
        
        // Try geocoding
        $coords = $massaService->geocodeAddress($fullAddress);
        
        if ($coords && isset($coords['latitude']) && isset($coords['longitude'])) {
            $massa->update([
                'latitude' => $coords['latitude'],
                'longitude' => $coords['longitude'],
                'geocode_source' => 'nominatim',
                'geocoded_at' => now(),
            ]);
            Log::info("GeocodeAddressJob: Successfully geocoded Massa ID {$this->massaId}");
            return;
        }
        
        // Fallback to region coordinates
        $this->applyRegionFallback($massa);
    }

    /**
     * Apply cascading region fallback coordinates.
     */
    protected function applyRegionFallback(Massa $massa): void
    {
        $source = null;
        $lat = null;
        $lng = null;
        
        // Try Village first (most precise)
        if ($massa->village && $massa->village->latitude && $massa->village->longitude) {
            $lat = $massa->village->latitude + (rand(-30, 30) / 10000); // ~30m jitter
            $lng = $massa->village->longitude + (rand(-30, 30) / 10000);
            $source = 'village_fallback';
        }
        // Try District
        elseif ($massa->district && $massa->district->latitude && $massa->district->longitude) {
            $lat = $massa->district->latitude + (rand(-80, 80) / 10000); // ~80m jitter
            $lng = $massa->district->longitude + (rand(-80, 80) / 10000);
            $source = 'district_fallback';
        }
        // Try Regency
        elseif ($massa->regency && $massa->regency->latitude && $massa->regency->longitude) {
            $lat = $massa->regency->latitude + (rand(-150, 150) / 10000); // ~150m jitter
            $lng = $massa->regency->longitude + (rand(-150, 150) / 10000);
            $source = 'regency_fallback';
        }
        
        if ($lat && $lng && $source) {
            $massa->update([
                'latitude' => $lat,
                'longitude' => $lng,
                'geocode_source' => $source,
                'geocoded_at' => now(),
            ]);
            Log::info("GeocodeAddressJob: Applied {$source} for Massa ID {$this->massaId}");
        } else {
            Log::warning("GeocodeAddressJob: No fallback coordinates available for Massa ID {$this->massaId}");
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("GeocodeAddressJob failed for Massa ID {$this->massaId}: " . $exception->getMessage());
    }
}
