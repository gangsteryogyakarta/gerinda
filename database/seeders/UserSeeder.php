<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin User
        $user = User::firstOrCreate(
            ['email' => 'admin@gerindradiy.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('lanjut2029'),
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
            ]
        );
        
        $this->command->info('User Admin created: admin@gerindradiy.com / lanjut2029');

        // Optional: Assign role if Spatie Permission is installed
        // if (class_exists(\Spatie\Permission\Models\Role::class)) {
        //     $user->assignRole('admin');
        // }
    }
}
