<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$email = 'admin@gerindra.or.id';
$password = 'password123';

echo "Mencari user $email...\n";
$user = App\Models\User::where('email', $email)->first();

if ($user) {
    echo "User ditemukan! ID: " . $user->id . "\n";
    $user->password = Illuminate\Support\Facades\Hash::make($password);
    $user->save();
    echo "Password BERHASIL diubah menjadi: $password\n";
} else {
    echo "User TIDAK DITEMUKAN. Membuat user baru...\n";
    $user = App\Models\User::create([
        'name' => 'Administrator',
        'email' => $email,
        'password' => Illuminate\Support\Facades\Hash::make($password),
    ]);
    $user->syncRoles(['super-admin']);
    echo "User BARU berhasil dibuat dengan password: $password\n";
}
