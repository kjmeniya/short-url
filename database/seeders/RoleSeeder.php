<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'id' => 1,
                'name' => 'super_admin',
                'display_name' => 'Super Administrator',
                'description' => 'Super administrator with full system access',
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'id' => 2,
                'name' => 'admin',
                'display_name' => 'Administrator',
                'description' => 'Administrator with elevated permissions',
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'id' => 3,
                'name' => 'user',
                'display_name' => 'User',
                'description' => 'Regular user with basic permissions',
                'is_active' => true,
                'is_system' => true,
            ],
        ];

        foreach ($roles as $roleData) {
            Role::updateOrCreate(
                ['name' => $roleData['name']],
                $roleData
            );
        }
    }
}
