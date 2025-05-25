<?php

namespace App\Http\Controllers;

use App\Models\WeatherData;
use App\Services\WeatherService;
use App\Http\Requests\WeatherDataRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class WeatherController extends Controller
{
    protected $weatherService;

    public function __construct(WeatherService $weatherService)
    {
        $this->weatherService = $weatherService;
    }

    public function index(Request $request)
    {
        $location = $request->get('location', 'Kigali');
        $period = $request->get('period', '24h');

        $weatherData = $this->getWeatherData($location, $period);
        $locations = $this->getAvailableLocations();

        return view('weather.index', compact('weatherData', 'locations', 'location', 'period'));
    }

    public function api(Request $request)
    {
        $location = $request->get('location', 'Kigali');
        $limit = min($request->get('limit', 24), 100);

        $data = WeatherData::where('location', $location)
            ->latest()
            ->take($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'timestamp' => $item->created_at->toISOString(),
                    'temperature' => $item->temperature,
                    'humidity' => $item->humidity,
                    'precipitation' => $item->precipitation,
                    'wind_speed' => $item->wind_speed,
                    'wind_direction' => $item->wind_direction,
                    'pressure' => $item->pressure,
                    'visibility' => $item->visibility,
                ];
            });

        return response()->json([
            'location' => $location,
            'data' => $data,
            'count' => $data->count(),
        ]);
    }

    public function current(Request $request)
    {
        $location = $request->get('location', 'Kigali');
        
        $currentWeather = Cache::remember("current_weather_{$location}", 600, function () use ($location) {
            return WeatherData::where('location', $location)
                ->latest()
                ->first();
        });

        if (!$currentWeather) {
            return response()->json([
                'error' => 'No weather data available for this location'
            ], 404);
        }

        return response()->json([
            'location' => $currentWeather->location,
            'temperature' => $currentWeather->temperature,
            'humidity' => $currentWeather->humidity,
            'precipitation' => $currentWeather->precipitation,
            'wind_speed' => $currentWeather->wind_speed,
            'wind_direction' => $currentWeather->wind_direction,
            'pressure' => $currentWeather->pressure,
            'visibility' => $currentWeather->visibility,
            'conditions' => $currentWeather->weather_conditions,
            'last_updated' => $currentWeather->created_at->toISOString(),
        ]);
    }

    public function forecast(Request $request)
    {
        $location = $request->get('location', 'Kigali');
        
        try {
            $forecast = $this->weatherService->getForecast($location);
            
            return response()->json([
                'location' => $location,
                'forecast' => $forecast,
                'generated_at' => now()->toISOString(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch forecast data',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function historical(Request $request)
    {
        $request->validate([
            'location' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        $location = $request->get('location');
        $startDate = Carbon::parse($request->get('start_date'));
        $endDate = Carbon::parse($request->get('end_date'));

        // Limit to 30 days max
        if ($startDate->diffInDays($endDate) > 30) {
            return response()->json([
                'error' => 'Date range cannot exceed 30 days'
            ], 400);
        }

        $historicalData = WeatherData::where('location', $location)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->created_at->toDateString(),
                    'time' => $item->created_at->toTimeString(),
                    'temperature' => $item->temperature,
                    'humidity' => $item->humidity,
                    'precipitation' => $item->precipitation,
                    'wind_speed' => $item->wind_speed,
                    'pressure' => $item->pressure,
                ];
            });

        return response()->json([
            'location' => $location,
            'period' => [
                'start' => $startDate->toDateString(),
                'end' => $endDate->toDateString(),
            ],
            'data' => $historicalData,
            'summary' => $this->calculateHistoricalSummary($historicalData),
        ]);
    }

    public function store(WeatherDataRequest $request)
    {
        $this->authorize('admin');

        $weatherData = WeatherData::create($request->validated());

        // Clear relevant caches
        $this->clearWeatherCaches($weatherData->location);

        return response()->json([
            'message' => 'Weather data stored successfully',
            'data' => $weatherData
        ], 201);
    }

    public function refresh(Request $request)
    {
        $location = $request->get('location', 'Kigali');

        try {
            // Dispatch job to fetch fresh weather data
            \App\Jobs\FetchWeatherDataJob::dispatch($location);

            return response()->json([
                'message' => 'Weather data refresh initiated',
                'location' => $location,
                'status' => 'queued'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to initiate weather data refresh',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    protected function getWeatherData($location, $period)
    {
        $cacheKey = "weather_data_{$location}_{$period}";
        
        return Cache::remember($cacheKey, 300, function () use ($location, $period) {
            $query = WeatherData::where('location', $location);

            switch ($period) {
                case '6h':
                    $query->where('created_at', '>=', Carbon::now()->subHours(6));
                    break;
                case '24h':
                    $query->where('created_at', '>=', Carbon::now()->subDay());
                    break;
                case '7d':
                    $query->where('created_at', '>=', Carbon::now()->subWeek());
                    break;
                case '30d':
                    $query->where('created_at', '>=', Carbon::now()->subMonth());
                    break;
            }

            return $query->orderBy('created_at', 'desc')->get();
        });
    }

    protected function getAvailableLocations()
    {
        return Cache::remember('weather_locations', 3600, function () {
            return WeatherData::distinct('location')
                ->pluck('location')
                ->sort()
                ->values();
        });
    }

    protected function calculateHistoricalSummary($data)
    {
        if ($data->isEmpty()) {
            return null;
        }

        $temperatures = $data->pluck('temperature')->filter();
        $humidity = $data->pluck('humidity')->filter();
        $precipitation = $data->pluck('precipitation')->filter();

        return [
            'temperature' => [
                'avg' => round($temperatures->avg(), 1),
                'min' => $temperatures->min(),
                'max' => $temperatures->max(),
            ],
            'humidity' => [
                'avg' => round($humidity->avg(), 1),
                'min' => $humidity->min(),
                'max' => $humidity->max(),
            ],
            'total_precipitation' => round($precipitation->sum(), 2),
            'data_points' => $data->count(),
        ];
    }

    protected function clearWeatherCaches($location)
    {
        $periods = ['6h', '24h', '7d', '30d'];
        foreach ($periods as $period) {
            Cache::forget("weather_data_{$location}_{$period}");
        }
        Cache::forget("current_weather_{$location}");
        Cache::forget('weather_locations');
    }
}