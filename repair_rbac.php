<?php

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- START REPAIR ---\n";

// 1. Refresh Permissions
app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
echo "Cache cleared.\n";

// 2. Ensure Super Admin role has ALL permissions
$superAdmin = Role::firstOrCreate(['name' => 'super-admin']);
$allPermissions = Permission::all();
$superAdmin->syncPermissions($allPermissions);
echo "Super Admin synced with " . $allPermissions->count() . " permissions.\n";

// 3. Fix User
$email = 'admin@gerindradiy.com';
$user = User::where('email', $email)->first();

if ($user) {
    $user->syncRoles(['super-admin']);
    echo "User {$email} assigned to super-admin.\n";
    
    // Verify
    echo "Roles: " . implode(', ', $user->getRoleNames()->toArray()) . "\n";
    echo "Can user.view? " . ($user->can('user.view') ? 'YES' : 'NO') . "\n";
} else {
    echo "User {$email} NOT FOUND!\n";
}

echo "--- END REPAIR ---\n";
