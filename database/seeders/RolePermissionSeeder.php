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
        $admin->givePermissionTo([
            'event.view', 'event.create', 'event.edit', 'event.publish',
            'registration.view', 'registration.create', 'registration.edit', 'registration.export',
            'checkin.perform', 'checkin.override', 'checkin.view_stats',
            'lottery.view', 'lottery.draw', 'lottery.manage_prizes',
            'massa.view', 'massa.create', 'massa.edit', 'massa.export',
            'report.view', 'report.export',
        ]);

        $operator = Role::firstOrCreate(['name' => 'operator']);
        $operator->givePermissionTo([
            'event.view',
            'registration.view', 'registration.create',
            'checkin.perform', 'checkin.view_stats',
            'lottery.view',
            'massa.view', 'massa.create',
            'report.view',
        ]);

        $checkinOfficer = Role::firstOrCreate(['name' => 'checkin-officer']);
        $checkinOfficer->givePermissionTo([
            'event.view',
            'registration.view',
            'checkin.perform', 'checkin.view_stats',
        ]);

        $viewer = Role::firstOrCreate(['name' => 'viewer']);
        $viewer->givePermissionTo([
            'event.view',
            'registration.view',
            'checkin.view_stats',
            'massa.view',
            'report.view',
        ]);

        // Create default super admin user
        $superAdminUser = User::firstOrCreate(
            ['email' => 'admin@gerindra.or.id'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('gerindra2024'),
            ]
        );
        $superAdminUser->assignRole('super-admin');

        // Create sample operator
        $operatorUser = User::firstOrCreate(
            ['email' => 'operator@gerindra.or.id'],
            [
                'name' => 'Operator',
                'password' => Hash::make('operator2024'),
            ]
        );
        $operatorUser->assignRole('operator');
    }
}
