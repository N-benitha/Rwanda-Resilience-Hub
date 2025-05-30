<?php

namespace Database\Factories;

use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReportFactory extends Factory
{
    protected $model = Report::class;

    public function definition(): array
    {
        $type = $this->faker->randomElement(['flood_risk', 'weather_analysis', 'sensor_summary', 'predictive']);
        
        $periodStart = $this->faker->dateTimeBetween('-30 days', '-1 day');
        $periodEnd = (clone $periodStart)->modify('+' . rand(1, 7) . ' days');

        return [
            'user_id' => User::factory(),
            'title' => $this->generateTitle($type),
            'type' => $type,
            'summary' => $this->generateSummary($type),
            'content' => $this->generateContent($type),
            'data_sources' => json_encode($this->generateDataSources($type)),
            'charts_config' => json_encode($this->generateChartsConfig($type)),
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'status' => $this->faker->randomElement(['generated', 'reviewed', 'published', 'archived']),
            'file_path' => $this->faker->boolean(60) ? 'reports/' . $this->faker->uuid() . '.pdf' : null,
            'created_at' => $this->faker->dateTimeBetween('-7 days', 'now'),
            'updated_at' => now(),
        ];
    }

    private function generateTitle(string $type): string
    {
        return match ($type) {
            'flood_risk' => $this->faker->randomElement([
                'Flood Risk Assessment for ' . $this->faker->randomElement(['Kigali', 'Butare', 'Gitarama', 'Ruhengeri']),
                'Weekly Flood Risk Analysis - ' . $this->faker->monthName(),
                'Emergency Flood Risk Report - ' . $this->faker->date('Y-m-d'),
            ]),
            'weather_analysis' => $this->faker->randomElement([
                'Weather Pattern Analysis - ' . $this->faker->monthName() . ' ' . $this->faker->year(),
                'Seasonal Weather Forecast Report',
                'Climate Anomaly Detection Report',
            ]),
            'sensor_summary' => $this->faker->randomElement([
                'IoT Sensor Data Summary - Week ' . $this->faker->numberBetween(1, 52),
                'Community Sensor Network Report',
                'Sensor Data Quality Assessment',
            ]),
            'predictive' => $this->faker->randomElement([
                'AI-Powered Flood Prediction Model Results',
                'Machine Learning Weather Forecast Analysis',
                'Predictive Analytics Report - ' . $this->faker->date('Y-m'),
            ]),
        };
    }

    private function generateSummary(string $type): string
    {
        return match ($type) {
            'flood_risk' => 'This report analyzes current flood risk conditions across monitored regions in Rwanda, incorporating weather data, sensor readings, and predictive modeling to assess potential flooding threats.',
            'weather_analysis' => 'Comprehensive analysis of weather patterns, including temperature trends, precipitation levels, and atmospheric conditions affecting Rwanda\'s climate and agricultural activities.',
            'sensor_summary' => 'Summary of data collected from distributed IoT sensors monitoring environmental conditions, water levels, and weather parameters across Rwanda.',
            'predictive' => 'AI-driven analysis providing forecasts and predictions for weather conditions, flood risks, and environmental changes based on historical and real-time data patterns.',
        };
    }

    private function generateContent(string $type): string
    {
        $baseContent = match ($type) {
            'flood_risk' => "# Flood Risk Analysis Report\n\n## Executive Summary\nThis comprehensive flood risk assessment evaluates current conditions and potential threats across monitored regions in Rwanda.\n\n## Risk Assessment\n- **High Risk Areas**: 3 locations identified\n- **Moderate Risk Areas**: 8 locations under monitoring\n- **Low Risk Areas**: 15 locations stable\n\n## Key Findings\n- Recent precipitation levels have increased by 15% compared to seasonal averages\n- Soil saturation levels are approaching critical thresholds in 3 districts\n- River water levels show upward trends in major basins\n\n## Recommendations\n1. Maintain enhanced monitoring in high-risk areas\n2. Prepare early warning systems for potential activation\n3. Review evacuation routes and emergency response protocols",
            
            'weather_analysis' => "# Weather Analysis Report\n\n## Overview\nDetailed analysis of weather conditions and patterns affecting Rwanda's climate systems.\n\n## Temperature Trends\n- Average temperature: 22.5°C (±2.1°C)\n- Maximum recorded: 28.3°C in Kigali\n- Minimum recorded: 16.8°C in highland regions\n\n## Precipitation Analysis\n- Total rainfall: 145mm (current period)\n- Distribution: Uneven with concentration in western regions\n- Comparison to historical averages: +12%\n\n## Atmospheric Conditions\n- Humidity levels: 65-85% range\n- Wind patterns: Predominantly easterly winds\n- Pressure systems: Stable with minor fluctuations",
            
            'sensor_summary' => "# Sensor Network Data Summary\n\n## Network Status\n- Active sensors: 47 of 52 deployed\n- Data transmission rate: 98.2%\n- Battery levels: Average 78%\n\n## Data Quality Metrics\n- Validated readings: 94.6%\n- Anomalies detected: 12 instances\n- Calibration required: 3 sensors\n\n## Environmental Readings\n- Temperature range: 16.8°C - 28.3°C\n- Humidity range: 42% - 89%\n- Soil moisture: 35% - 82%\n- Water levels: 0.2m - 3.4m above baseline",
            
            'predictive' => "# Predictive Analytics Report\n\n## Model Performance\n- Accuracy: 89.3%\n- Confidence interval: 85-95%\n- Prediction horizon: 72 hours\n\n## Flood Risk Predictions\n- Next 24 hours: Low to moderate risk\n- 48-72 hours: Elevated risk in 2 regions\n- Weekly outlook: Stable conditions expected\n\n## Weather Forecasts\n- Temperature: Gradual increase expected\n- Precipitation: 40% chance of rainfall\n- Wind conditions: Light to moderate speeds\n\n## AI Model Insights\nMachine learning algorithms identify seasonal patterns and anomalies, providing enhanced prediction capabilities for flood risk management.",
        };

        return $baseContent . "\n\n## Data Sources\n" . implode("\n", array_map(
            fn($source) => "- {$source}",
            $this->generateDataSources($type)
        )) . "\n\n---\n*Report generated on " . now()->format('Y-m-d H:i:s') . "*";
    }

    private function generateDataSources(string $type): array
    {
        $common = ['OpenWeatherMap API', 'NASA POWER', 'IoT Sensor Network'];
        
        return match ($type) {
            'flood_risk' => array_merge($common, ['River Gauge Stations', 'Satellite Imagery', 'Historical Flood Records']),
            'weather_analysis' => array_merge($common, ['Meteorological Stations', 'Climate Models', 'Atmospheric Soundings']),
            'sensor_summary' => ['IoT Sensor Network', 'Community Sensors', 'Automated Weather Stations'],
            'predictive' => array_merge($common, ['ML Models', 'Historical Data', 'Real-time Feeds']),
        };
    }

    private function generateChartsConfig(string $type): array
    {
        return match ($type) {
            'flood_risk' => [
                'risk_levels_pie' => [
                    'type' => 'pie',
                    'title' => 'Flood Risk Distribution',
                    'data' => ['Low' => 60, 'Moderate' => 30, 'High' => 8, 'Critical' => 2]
                ],
                'timeline_chart' => [
                    'type' => 'line',
                    'title' => 'Risk Level Timeline',
                    'x_axis' => 'date',
                    'y_axis' => 'risk_percentage'
                ]
            ],
            'weather_analysis' => [
                'temperature_trend' => [
                    'type' => 'line',
                    'title' => 'Temperature Trends',
                    'period' => '30_days'
                ],
                'precipitation_bar' => [
                    'type' => 'bar',
                    'title' => 'Precipitation by Region',
                    'grouping' => 'location'
                ]
            ],
            'sensor_summary' => [
                'sensor_status' => [
                    'type' => 'gauge',
                    'title' => 'Network Health',
                    'value' => 94.6
                ],
                'data_quality' => [
                    'type' => 'bar',
                    'title' => 'Data Quality by Sensor',
                    'threshold' => 90
                ]
            ],
            'predictive' => [
                'prediction_accuracy' => [
                    'type' => 'gauge',
                    'title' => 'Model Accuracy',
                    'value' => 89.3
                ],
                'forecast_timeline' => [
                    'type' => 'area',
                    'title' => 'Prediction Timeline',
                    'confidence_bands' => true
                ]
            ]
        };
    }

    public function floodRisk(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'flood_risk',
            'title' => $this->generateTitle('flood_risk'),
        ]);
    }

    public function weatherAnalysis(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'weather_analysis',
            'title' => $this->generateTitle('weather_analysis'),
        ]);
    }

    public function sensorSummary(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'sensor_summary',
            'title' => $this->generateTitle('sensor_summary'),
        ]);
    }

    public function predictive(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'predictive',
            'title' => $this->generateTitle('predictive'),
        ]);
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
            'file_path' => 'reports/' . $this->faker->uuid() . '.pdf',
        ]);
    }
}