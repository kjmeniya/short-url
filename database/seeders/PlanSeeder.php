<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Free',
                'description' => 'Basic features for personal use.',
                'price' => 0.00,
                'sort_order' => 1,
                'features' => [
                    'max_links' => '100',
                    'custom_alias' => 'false',
                    'analytics' => 'basic'
                ]
            ],
            [
                'name' => 'Basic',
                'description' => 'Advanced features for professionals.',
                'price' => 9.99,
                'sort_order' => 2,
                'features' => [
                    'max_links' => '1000',
                    'custom_alias' => 'true',
                    'analytics' => 'advanced',
                    'api_access' => 'true'
                ]
            ],
            [
                'name' => 'Pro',
                'description' => 'Unlimited features for businesses.',
                'price' => 29.99,
                'sort_order' => 3,
                'features' => [
                    'max_links' => '-1', // unlimited
                    'custom_alias' => 'true',
                    'analytics' => 'premium',
                    'api_access' => 'true',
                    'team_members' => '5'
                ]
            ]
        ];

        foreach ($plans as $planData) {
            $features = $planData['features'];
            unset($planData['features']);

            $planData['slug'] = Str::slug($planData['name']);
            $planData['is_active'] = true;

            $plan = Plan::create($planData);

            foreach ($features as $name => $value) {
                $plan->features()->create([
                    'feature_name' => $name,
                    'feature_value' => $value
                ]);
            }
        }
    }
}
