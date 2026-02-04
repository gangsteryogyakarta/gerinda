<?php

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Massa;
use Illuminate\Support\Facades\DB;

try {
    $count = Massa::count();
    $distribution = Massa::select('kategori_massa', DB::raw('count(*) as total'))
        ->groupBy('kategori_massa')
        ->get();

    echo "Total Massa: $count\n";
    echo "Distribution:\n";
    foreach ($distribution as $d) {
        echo "- " . ($d->kategori_massa ?? 'NULL') . ": {$d->total}\n";
    }

    $nullCount = Massa::whereNull('kategori_massa')->count();
    echo "Null Kategori: $nullCount\n";
    
    // Auto-fix NULLs
    if ($nullCount > 0) {
        echo "Fixing NULL values...\n";
        Massa::whereNull('kategori_massa')->update(['kategori_massa' => 'Simpatisan']);
        echo "Fixed.\n";
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
