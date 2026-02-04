<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DEEP DIAGNOSTIC STATS ===\n";

// Massa Stats
$totalMassa = App\Models\Massa::count();
$geocodedMassa = App\Models\Massa::whereNotNull('latitude')->count();
$pendingMassa = $totalMassa - $geocodedMassa;

// Region Stats (Only those used by Massa)
$usedVillages = App\Models\Village::whereHas('massa')->count();
$geocodedUsedVillages = App\Models\Village::whereHas('massa')->whereNotNull('latitude')->count();
$pendingVillages = $usedVillages - $geocodedUsedVillages;

$usedDistricts = App\Models\District::whereHas('massa')->count();
$geocodedUsedDistricts = App\Models\District::whereHas('massa')->whereNotNull('latitude')->count();

// Failed Jobs
$failedJobs = DB::table('failed_jobs')->count();
$jobs = DB::table('jobs')->count();

echo "\n--- MASSA ---\n";
echo "Total: $totalMassa\n";
echo "Geocoded: $geocodedMassa (" . round(($geocodedMassa/$totalMassa)*100, 1) . "%)\n";
echo "Pending: $pendingMassa\n";

echo "\n--- REGIONS (Used by Massa) ---\n";
echo "Villages Used: $usedVillages\n";
echo "  - Has Coordinates: $geocodedUsedVillages\n";
echo "  - MISSING Coordinates: $pendingVillages\n";

echo "Districts Used: $usedDistricts\n";
echo "  - Has Coordinates: $geocodedUsedDistricts\n";

echo "\n--- QUEUE STATUS ---\n";
echo "Active Jobs in Queue: $jobs\n";
echo "Failed Jobs: $failedJobs\n";

// Show sample of pending Massa locations to see if there's a pattern
echo "\n--- SAMPLE PENDING LOCATIONS ---\n";
$samples = App\Models\Massa::whereNull('latitude')
    ->with(['province', 'regency', 'district', 'village'])
    ->limit(5)
    ->get();

foreach ($samples as $s) {
    echo "- ID {$s->id}: " . 
         ($s->village->name ?? 'No Village') . ", " . 
         ($s->district->name ?? 'No District') . ", " . 
         ($s->regency->name ?? 'No Regency') . "\n";
}
