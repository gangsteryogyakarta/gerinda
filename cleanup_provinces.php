<?php
// cleanup_provinces.php - Remove non-DIY provinces

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Province;
use App\Models\Regency;

// Show current provinces
echo "Current Provinces:\n";
$provinces = Province::all();
foreach ($provinces as $province) {
    echo "  - ID: {$province->id}, Name: {$province->name}\n";
}

echo "\n";

// Keep only DI Yogyakarta (code 34)
$diy = Province::where('code', '34')->first();

if (!$diy) {
    echo "DI Yogyakarta not found!\n";
    exit(1);
}

// Delete other provinces
$deleted = Province::where('id', '!=', $diy->id)->delete();
echo "Deleted {$deleted} non-DIY provinces.\n";

// Show stats
echo "\nFinal Statistics:\n";
echo "  - Provinces: " . Province::count() . "\n";
echo "  - Regencies: " . Regency::count() . "\n";
echo "  - Districts: " . \App\Models\District::count() . "\n";
echo "  - Villages: " . \App\Models\Village::count() . "\n";

echo "\nDone!\n";
