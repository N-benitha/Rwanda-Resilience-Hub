<?php

namespace Database\Factories;

use App\Models\FloodRisk;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class FloodRiskFactory extends Factory
{
    protected $model = FloodRisk::class;

    public function definition(): array
    {
        $locations = ['Kigali', 'Butare', 'Gisenyi', 'Ruhengeri', 'Gitarama'];
        $riskLevels = ['low', 'moderate', 'high', 'extreme'];
        $severityLevels = ['minor', 'moderate', 'significant', 'severe', 'catastrophic'];
        
        $riskLevel = $this->faker->randomElement($riskLevels);
        $probability = $this->getProbabilityForRiskLevel($riskLevel);
        $severity = $this->getSeverityForRiskLevel($riskLevel);

        return [
            'location' => $this->faker->randomElement($locations),
            'date' => $this->faker->dateTimeBetween('-30 days', '+7 days')->format('Y-m-d'),
            'risk_level' => $riskLevel,
            'probability' => $probability,
            'severity' => $severity,
            'factors' => $this->generateFactors($riskLevel),
            'recommendations' => $this->generateRecommendations($riskLevel),
            'precipitation_7day' => $this->faker->randomFloat(2, 0, 200),
            'temperature_avg' => $this->faker->randomFloat(2, 15, 35),
            'humidity_avg' => $this->faker->randomFloat(2, 40, 95),
            'calculated_at' => $this->faker->dateTimeBetween('-1 hour', 'now')
        ];
    }

    private function getProbabilityForRiskLevel(string $riskLevel): float
    {
        return match ($riskLevel) {
            'low' => $this->faker->randomFloat(2, 0, 25),
            'moderate' => $this->faker->randomFloat(2, 25, 50),
            'high' => $this->faker->randomFloat(2, 50, 80),
            'extreme' => $this->faker->randomFloat(2, 80, 100),
            default => $this->faker->randomFloat(2, 0, 100)
        };
    }

    private function getSeverityForRiskLevel(string $riskLevel): string
    {
        return match ($riskLevel) {
            'low' => $this->faker->randomElement(['minor', 'moderate']),
            'moderate' => $this->faker->randomElement(['moderate', 'significant']),
            'high' => $this->faker->randomElement(['significant', 'severe']),
            'extreme' => $this->faker->randomElement(['severe', 'catastrophic']),
            default => 'moderate'
        };
    }

    private function generateFactors(string $riskLevel): array
    {
        $allFactors = [
            'Heavy rainfall in the past 7 days',
            'Saturated soil conditions',
            'High river water levels',
            'Poor drainage infrastructure',
            'Deforestation in watershed areas',
            'Urban development in flood zones',
            'Blocked drainage channels',
            'Steep terrain topography',
            'Previous flood history',
            'Seasonal weather patterns',
            'Climate change impacts',
            'Infrastructure vulnerability'
        ];

        $factorCount = match ($riskLevel) {
            'low' => $this->faker->numberBetween(1, 3),
            'moderate' => $this->faker->numberBetween(2, 4),
            'high' => $this->faker->numberBetween(3, 6),
            'extreme' => $this->faker->numberBetween(4, 8),
            default => 3
        };

        return $this->faker->randomElements($allFactors, $factorCount);
    }

    private function generateRecommendations(string $riskLevel): array
    {
        $recommendations = [
            'low' => [
                'Monitor weather forecasts regularly',
                'Ensure drainage systems are clear',
                'Review emergency evacuation plans',
                'Check flood insurance coverage'
            ],
            'moderate' => [
                'Prepare emergency supply kits',
                'Identify safe evacuation routes',
                'Monitor local water levels',
                'Secure outdoor equipment',
                'Stay informed through official channels'
            ],
            'high' => [
                'Consider temporary evacuation',
                'Move valuables to higher ground',
                'Avoid travel in flood-prone areas',
                'Prepare for power outages',
                'Keep emergency contacts ready',
                'Monitor weather alerts continuously'
            ],
            'extreme' => [
                'Evacuate immediately if advised',
                'Avoid all unnecessary travel',
                'Stay on higher ground',
                'Have emergency supplies ready',
                'Follow official evacuation orders',
                'Prepare for extended disruptions',
                'Keep battery-powered radio available'
            ]
        ];

        return $recommendations[$riskLevel] ?? $recommendations['moderate'];
    }

    public function highRisk(): static
    {
        return $this->state(fn (array $attributes) => [
            'risk_level' => $this->faker->randomElement(['high', 'extreme']),
            'probability' => $this->faker->randomFloat(2, 60, 100),
            'severity' => $this->faker->randomElement(['severe', 'catastrophic']),
            'precipitation_7day' => $this->faker->randomFloat(2, 50, 200)
        ]);
    }

    public function forLocation(string $location): static
    {
        return $this->state(fn (array $attributes) => [
            'location' => $location
        ]);
    }

    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => $this->faker->dateTimeBetween('-7 days', 'now')->format('Y-m-d'),
            'calculated_at' => $this->faker->dateTimeBetween('-6 hours', 'now')
        ]);
    }
}