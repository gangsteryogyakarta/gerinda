<?php
// Gerindra EMS - Login Debug Script
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$email = 'admin@gerindradiy.com';
$user = App\Models\User::where('email', $email)->first();

echo "---------------------------------\n";
echo "DEBUG LOGIN\n";
echo "---------------------------------\n";
if ($user) {
    echo "USER FOUND: " . $user->name . " (ID: " . $user->id . ")\n";
    echo "EMAIL: " . $user->email . "\n";
    echo "PASSWORD HASH OK: " . (Hash::check('lanjut2029', $user->password) ? 'YES' : 'NO') . "\n";
} else {
    echo "USER NOT FOUND: " . $email . "\n";
    $anyUser = App\Models\User::first();
    if ($anyUser) {
        echo "Found another user: " . $anyUser->email . "\n";
    } else {
        echo "DATABASE IS EMPTY (No users found)\n";
    }
}

echo "---------------------------------\n";
echo "SESSION CONFIG\n";
echo "---------------------------------\n";
echo "DRIVER: " . config('session.driver') . "\n";
echo "DOMAIN: " . config('session.domain') . "\n";
echo "URL: " . config('app.url') . "\n";
echo "DEBUG: " . (config('app.debug') ? 'TRUE' : 'FALSE') . "\n";
echo "---------------------------------\n";
