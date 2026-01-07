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
            ['email' => 'admin@gerindra.id'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
            ]
        );
        
        $this->command->info('User Admin created: admin@gerindra.id / password');

        // Optional: Assign role if Spatie Permission is installed
        // if (class_exists(\Spatie\Permission\Models\Role::class)) {
        //     $user->assignRole('admin');
        // }
    }
}
