<?php

namespace Database\Factories;

use App\Models\WeatherData;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class WeatherDataFactory extends Factory
{
    protected $model = WeatherData::class;

    public function definition(): array
    {
        $locations = [
            ['name' => 'Kigali', 'lat' => -1.9441, 'lng' => 30.0619],
            ['name' => 'Butare', 'lat' => -2.5967, 'lng' => 29.7395],
            ['name' => 'Gisenyi', 'lat' => -1.7030, 'lng' => 29.2569],
            ['name' => 'Ruhengeri', 'lat' => -1.4983, 'lng' => 29.6339],
            ['name' => 'Gitarama', 'lat' => -2.0758, 'lng' => 29.7564]
        ];

        $location = $this->faker->randomElement($locations);
        $conditions = ['Clear', 'Partly Cloudy', 'Cloudy', 'Rain', 'Thunderstorm', 'Drizzle'];
        $selectedCondition = $this->faker->randomElement($conditions);

        return [
            'location' => $location['name'],
            'latitude' => $location['lat'] + $this->faker->randomFloat(4, -0.1, 0.1),
            'longitude' => $location['lng'] + $this->faker->randomFloat(4, -0.1, 0.1),
            'temperature' => $this->faker->randomFloat(2, 15, 35),
            'humidity' => $this->faker->numberBetween(30, 95),
            'pressure' => $this->faker->randomFloat(2, 950, 1050),
            'precipitation' => $this->getPrecipitationForCondition($selectedCondition),
            'wind_speed' => $this->faker->randomFloat(2, 0, 25),
            'wind_direction' => $this->faker->numberBetween(0, 359),
            'visibility' => $this->faker->randomFloat(2, 1, 50),
            'weather_condition' => $selectedCondition,
            'weather_description' => $this->getDescriptionForCondition($selectedCondition),
            'cloud_cover' => $this->getCloudCoverForCondition($selectedCondition),
            'uv_index' => $this->faker->randomFloat(1, 0, 11),
            'solar_radiation' => $this->faker->randomFloat(2, 0, 1000),
            'recorded_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'api_source' => $this->faker->randomElement(['openweather', 'nasa'])
        ];
    }

    private function getPrecipitationForCondition(string $condition): float
    {
        return match ($condition) {
            'Clear' => 0,
            'Partly Cloudy' => $this->faker->randomFloat(2, 0, 2),
            'Cloudy' => $this->faker->randomFloat(2, 0, 5),
            'Drizzle' => $this->faker->randomFloat(2, 1, 8),
            'Rain' => $this->faker->randomFloat(2, 5, 25),
            'Thunderstorm' => $this->faker->randomFloat(2, 10, 50),
            default => $this->faker->randomFloat(2, 0, 10)
        };
    }

    private function getDescriptionForCondition(string $condition): string
    {
        return match ($condition) {
            'Clear' => $this->faker->randomElement(['clear sky', 'sunny']),
            'Partly Cloudy' => $this->faker->randomElement(['few clouds', 'scattered clouds']),
            'Cloudy' => $this->faker->randomElement(['broken clouds', 'overcast clouds']),
            'Drizzle' => $this->faker->randomElement(['light drizzle', 'drizzle']),
            'Rain' => $this->faker->randomElement(['light rain', 'moderate rain', 'heavy rain']),
            'Thunderstorm' => $this->faker->randomElement(['thunderstorm with rain', 'heavy thunderstorm']),
            default => 'unknown'
        };
    }

    private function getCloudCoverForCondition(string $condition): int
    {
        return match ($condition) {
            'Clear' => $this->faker->numberBetween(0, 20),
            'Partly Cloudy' => $this->faker->numberBetween(20, 60),
            'Cloudy' => $this->faker->numberBetween(60, 90),
            'Rain', 'Thunderstorm', 'Drizzle' => $this->faker->numberBetween(80, 100),
            default => $this->faker->numberBetween(0, 100)
        };
    }

    public function highPrecipitation(): static
    {
        return $this->state(fn (array $attributes) => [
            'precipitation' => $this->faker->randomFloat(2, 20, 80),
            'weather_condition' => $this->faker->randomElement(['Rain', 'Thunderstorm']),
            'cloud_cover' => $this->faker->numberBetween(80, 100)
        ]);
    }

    public function forLocation(string $location): static
    {
        $coordinates = $this->getCoordinatesForLocation($location);
        
        return $this->state(fn (array $attributes) => [
            'location' => $location,
            'latitude' => $coordinates['lat'],
            'longitude' => $coordinates['lng']
        ]);
    }

    private function getCoordinatesForLocation(string $location): array
    {
        $locations = [
            'Kigali' => ['lat' => -1.9441, 'lng' => 30.0619],
            'Butare' => ['lat' => -2.5967, 'lng' => 29.7395],
            'Gisenyi' => ['lat' => -1.7030, 'lng' => 29.2569],
            'Ruhengeri' => ['lat' => -1.4983, 'lng' => 29.6339],
            'Gitarama' => ['lat' => -2.0758, 'lng' => 29.7564]
        ];

        return $locations[$location] ?? ['lat' => -2.0, 'lng' => 30.0];
    }
}