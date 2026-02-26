<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            // Path to the countries SQL file
            $sqlPath = resource_path('data/countries.sql');
            
            if (!file_exists($sqlPath)) {
                throw new \Exception('Countries SQL file not found at: ' . $sqlPath);
            }

            // Disable foreign key checks temporarily
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            
            // Execute the SQL file directly
            DB::unprepared(file_get_contents($sqlPath));
            
            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            $this->command->info('Successfully seeded all countries from SQL file.');
            
        } catch (\Exception $e) {
            Log::error('Error seeding countries: ' . $e->getMessage());
            $this->command->error('Error seeding countries: ' . $e->getMessage());
            
            // Re-enable foreign key checks in case of error
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }
}
