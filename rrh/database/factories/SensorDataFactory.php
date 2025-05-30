<?php

namespace Database\Factories;

use App\Models\SensorData;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SensorDataFactory extends Factory
{
    protected $model = SensorData::class;

    public function definition(): array
    {
        $rwandaLocations = [
            'Kigali', 'Butare', 'Gitarama', 'Ruhengeri', 'Gisenyi', 
            'Byumba', 'Cyangugu', 'Kibungo', 'Nyanza', 'Muhanga'
        ];

        return [
            'user_id' => User::factory(),
            'sensor_id' => 'SENSOR_' . $this->faker->unique()->regexify('[A-Z0-9]{8}'),
            'latitude' => $this->faker->latitude(-2.8, -1.0), // Rwanda bounds
            'longitude' => $this->faker->longitude(28.8, 30.9), // Rwanda bounds
            'location_name' => $this->faker->randomElement($rwandaLocations),
            'rainfall' => $this->faker->randomFloat(2, 0, 50),
            'temperature' => $this->faker->randomFloat(2, 15, 30),
            'humidity' => $this->faker->randomFloat(2, 40, 95),
            'wind_speed' => $this->faker->randomFloat(2, 0, 25),
            'water_level' => $this->faker->randomFloat(2, 0, 10),
            'flow_level' => $this->faker->randomFloat(2, 0, 15),
            'soil_moisture' => $this->faker->randomFloat(2, 20, 90),
            'image_path' => $this->faker->boolean(30) ? 'sensors/images/' . $this->faker->uuid() . '.jpg' : null,
            'video_path' => $this->faker->boolean(15) ? 'sensors/videos/' . $this->faker->uuid() . '.mp4' : null,
            'additional_data' => json_encode([
                'battery_level' => $this->faker->randomFloat(2, 20, 100),
                'signal_strength' => $this->faker->randomFloat(2, -100, -30),
                'data_quality' => $this->faker->randomElement(['excellent', 'good', 'fair', 'poor']),
                'calibration_date' => $this->faker->dateTimeBetween('-6 months', '-1 month')->format('Y-m-d'),
            ]),
            'is_validated' => $this->faker->boolean(70),
            'created_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'updated_at' => now(),
        ];
    }

    public function validated(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_validated' => true,
        ]);
    }

    public function unvalidated(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_validated' => false,
        ]);
    }

    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => $this->faker->dateTimeBetween('-7 days', 'now'),
        ]);
    }

    public function withMedia(): static
    {
        return $this->state(fn (array $attributes) => [
            'image_path' => 'sensors/images/' . $this->faker->uuid() . '.jpg',
            'video_path' => $this->faker->boolean(50) ? 'sensors/videos/' . $this->faker->uuid() . '.mp4' : null,
        ]);
    }
}