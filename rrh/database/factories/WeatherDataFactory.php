<?php

namespace Database\Factories;

use App\Models\WeatherData;
use Illuminate\Database\Eloquent\Factories\Factory;

class WeatherDataFactory extends Factory
{
    protected $model = WeatherData::class;

    public function definition(): array
    {
        // Rwanda coordinates bounds
        $rwandaLocations = [
            ['name' => 'Kigali', 'lat' => -1.9441, 'lng' => 30.0619],
            ['name' => 'Butare', 'lat' => -2.5967, 'lng' => 29.7395],
            ['name' => 'Gitarama', 'lat' => -2.0742, 'lng' => 29.7570],
            ['name' => 'Ruhengeri', 'lat' => -1.4993, 'lng' => 29.6333],
            ['name' => 'Gisenyi', 'lat' => -1.7049, 'lng' => 29.2565],
            ['name' => 'Byumba', 'lat' => -1.5756, 'lng' => 30.0677],
            ['name' => 'Cyangugu', 'lat' => -2.4841, 'lng' => 28.9070],
            ['name' => 'Kibungo', 'lat' => -2.1542, 'lng' => 30.7425],
        ];

        $location = $this->faker->randomElement($rwandaLocations);
        
        return [
            'latitude' => $location['lat'] + $this->faker->randomFloat(4, -0.1, 0.1),
            'longitude' => $location['lng'] + $this->faker->randomFloat(4, -0.1, 0.1),
            'location_name' => $location['name'], // Changed from 'location' to 'location_name'
            'temperature' => $this->faker->randomFloat(2, 15, 30), // Rwanda typical temps
            'humidity' => $this->faker->randomFloat(2, 40, 95),
            'precipitation' => $this->faker->randomFloat(2, 0, 200),
            'wind_speed' => $this->faker->randomFloat(2, 0, 25),
            'pressure' => $this->faker->randomFloat(2, 1000, 1020),
            'weather_condition' => $this->faker->randomElement([
                'clear', 'partly_cloudy', 'cloudy', 'rainy', 'thunderstorm', 'drizzle'
            ]),
            'description' => $this->faker->sentence(),
            'raw_data' => json_encode([
                'source_id' => $this->faker->uuid(),
                'api_version' => '2.5',
                'quality_score' => $this->faker->randomFloat(2, 0.8, 1.0)
            ]),
            'data_source' => $this->faker->randomElement(['openweathermap', 'nasa_power']),
            'recorded_at' => $this->faker->dateTimeBetween('-7 days', 'now'),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function openWeatherMap(): static
    {
        return $this->state(fn (array $attributes) => [
            'data_source' => 'openweathermap',
        ]);
    }

    public function nasaPower(): static
    {
        return $this->state(fn (array $attributes) => [
            'data_source' => 'nasa_power',
        ]);
    }

    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'recorded_at' => $this->faker->dateTimeBetween('-1 day', 'now'),
        ]);
    }
}