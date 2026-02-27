<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default super admin user (role_id = 1)
        User::updateOrCreate(
            ['email' => 'kalpeshmeniya96@gmail.com'],
            [
                'name' => 'Super Admin',
                'email' => 'kalpeshmeniya96@gmail.com',
                'password' => Hash::make('password'),
                'role_id' => 1, // Super admin role
                'is_active' => true,
                'email_verified_at' => now(),
                'timezone' => 'Asia/Kolkata',
                'language' => 'en',
            ]
        );

        User::updateOrCreate(
            ['email' => 'jatanmer88@gmail.com'],
            [
                'name' => 'Jatan Mer',
                'email' => 'jatanmer88@gmail.com',
                'password' => Hash::make('password'),
                'role_id' => 1, // Super admin role
                'is_active' => true,
                'email_verified_at' => now(),
                'timezone' => 'Asia/Kolkata',
                'language' => 'en',
            ]
        );

        // Create additional admin user (role_id = 2)
        User::updateOrCreate(
            ['email' => 'admin2@example.com'],
            [
                'name' => 'Admin Manager',
                'email' => 'admin2@example.com',
                'password' => Hash::make('password'),
                'role_id' => 2, // Admin role
                'is_active' => true,
                'email_verified_at' => now(),
                'timezone' => 'UTC',
                'language' => 'en',
            ]
        );

        // Create sample regular user (role_id = 3)
        User::updateOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'Regular User',
                'email' => 'user@example.com',
                'password' => Hash::make('password'),
                'role_id' => 3, // User role
                'is_active' => true,
                'email_verified_at' => now(),
                'timezone' => 'UTC',
                'language' => 'en',
            ]
        );
    }
}
