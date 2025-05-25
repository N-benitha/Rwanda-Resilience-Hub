<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\WeatherData;
use App\Models\FloodRisk;
use App\Models\SensorData;
use App\Models\Report;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Starting database seeding...');

        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@rrh.gov.rw'],
            [
                'name' => 'RRH Administrator',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'user_type' => 'admin',
            ]
        );

        // Create regular users
        $users = User::factory(10)->create();
        
        // Create meteorologist users
        $meteorologists = User::factory(3)->create([
            'user_type' => 'meteorologist'
        ]);

        $this->command->info('Created users: ' . ($users->count() + $meteorologists->count() + 1));

        // Create weather data for major Rwanda cities
        $rwandaCities = [
            ['name' => 'Kigali', 'lat' => -1.9441, 'lon' => 30.0619],
            ['name' => 'Huye', 'lat' => -2.5967, 'lon' => 29.7387],
            ['name' => 'Musanze', 'lat' => -1.4999, 'lon' => 29.6357],
            ['name' => 'Rubavu', 'lat' => -1.6792, 'lon' => 29.2678],
            ['name' => 'Nyagatare', 'lat' => -1.2918, 'lon' => 30.3392],
            ['name' => 'Muhanga', 'lat' => -2.0853, 'lon' => 29.7389],
            ['name' => 'Karongi', 'lat' => -2.0069, 'lon' => 29.3265],
            ['name' => 'Kayonza', 'lat' => -1.8833, 'lon' => 30.6167]
        ];

        foreach ($rwandaCities as $city) {
            // Current weather data
            WeatherData::factory(5)->create([
                'location' => $city['name'],
                'latitude' => $city['lat'],
                'longitude' => $city['lon'],
                'forecast_type' => 'current'
            ]);

            // Forecast data
            WeatherData::factory(10)->forecast()->create([
                'location' => $city['name'],
                'latitude' => $city['lat'],
                'longitude' => $city['lon']
            ]);

            // Rainy season data
            WeatherData::factory(8)->rainyseason()->create([
                'location' => $city['name'],
                'latitude' => $city['lat'],
                'longitude' => $city['lon']
            ]);
        }

        $this->command->info('Created weather data for 8 major cities');

        // Create flood risk assessments
        $weatherDataIds = WeatherData::pluck('id')->toArray();
        $userIds = User::pluck('id')->toArray();

        // High risk areas (mainly Kigali flood-prone zones)
        FloodRisk::factory(15)->high()->create([
            'weather_data_id' => $this->command->choice ? null : fake()->randomElement($weatherDataIds),
            'user_id' => fake()->randomElement($userIds)
        ]);

        // Medium risk areas
        FloodRisk::factory(20)->medium()->create([
            'weather_data_id' => fake()->randomElement($weatherDataIds),
            'user_id' => fake()->randomElement($userIds)
        ]);

        // Low risk areas
        FloodRisk::factory(25)->low()->create([
            'weather_data_id' => fake()->randomElement($weatherDataIds),
            'user_id' => fake()->randomElement($userIds)
        ]);

        // Critical risk areas (few emergency cases)
        FloodRisk::factory(5)->critical()->alertSent()->create([
            'weather_data_id' => fake()->randomElement($weatherDataIds),
            'user_id' => fake()->randomElement($userIds)
        ]);

        $this->command->info('Created flood risk assessments');

        // Create sensor data
        $sensorLocations = [
            ['name' => 'Nyabugogo Sensor Station', 'lat' => -1.9355, 'lon' => 30.0610],
            ['name' => 'Kimisagara Monitoring Point', 'lat' => -1.9567, 'lon' => 30.0588],
            ['name' => 'Nyabarongo River Gauge', 'lat' => -1.9800, 'lon' => 30.0500],
            ['name' => 'Kigali Airport Weather Station', 'lat' => -1.9686, 'lon' => 30.1394],
        ];

        foreach ($sensorLocations as $location) {
            SensorData::factory(50)->create([
                'location' => $location['name'],
                'latitude' => $location['lat'],
                'longitude' => $location['lon']
            ]);
        }

        $this->command->info('Created sensor data for monitoring stations');

        // Create reports
        $floodRiskIds = FloodRisk::pluck('id')->toArray();

        // Daily reports
        Report::factory(30)->daily()->create([
            'generated_by' => fake()->randomElement(array_merge([$admin->id], $meteorologists->pluck('id')->toArray())),
            'flood_risk_ids' => fake()->randomElements($floodRiskIds, fake()->numberBetween(1, 5))
        ]);

        // Weekly reports
        Report::factory(8)->weekly()->create([
            'generated_by' => fake()->randomElement($meteorologists->pluck('id')->toArray()),
            'flood_risk_ids' => fake()->randomElements($floodRiskIds, fake()->numberBetween(3, 10))
        ]);

        // Monthly reports
        Report::factory(3)->monthly()->create([
            'generated_by' => $admin->id,
            'flood_risk_ids' => fake()->randomElements($floodRiskIds, fake()->numberBetween(5, 15))
        ]);

        // Emergency reports
        Report::factory(5)->emergency()->create([
            'generated_by' => fake()->randomElement(array_merge([$admin->id], $meteorologists->pluck('id')->toArray())),
            'flood_risk_ids' => FloodRisk::where('risk_level', 'critical')->pluck('id')->toArray()
        ]);

        $this->command->info('Created various types of reports');

        $this->command->info('Database seeding completed successfully!');
        $this->command->newLine();
        $this->command->table(
            ['Resource', 'Count'],
            [
                ['Users', User::count()],
                ['Weather Data', WeatherData::count()],
                ['Flood Risks', FloodRisk::count()],
                ['Sensor Data', SensorData::count()],
                ['Reports', Report::count()],
            ]
        );

        $this->command->info('Admin Login: admin@rrh.gov.rw / password');
    }
}