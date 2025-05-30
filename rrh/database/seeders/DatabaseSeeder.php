<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\WeatherData;
use App\Models\FloodRisk;
use App\Models\SensorData;
use App\Models\Report;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@rrh.gov.rw',
            'password' => Hash::make('password'),
            'user_type' => 'admin',
            'location_name' => 'Kigali',
            'latitude' => -1.9441,
            'longitude' => 30.0619,
            'email_verified_at' => now(),
        ]);

        // Create meteorologist
        $meteorologist = User::factory()->create([
            'name' => 'John Meteorologist',
            'email' => 'meteo@rrh.gov.rw',
            'password' => Hash::make('password'),
            'user_type' => 'meteorologist',
            'location_name' => 'Kigali',
            'latitude' => -1.9441,
            'longitude' => 30.0619,
            'email_verified_at' => now(),
        ]);

        // Create government users
        User::factory(5)->create([
            'user_type' => 'government',
            'email_verified_at' => now(),
        ]);

        // Create civilian users
        User::factory(20)->create([
            'user_type' => 'civilian',
            'email_verified_at' => now(),
        ]);

        // Create weather data
        WeatherData::factory(100)->create();
        WeatherData::factory(50)->openWeatherMap()->create();
        WeatherData::factory(30)->nasaPower()->recent()->create();

        // Create flood risk data
        FloodRisk::factory(20)->lowRisk()->create();
        FloodRisk::factory(15)->moderateRisk()->create();
        FloodRisk::factory(10)->highRisk()->create();
        FloodRisk::factory(5)->criticalRisk()->create();

        // Create sensor data for some users
        $civilianUsers = User::where('user_type', 'civilian')->take(10)->get();
        
        foreach ($civilianUsers as $user) {
            SensorData::factory(rand(2, 8))->create([
                'user_id' => $user->id,
                'latitude' => $user->latitude ?? fake()->latitude(-2.8, -1.0),
                'longitude' => $user->longitude ?? fake()->longitude(28.8, 30.9),
            ]);
        }

        // Create reports
        Report::factory(15)->create([
            'user_id' => $admin->id,
            'type' => 'flood_risk',
        ]);

        Report::factory(10)->create([
            'user_id' => $meteorologist->id,
            'type' => 'weather_analysis',
        ]);

        Report::factory(8)->create([
            'user_id' => $admin->id,
            'type' => 'predictive',
        ]);

        $this->command->info('Database seeded successfully!');
        $this->command->info('Admin login: admin@rrh.gov.rw / password');
        $this->command->info('Meteorologist login: meteo@rrh.gov.rw / password');
    }
}