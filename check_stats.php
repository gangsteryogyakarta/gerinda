<?php
// Load Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Check Stats
$total = App\Models\Massa::count();
$geocoded = App\Models\Massa::whereNotNull('latitude')->count();
$villages = App\Models\Village::whereNotNull('latitude')->count();
$districts = App\Models\District::whereNotNull('latitude')->count();

echo "=== GEOCODING STATS ===\n";
echo "Total Massa: " . number_format($total) . "\n";
echo "Geocoded Massa: " . number_format($geocoded) . " (" . round(($geocoded/$total)*100, 1) . "%)\n";
echo "Pending: " . number_format($total - $geocoded) . "\n";
echo "-----------------------\n";
echo "Geocoded Villages: " . number_format($villages) . "\n";
echo "Geocoded Districts: " . number_format($districts) . "\n";
