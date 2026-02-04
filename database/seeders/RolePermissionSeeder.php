<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Event permissions
            'event.view',
            'event.create',
            'event.edit',
            'event.delete',
            'event.publish',
            
            // Registration permissions
            'registration.view',
            'registration.create',
            'registration.edit',
            'registration.delete',
            'registration.export',
            
            // Check-in permissions
            'checkin.perform',
            'checkin.override',
            'checkin.view_stats',
            
            // Lottery permissions
            'lottery.view',
            'lottery.draw',
            'lottery.manage_prizes',
            'lottery.undo',
            
            // Massa permissions
            'massa.view',
            'massa.create',
            'massa.edit',
            'massa.delete',
            'massa.export',
            
            // Report permissions
            'report.view',
            'report.export',
            
            // User management
            'user.view',
            'user.create',
            'user.edit',
            'user.delete',
            
            // System settings
            'settings.view',
            'settings.edit',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin']);
        $superAdmin->givePermissionTo(Permission::all());

        $admin = Role::firstOrCreate(['name' => 'admin']);
        // Admin gets all permissions EXCEPT user management and system settings
        $adminPermissions = Permission::where('name', 'not like', 'user.%')
            ->where('name', 'not like', 'settings.%')
            ->get();
        $admin->syncPermissions($adminPermissions);

        // Legacy roles removed: operator, checkin-officer, viewer

        // Create default super admin user
        $superAdminUser = User::firstOrCreate(
            ['email' => 'admin@gerindra.or.id'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('gerindra2024'),
            ]
        );
        $superAdminUser->assignRole('super-admin');

        // Operator sample user removed
    }
}
