<?php
use Illuminate\Contracts\Console\Kernel;
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$email = 'admin@gerindradiy.com';
$u = \App\Models\User::where('email', $email)->first();

if (!$u) {
    echo "User $email NOT FOUND!" . PHP_EOL;
    exit;
}

echo "User: " . $u->email . PHP_EOL;
$roles = $u->getRoleNames();
echo "Roles: " . $roles . PHP_EOL;

if ($roles->isEmpty()) {
    echo "WARNING: User has NO ROLES assigned." . PHP_EOL;
}
