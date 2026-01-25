<?php
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Schema;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

echo 'Driver: ' . config('cache.default') . PHP_EOL;
try {
    echo 'Table [cache]: ' . (Schema::hasTable('cache') ? 'YES' : 'NO') . PHP_EOL;
} catch (\Throwable $e) {
    echo 'DB Error: ' . $e->getMessage() . PHP_EOL;
}
