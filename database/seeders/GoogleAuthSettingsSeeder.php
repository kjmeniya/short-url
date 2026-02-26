<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class GoogleAuthSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'google_auth_enabled',
                'name' => 'Enable Google Authentication',
                'description' => 'Allow users to login/register using their Google account',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'authentication',
                'sort_order' => 1,
                'is_public' => false,
                'is_active' => true,
            ],
            [
                'key' => 'google_client_id',
                'name' => 'Google Client ID',
                'description' => 'Google OAuth Client ID from Google Cloud Console',
                'value' => '',
                'type' => 'text',
                'group' => 'authentication',
                'sort_order' => 2,
                'is_public' => false,
                'is_active' => true,
            ],
            [
                'key' => 'google_client_secret',
                'name' => 'Google Client Secret',
                'description' => 'Google OAuth Client Secret from Google Cloud Console',
                'value' => '',
                'type' => 'text',
                'group' => 'authentication',
                'sort_order' => 3,
                'is_public' => false,
                'is_active' => true,
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
