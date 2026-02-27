<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class RedirectDelaySettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Setting::updateOrCreate(
            ['key' => 'max_redirect_delay'],
            [
                'name' => 'Max Redirect Delay',
                'description' => 'Maximum allowed redirect delay in seconds (0-30)',
                'value' => '10',
                'type' => 'number',
                'group' => 'general',
                'sort_order' => 30,
                'is_public' => true,
                'is_active' => true,
            ]
        );
    }
}
