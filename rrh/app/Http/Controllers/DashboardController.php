<?php

namespace App\Http\Controllers;

use App\Models\WeatherData;
use App\Models\FloodRisk;
use App\Models\Report;
use App\Services\WeatherService;
use App\Services\FloodPredictionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class DashboardController extends Controller
{
    protected $weatherService;
    protected $floodPredictionService;

    public function __construct(WeatherService $weatherService, FloodPredictionService $floodPredictionService)
    {
        $this->weatherService = $weatherService;
        $this->floodPredictionService = $floodPredictionService;
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Get dashboard data with caching
        $dashboardData = Cache::remember('dashboard_data_' . $user->id, 300, function () use ($user) {
            return [
                'weather_summary' => $this->getWeatherSummary(),
                'flood_risks' => $this->getFloodRisks(),
                'recent_reports' => $this->getRecentReports($user),
                'statistics' => $this->getStatistics(),
            ];
        });

        return view('dashboard.index', $dashboardData);
    }

    public function admin()
    {
        $this->authorize('admin');

        $adminData = Cache::remember('admin_dashboard_data', 300, function () {
            return [
                'total_users' => \App\Models\User::count(),
                'active_sensors' => \App\Models\SensorData::distinct('sensor_id')->count(),
                'weather_data_points' => WeatherData::count(),
                'high_risk_areas' => FloodRisk::where('risk_level', 'high')->count(),
                'recent_weather_data' => WeatherData::with(['location_name'])
                    ->latest()
                    ->take(10)
                    ->get(),
                'system_health' => $this->getSystemHealth(),
            ];
        });

        return view('dashboard.admin', $adminData);
    }

    protected function getWeatherSummary()
    {
        return WeatherData::select('location_name', 'temperature', 'humidity', 'precipitation', 'created_at')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($data) {
                return [
                    'location_name' => $data->location,
                    'temperature' => $data->temperature,
                    'humidity' => $data->humidity,
                    'precipitation' => $data->precipitation,
                    'time' => $data->created_at->diffForHumans(),
                ];
            });
    }

    protected function getFloodRisks()
    {
        return FloodRisk::with(['weatherData'])
            ->where('created_at', '>=', Carbon::now()->subHours(24))
            ->orderBy('risk_score', 'desc')
            ->take(10)
            ->get()
            ->map(function ($risk) {
                return [
                    'location_name' => $risk->location,
                    'risk_level' => $risk->risk_level,
                    'risk_score' => $risk->risk_score,
                    'predicted_at' => $risk->created_at->format('M d, H:i'),
                    'factors' => json_decode($risk->contributing_factors, true),
                ];
            });
    }

    protected function getRecentReports($user)
    {
        $query = Report::with(['user']);

        if ($user->user_type !== 'admin') {
            $query->where('user_id', $user->id);
        }

        return $query->latest()
            ->take(5)
            ->get()
            ->map(function ($report) {
                return [
                    'id' => $report->id,
                    'title' => $report->title,
                    'type' => $report->report_type,
                    'status' => $report->status,
                    'created_at' => $report->created_at->diffForHumans(),
                    'user' => $report->user->name,
                ];
            });
    }

    protected function getStatistics()
    {
        return [
            'weather_stations' => WeatherData::distinct('location_name')->count(),
            'predictions_today' => FloodRisk::whereDate('created_at', Carbon::today())->count(),
            'high_risk_alerts' => FloodRisk::where('risk_level', 'high')
                ->where('created_at', '>=', Carbon::now()->subHours(24))
                ->count(),
            'data_accuracy' => $this->calculateDataAccuracy(),
        ];
    }

    protected function getSystemHealth()
    {
        $lastWeatherUpdate = WeatherData::latest()->first()?->created_at;
        $lastFloodPrediction = FloodRisk::latest()->first()?->created_at;
        $queueSize = \Illuminate\Support\Facades\DB::table('jobs')->count();

        return [
            'weather_api_status' => $lastWeatherUpdate && $lastWeatherUpdate->gt(Carbon::now()->subHours(2)) ? 'healthy' : 'warning',
            'prediction_service_status' => $lastFloodPrediction && $lastFloodPrediction->gt(Carbon::now()->subHours(6)) ? 'healthy' : 'warning',
            'queue_status' => $queueSize < 100 ? 'healthy' : 'overloaded',
            'last_weather_update' => $lastWeatherUpdate?->diffForHumans() ?? 'Never',
            'last_prediction' => $lastFloodPrediction?->diffForHumans() ?? 'Never',
            'pending_jobs' => $queueSize,
        ];
    }

    protected function calculateDataAccuracy()
    {
        // Simple accuracy calculation based on successful API calls vs failed ones
        $totalAttempts = Cache::get('api_total_attempts', 0);
        $successfulAttempts = Cache::get('api_successful_attempts', 0);

        if ($totalAttempts === 0) {
            return 100;
        }

        return round(($successfulAttempts / $totalAttempts) * 100, 1);
    }

    public function refreshData(Request $request)
    {
        $user = auth()->user();
        
        // Clear relevant caches
        Cache::forget('dashboard_data_' . $user->id);
        if ($user->user_type === 'admin') {
            Cache::forget('admin_dashboard_data');
        }

        // Dispatch jobs to fetch fresh data
        \App\Jobs\FetchWeatherDataJob::dispatch();
        \App\Jobs\ProcessFloodRiskJob::dispatch();

        return response()->json([
            'message' => 'Data refresh initiated',
            'status' => 'success'
        ]);
    }
}