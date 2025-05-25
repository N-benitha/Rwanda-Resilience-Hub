<?php

namespace App\Services;

use App\Models\FloodRisk;
use App\Models\WeatherData;
use App\Models\SensorData;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class FloodPredictionService
{
    private Client $client;
    private WeatherService $weatherService;
    private string $groqApiKey;
    private string $groqBaseUrl;
    private string $groqModel;

    public function __construct(WeatherService $weatherService)
    {
        $this->client = new Client(['timeout' => 60]);
        $this->weatherService = $weatherService;
        $this->groqApiKey = config('services.groq.api_key');
        $this->groqBaseUrl = config('services.groq.base_url');
        $this->groqModel = config('services.groq.model');
    }

    public function predictFloodRisk(float $lat, float $lon, ?string $locationName = null): FloodRisk
    {
        // Gather weather data
        $currentWeather = $this->weatherService->getCurrentWeather($lat, $lon);
        $forecast = $this->weatherService->getWeatherForecast($lat, $lon);
        $historicalData = $this->weatherService->getHistoricalWeatherData($lat, $lon, 30);
        
        // Get sensor data if available
        $sensorData = $this->getSensorData($lat, $lon);
        
        // Calculate base risk factors
        $riskFactors = $this->calculateRiskFactors($currentWeather, $forecast, $historicalData, $sensorData);
        
        // Get AI analysis
        $aiAnalysis = $this->getAIAnalysis($riskFactors, $currentWeather, $forecast);
        
        // Calculate final risk percentage and level
        $riskPercentage = $this->calculateRiskPercentage($riskFactors);
        $riskLevel = $this->determineRiskLevel($riskPercentage);
        
        return FloodRisk::create([
            'latitude' => $lat,
            'longitude' => $lon,
            'location_name' => $locationName,
            'risk_percentage' => $riskPercentage,
            'risk_level' => $riskLevel,
            'predicted_precipitation' => $riskFactors['predicted_precipitation'],
            'soil_moisture_level' => $riskFactors['soil_moisture'] ?? null,
            'river_level' => $riskFactors['river_level'] ?? null,
            'contributing_factors' => $riskFactors,
            'ai_analysis' => $aiAnalysis,
            'prediction_date' => now(),
            'valid_until' => now()->addHours(24),
        ]);
    }

    private function calculateRiskFactors(array $currentWeather, array $forecast, array $historicalData, array $sensorData): array
    {
        $factors = [
            'current_precipitation' => 0,
            'predicted_precipitation' => 0,
            'rainfall_intensity' => 0,
            'historical_average' => 0,
            'soil_saturation' => 0,
            'temperature_factor' => 0,
            'wind_factor' => 0,
            'pressure_factor' => 0,
        ];

        if ($currentWeather) {
            $factors['current_precipitation'] = $this->extractPrecipitation($currentWeather);
            $factors['temperature_factor'] = $this->calculateTemperatureFactor($currentWeather['temperature']);
            $factors['wind_factor'] = $this->calculateWindFactor($currentWeather['wind_speed']);
            $factors['pressure_factor'] = $this->calculatePressureFactor($currentWeather['pressure']);
        }

        if ($forecast && isset($forecast['list'])) {
            $totalPredicted = 0;
            $maxIntensity = 0;
            
            foreach (array_slice($forecast['list'], 0, 24) as $item) { // Next 24 hours
                $precipitation = $this->extractPrecipitation($item);
                $totalPredicted += $precipitation;
                $maxIntensity = max($maxIntensity, $precipitation);
            }
            
            $factors['predicted_precipitation'] = $totalPredicted;
            $factors['rainfall_intensity'] = $maxIntensity;
        }

        if ($historicalData) {
            $historicalPrecipitation = array_column($historicalData, 'precipitation');
            $factors['historical_average'] = array_sum($historicalPrecipitation) / count($historicalPrecipitation);
            $factors['soil_saturation'] = $this->calculateSoilSaturation($historicalPrecipitation);
        }

        // Include sensor data if available
        if (!empty($sensorData)) {
            $factors['soil_moisture'] = $sensorData['soil_moisture'] ?? null;
            $factors['river_level'] = $sensorData['water_level'] ?? null;
            $factors['sensor_rainfall'] = $sensorData['rainfall'] ?? null;
        }

        return $factors;
    }

    private function extractPrecipitation(array $weatherData): float
    {
        if (isset($weatherData['rain']['1h'])) {
            return $weatherData['rain']['1h'];
        }
        if (isset($weatherData['rain']['3h'])) {
            return $weatherData['rain']['3h'] / 3;
        }
        return 0;
    }

    private function calculateTemperatureFactor(float $temperature): float
    {
        // Higher temperatures can increase evaporation but also convection
        if ($temperature > 30) return 1.2;
        if ($temperature > 25) return 1.1;
        if ($temperature < 10) return 0.9;
        return 1.0;
    }

    private function calculateWindFactor(float $windSpeed): float
    {
        // Strong winds can intensify storms
        if ($windSpeed > 15) return 1.3;
        if ($windSpeed > 10) return 1.1;
        return 1.0;
    }

    private function calculatePressureFactor(float $pressure): float
    {
        // Low pressure indicates storm systems
        if ($pressure < 1000) return 1.4;
        if ($pressure < 1010) return 1.2;
        if ($pressure > 1020) return 0.8;
        return 1.0;
    }

    private function calculateSoilSaturation(array $historicalPrecipitation): float
    {
        $recentRainfall = array_sum(array_slice($historicalPrecipitation, -7)); // Last 7 days
        $saturationThreshold = 100; // mm in 7 days
        return min(100, ($recentRainfall / $saturationThreshold) * 100);
    }

    private function calculateRiskPercentage(array $factors): float
    {
        $baseRisk = 0;
        
        // Precipitation risk (40% weight)
        $precipitationRisk = min(100, ($factors['predicted_precipitation'] / 50) * 100);
        $baseRisk += $precipitationRisk * 0.4;
        
        // Soil saturation risk (25% weight)
        $baseRisk += $factors['soil_saturation'] * 0.25;
        
        // Intensity risk (20% weight)
        $intensityRisk = min(100, ($factors['rainfall_intensity'] / 20) * 100);
        $baseRisk += $intensityRisk * 0.2;
        
        // Environmental factors (15% weight)
        $environmentalFactor = $factors['temperature_factor'] * $factors['wind_factor'] * $factors['pressure_factor'];
        $baseRisk += min(100, ($environmentalFactor - 1) * 100) * 0.15;
        
        // Sensor data adjustment
        if (isset($factors['soil_moisture']) && $factors['soil_moisture'] > 80) {
            $baseRisk *= 1.2;
        }
        
        if (isset($factors['river_level']) && $factors['river_level'] > 5) {
            $baseRisk *= 1.3;
        }
        
        return min(100, max(0, $baseRisk));
    }

    private function determineRiskLevel(float $percentage): string
    {
        if ($percentage >= 75) return 'critical';
        if ($percentage >= 50) return 'high';
        if ($percentage >= 25) return 'moderate';
        return 'low';
    }

    private function getSensorData(float $lat, float $lon): array
    {
        $sensorData = SensorData::where('latitude', '>=', $lat - 0.01)
            ->where('latitude', '<=', $lat + 0.01)
            ->where('longitude', '>=', $lon - 0.01)
            ->where('longitude', '<=', $lon + 0.01)
            ->where('created_at', '>=', now()->subHours(6))
            ->where('is_validated', true)
            ->latest()
            ->first();

        return $sensorData ? $sensorData->toArray() : [];
    }

    private function getAIAnalysis(array $riskFactors, ?array $currentWeather, ?array $forecast): ?array
    {
        if (!$this->groqApiKey) {
            return null;
        }

        $cacheKey = 'ai_analysis_' . md5(json_encode($riskFactors));
        
        return Cache::remember($cacheKey, 1800, function () use ($riskFactors, $currentWeather, $forecast) {
            try {
                $prompt = $this->buildAnalysisPrompt($riskFactors, $currentWeather, $forecast);
                
                $response = $this->client->post($this->groqBaseUrl . '/chat/completions', [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->groqApiKey,
                        'Content-Type' => 'application/json',
                    ],
                    'json' => [
                        'model' => $this->groqModel,
                        'messages' => [
                            ['role' => 'system', 'content' => 'You are a flood risk analysis expert for Rwanda. Provide concise, actionable flood risk analysis in JSON format.'],
                            ['role' => 'user', 'content' => $prompt]
                        ],
                        'max_tokens' => 500,
                        'temperature' => 0.3
                    ]
                ]);

                $data = json_decode($response->getBody()->getContents(), true);
                $analysis = $data['choices'][0]['message']['content'] ?? null;
                
                return json_decode($analysis, true) ?? ['analysis' => $analysis];
            } catch (GuzzleException $e) {
                Log::error('Groq AI API Error: ' . $e->getMessage());
                return null;
            }
        });
    }

    private function buildAnalysisPrompt(array $riskFactors, ?array $currentWeather, ?array $forecast): string
    {
        return "Analyze flood risk for Rwanda location with these conditions:
        
Risk Factors:
- Predicted precipitation: {$riskFactors['predicted_precipitation']}mm
- Soil saturation: {$riskFactors['soil_saturation']}%
- Rainfall intensity: {$riskFactors['rainfall_intensity']}mm/h
- Historical average: {$riskFactors['historical_average']}mm

Current Weather: " . json_encode($currentWeather) . "

Forecast Data Available: " . (bool)$forecast . "

Return JSON with:
{
  \"risk_summary\": \"Brief risk assessment\",
  \"key_factors\": [\"factor1\", \"factor2\"],
  \"recommendations\": [\"action1\", \"action2\"],
  \"confidence_level\": \"high/medium/low\"
}";
    }

    public function getFloodRiskByLocation(float $lat, float $lon, int $hours = 24): ?FloodRisk
    {
        return FloodRisk::where('latitude', '>=', $lat - 0.01)
            ->where('latitude', '<=', $lat + 0.01)
            ->where('longitude', '>=', $lon - 0.01)
            ->where('longitude', '<=', $lon + 0.01)
            ->where('valid_until', '>', now())
            ->where('prediction_date', '>=', now()->subHours($hours))
            ->latest('prediction_date')
            ->first();
    }

    public function getRegionalFloodRisks(array $bounds): array
    {
        return FloodRisk::whereBetween('latitude', [$bounds['south'], $bounds['north']])
            ->whereBetween('longitude', [$bounds['west'], $bounds['east']])
            ->where('valid_until', '>', now())
            ->orderBy('risk_percentage', 'desc')
            ->get()
            ->toArray();
    }
}