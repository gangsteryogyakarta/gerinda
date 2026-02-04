<?php

use App\Models\Massa;

$count = Massa::count();
$distribution = Massa::select('kategori_massa', \DB::raw('count(*) as total'))
    ->groupBy('kategori_massa')
    ->get();

echo "Total Massa: $count\n";
echo "Distribution:\n";
foreach ($distribution as $d) {
    echo "- {$d->kategori_massa}: {$d->total}\n";
}

$nullCount = Massa::whereNull('kategori_massa')->count();
echo "Null Kategori: $nullCount\n";
