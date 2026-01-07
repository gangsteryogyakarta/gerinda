<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class SettingController extends Controller
{
    /**
     * Display settings page
     */
    public function index()
    {
        $settings = [
            'geocoding_provider' => config('services.geocoding.provider', 'nominatim'),
            'google_maps_configured' => !empty(config('services.google_maps.api_key')),
            'wa_gateway_configured' => !empty(config('services.wa_gateway.url')),
            'queue_driver' => config('queue.default'),
            'cache_driver' => config('cache.default'),
        ];

        return view('settings.index', compact('settings'));
    }

    /**
     * Clear application caches
     */
    public function clearCache()
    {
        Artisan::call('optimize:clear');
        Cache::flush();

        return back()->with('success', 'Cache berhasil dibersihkan!');
    }

    /**
     * Test WhatsApp Gateway connection
     */
    public function testWhatsapp(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|string',
        ]);

        try {
            $service = app(\App\Services\NotificationService::class);
            $result = $service->sendWhatsApp(
                $validated['phone'],
                "Ini adalah pesan test dari Gerindra Event Management System.\n\nWaktu: " . now()->format('d/m/Y H:i:s')
            );

            if ($result) {
                return back()->with('success', 'Pesan test berhasil dikirim ke ' . $validated['phone']);
            } else {
                return back()->with('error', 'Gagal mengirim pesan. Periksa konfigurasi WA Gateway.');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Run database migrations
     */
    public function migrate()
    {
        try {
            Artisan::call('migrate', ['--force' => true]);
            $output = Artisan::output();

            return back()->with('success', 'Migrasi berhasil dijalankan. ' . $output);
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
