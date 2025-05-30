<?php

namespace Database\Factories;

use App\Models\FloodRisk;
use Illuminate\Database\Eloquent\Factories\Factory;

class FloodRiskFactory extends Factory
{
    protected $model = FloodRisk::class;

    public function definition(): array
    {
        // Rwanda coordinates bounds with flood-prone areas
        $rwandaRiskAreas = [
            ['name' => 'Kigali City', 'lat' => -1.9441, 'lng' => 30.0619, 'base_risk' => 30],
            ['name' => 'Nyabugogo Valley', 'lat' => -1.9350, 'lng' => 30.0588, 'base_risk' => 70],
            ['name' => 'Rusizi Valley', 'lat' => -2.4841, 'lng' => 28.9070, 'base_risk' => 80],
            ['name' => 'Akagera Basin', 'lat' => -2.1542, 'lng' => 30.7425, 'base_risk' => 60],
            ['name' => 'Ruhengeri Plains', 'lat' => -1.4993, 'lng' => 29.6333, 'base_risk' => 45],
            ['name' => 'Kivu Lake Shore', 'lat' => -1.7049, 'lng' => 29.2565, 'base_risk' => 55],
        ];

        $location = $this->faker->randomElement($rwandaRiskAreas);
        $riskPercentage = $location['base_risk'] + $this->faker->numberBetween(-20, 20);
        $riskPercentage = max(0, min(100, $riskPercentage)); // Clamp between 0-100

        // Determine risk level based on percentage
        $riskLevel = match (true) {
            $riskPercentage >= 80 => 'critical',
            $riskPercentage >= 60 => 'high',
            $riskPercentage >= 30 => 'moderate',
            default => 'low'
        };

        $predictionDate = $this->faker->dateTimeBetween('now', '+7 days');
        $validUntil = (clone $predictionDate)->modify('+24 hours');

        return [
            'latitude' => $location['lat'] + $this->faker->randomFloat(4, -0.05, 0.05),
            'longitude' => $location['lng'] + $this->faker->randomFloat(4, -0.05, 0.05),
            'location_name' => $location['name'],
            'risk_percentage' => $riskPercentage,
            'risk_level' => $riskLevel,
            'predicted_precipitation' => $this->faker->randomFloat(2, 0, 150),
            'soil_moisture_level' => $this->faker->randomFloat(2, 20, 95),
            'river_level' => $this->faker->randomFloat(2, 0.5, 8.0),
            'contributing_factors' => json_encode([
                'recent_rainfall' => $this->faker->randomFloat(2, 0, 100),
                'soil_saturation' => $this->faker->randomFloat(2, 0, 100),
                'river_flow_rate' => $this->faker->randomFloat(2, 0, 50),
                'topography_factor' => $this->faker->randomFloat(2, 0.1, 1.0),
                'deforestation_impact' => $this->faker->randomFloat(2, 0, 0.8),
            ]),
            'ai_analysis' => json_encode([
                'model_confidence' => $this->faker->randomFloat(2, 0.7, 0.98),
                'key_indicators' => $this->faker->randomElements([
                    'high_precipitation_forecast',
                    'saturated_soil_conditions',
                    'elevated_river_levels',
                    'upstream_rainfall',
                    'poor_drainage'
                ], $this->faker->numberBetween(2, 4)),
                'recommendation' => $this->faker->randomElement([
                    'monitor_closely',
                    'prepare_evacuation_routes',
                    'issue_early_warning',
                    'activate_emergency_response'
                ]),
                'historical_precedent' => $this->faker->boolean(70),
            ]),
            'prediction_date' => $predictionDate,
            'valid_until' => $validUntil,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function lowRisk(): static
    {
        return $this->state(fn (array $attributes) => [
            'risk_percentage' => $this->faker->randomFloat(2, 0, 29),
            'risk_level' => 'low',
        ]);
    }

    public function moderateRisk(): static
    {
        return $this->state(fn (array $attributes) => [
            'risk_percentage' => $this->faker->randomFloat(2, 30, 59),
            'risk_level' => 'moderate',
        ]);
    }

    public function highRisk(): static
    {
        return $this->state(fn (array $attributes) => [
            'risk_percentage' => $this->faker->randomFloat(2, 60, 79),
            'risk_level' => 'high',
        ]);
    }

    public function criticalRisk(): static
    {
        return $this->state(fn (array $attributes) => [
            'risk_percentage' => $this->faker->randomFloat(2, 80, 100),
            'risk_level' => 'critical',
        ]);
    }
}