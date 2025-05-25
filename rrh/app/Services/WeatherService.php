<?php

namespace App\Services;

use App\Models\WeatherData;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class WeatherService
{
    private Client $client;
    private string $openWeatherApiKey;
    private string $openWeatherBaseUrl;
    private string $nasaPowerBaseUrl;
    private string $nasaPowerParams;

    public function __construct()
    {
        $this->client = new Client(['timeout' => 30]);
        $this->openWeatherApiKey = config('services.openweathermap.api_key');
        $this->openWeatherBaseUrl = config('services.openweathermap.base_url');
        $this->nasaPowerBaseUrl = config('services.nasa_power.base_url');
        $this->nasaPowerParams = config('services.nasa_power.parameters');
    }

    public function getCurrentWeather(float $lat, float $lon): ?array
    {
        $cacheKey = "weather_current_{$lat}_{$lon}";
        
        return Cache::remember($cacheKey, 3600, function () use ($lat, $lon) {
            return $this->fetchOpenWeatherMapCurrent($lat, $lon);
        });
    }

    public function getWeatherForecast(float $lat, float $lon, int $days = 7): ?array
    {
        $cacheKey = "weather_forecast_{$lat}_{$lon}_{$days}";
        
        return Cache::remember($cacheKey, 3600, function () use ($lat, $lon, $days) {
            return $this->fetchOpenWeatherMapForecast($lat, $lon, $days);
        });
    }

    public function getNasaPowerData(float $lat, float $lon, string $startDate, string $endDate): ?array
    {
        $cacheKey = "nasa_power_{$lat}_{$lon}_{$startDate}_{$endDate}";
        
        return Cache::remember($cacheKey, 3600, function () use ($lat, $lon, $startDate, $endDate) {
            return $this->fetchNasaPowerData($lat, $lon, $startDate, $endDate);
        });
    }

    private function fetchOpenWeatherMapCurrent(float $lat, float $lon): ?array
    {
        try {
            $response = $this->client->get($this->openWeatherBaseUrl . 'weather', [
                'query' => [
                    'lat' => $lat,
                    'lon' => $lon,
                    'appid' => $this->openWeatherApiKey,
                    'units' => 'metric'
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            
            return [
                'temperature' => $data['main']['temp'],
                'humidity' => $data['main']['humidity'],
                'pressure' => $data['main']['pressure'],
                'wind_speed' => $data['wind']['speed'] ?? 0,
                'weather_condition' => $data['weather'][0]['main'],
                'description' => $data['weather'][0]['description'],
                'raw_data' => $data,
                'recorded_at' => now(),
                'data_source' => 'openweathermap'
            ];
        } catch (GuzzleException $e) {
            Log::error('OpenWeatherMap API Error: ' . $e->getMessage());
            return null;
        }
    }

    private function fetchOpenWeatherMapForecast(float $lat, float $lon, int $days): ?array
    {
        try {
            $response = $this->client->get($this->openWeatherBaseUrl . 'forecast', [
                'query' => [
                    'lat' => $lat,
                    'lon' => $lon,
                    'appid' => $this->openWeatherApiKey,
                    'units' => 'metric',
                    'cnt' => $days * 8 // 8 forecasts per day (3-hour intervals)
                ]
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error('OpenWeatherMap Forecast API Error: ' . $e->getMessage());
            return null;
        }
    }

    private function fetchNasaPowerData(float $lat, float $lon, string $startDate, string $endDate): ?array
    {
        try {
            $response = $this->client->get($this->nasaPowerBaseUrl, [
                'query' => [
                    'parameters' => $this->nasaPowerParams,
                    'community' => 'RE',
                    'longitude' => $lon,
                    'latitude' => $lat,
                    'start' => $startDate,
                    'end' => $endDate,
                    'format' => 'JSON'
                ]
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error('NASA POWER API Error: ' . $e->getMessage());
            return null;
        }
    }

    public function storeWeatherData(array $weatherData, float $lat, float $lon, ?string $locationName = null): WeatherData
    {
        return WeatherData::create([
            'latitude' => $lat,
            'longitude' => $lon,
            'location_name' => $locationName,
            'temperature' => $weatherData['temperature'],
            'humidity' => $weatherData['humidity'],
            'precipitation' => $weatherData['precipitation'] ?? 0,
            'wind_speed' => $weatherData['wind_speed'],
            'pressure' => $weatherData['pressure'],
            'weather_condition' => $weatherData['weather_condition'],
            'description' => $weatherData['description'],
            'raw_data' => $weatherData['raw_data'],
            'data_source' => $weatherData['data_source'],
            'recorded_at' => $weatherData['recorded_at'],
        ]);
    }

    public function getHistoricalWeatherData(float $lat, float $lon, int $days = 30): array
    {
        return WeatherData::where('latitude', $lat)
            ->where('longitude', $lon)
            ->where('recorded_at', '>=', now()->subDays($days))
            ->orderBy('recorded_at', 'desc')
            ->get()
            ->toArray();
    }

    public function getRainfallTrend(float $lat, float $lon, int $days = 7): array
    {
        $data = WeatherData::where('latitude', $lat)
            ->where('longitude', $lon)
            ->where('recorded_at', '>=', now()->subDays($days))
            ->selectRaw('DATE(recorded_at) as date, SUM(precipitation) as total_rainfall')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return $data->map(function ($item) {
            return [
                'date' => $item->date,
                'rainfall' => $item->total_rainfall
            ];
        })->toArray();
    }
}