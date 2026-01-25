<?php
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\View;
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

echo "Step 1: Testing Database Query..." . PHP_EOL;
try {
    $users = \App\Models\User::with('roles')->latest()->paginate(10);
    echo "Query Success. Found " . $users->count() . " users." . PHP_EOL;
} catch (\Throwable $e) {
    echo "FATAL ERROR IN QUERY: " . $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
    exit(1);
}

echo "Step 2: Testing View Rendering..." . PHP_EOL;
try {
    // Mocking auth user for view policies/checks
    $admin = \App\Models\User::where('email', 'admin@gerindradiy.com')->first();
    if($admin) {
        \Illuminate\Support\Facades\Auth::login($admin);
    }
    
    // Attempt to verify if view exists
    if (!View::exists('users.index')) {
        throw new Exception("View 'users.index' not found!");
    }

    // Try to render
    $html = View::make('users.index', compact('users'))->render();
    echo "View Render Success. Output length: " . strlen($html) . " characters." . PHP_EOL;
} catch (\Throwable $e) {
    echo "FATAL ERROR IN VIEW RENDER: " . $e->getMessage() . PHP_EOL;
    // Get previous exception if available (often view errors are wrapped)
    if ($e->getPrevious()) {
        echo "Previous Error: " . $e->getPrevious()->getMessage() . PHP_EOL;
    }
    echo $e->getTraceAsString() . PHP_EOL;
    exit(1);
}

echo "ALL CHECKS PASSED." . PHP_EOL;
