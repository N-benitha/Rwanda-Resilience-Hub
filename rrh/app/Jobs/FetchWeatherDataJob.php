<?php

namespace App\Jobs;

use App\Services\WeatherService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FetchWeatherDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;
    public int $timeout = 120;

    private float $latitude;
    private float $longitude;
    private ?string $locationName;

    public function __construct(float $latitude, float $longitude, ?string $locationName = null)
    {
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->locationName = $locationName;
    }

    public function handle(WeatherService $weatherService): void
    {
        try {
            Log::info("Fetching weather data for coordinates: {$this->latitude}, {$this->longitude}");

            // Fetch current weather data
            $currentWeather = $weatherService->getCurrentWeather($this->latitude, $this->longitude);
            
            if ($currentWeather) {
                $weatherService->storeWeatherData(
                    $currentWeather,
                    $this->latitude,
                    $this->longitude,
                    $this->locationName
                );
                
                Log::info("Successfully stored weather data for {$this->locationName}");
                
                // Dispatch flood risk prediction job
                ProcessFloodRiskJob::dispatch($this->latitude, $this->longitude, $this->locationName)
                    ->delay(now()->addMinutes(2));
            } else {
                Log::warning("No weather data received for coordinates: {$this->latitude}, {$this->longitude}");
            }

            // Fetch NASA POWER historical data (weekly)
            $startDate = now()->subDays(7)->format('Ymd');
            $endDate = now()->format('Ymd');
            
            $nasaData = $weatherService->getNasaPowerData(
                $this->latitude, 
                $this->longitude, 
                $startDate, 
                $endDate
            );
            
            if ($nasaData && isset($nasaData['properties']['parameter'])) {
                $this->processNasaData($weatherService, $nasaData);
            }

        } catch (\Exception $e) {
            Log::error("Weather data fetch failed: " . $e->getMessage(), [
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
                'location_name' => $this->locationName,
                'exception' => $e
            ]);
            
            throw $e;
        }
    }

    private function processNasaData(WeatherService $weatherService, array $nasaData): void
    {
        $parameters = $nasaData['properties']['parameter'];
        
        // Process daily data
        if (isset($parameters['PRECTOTCORR'])) {
            foreach ($parameters['PRECTOTCORR'] as $date => $precipitation) {
                $recordDate = \Carbon\Carbon::createFromFormat('Ymd', $date);
                
                $weatherData = [
                    'temperature' => $parameters['T2M'][$date] ?? 0,
                    'humidity' => $parameters['RH2M'][$date] ?? 0,
                    'precipitation' => $precipitation,
                    'wind_speed' => $parameters['WS10M'][$date] ?? 0,
                    'pressure' => $parameters['PS'][$date] ?? 1000,
                    'weather_condition' => $this->determineConditionFromPrecipitation($precipitation),
                    'description' => 'NASA POWER historical data',
                    'raw_data' => [
                        'date' => $date,
                        'nasa_parameters' => array_map(fn($param) => $param[$date] ?? null, $parameters)
                    ],
                    'data_source' => 'nasa_power',
                    'recorded_at' => $recordDate
                ];
                
                $weatherService->storeWeatherData(
                    $weatherData,
                    $this->latitude,
                    $this->longitude,
                    $this->locationName
                );
            }
            
            Log::info("Processed NASA POWER data for {$this->locationName}");
        }
    }

    private function determineConditionFromPrecipitation(float $precipitation): string
    {
        if ($precipitation > 10) return 'Rain';
        if ($precipitation > 2) return 'Light Rain';
        if ($precipitation > 0) return 'Drizzle';
        return 'Clear';
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("FetchWeatherDataJob failed permanently", [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'location_name' => $this->locationName,
            'exception' => $exception->getMessage()
        ]);
    }
}