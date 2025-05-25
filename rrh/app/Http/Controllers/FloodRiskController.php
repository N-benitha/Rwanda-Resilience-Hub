<?php

namespace App\Http\Controllers;

use App\Models\FloodRisk;
use App\Models\WeatherData;
use App\Services\FloodPredictionService;
use App\Http\Requests\FloodRiskRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class FloodRiskController extends Controller
{
    protected $floodPredictionService;

    public function __construct(FloodPredictionService $floodPredictionService)
    {
        $this->floodPredictionService = $floodPredictionService;
    }

    public function index(Request $request)
    {
        $location = $request->get('location');
        $riskLevel = $request->get('risk_level');
        $period = $request->get('period', '24h');

        $floodRisks = $this->getFloodRisks($location, $riskLevel, $period);
        $locations = $this->getAvailableLocations();
        $statistics = $this->getRiskStatistics($period);

        return view('flood-risks.index', compact(
            'floodRisks', 
            'locations', 
            'statistics', 
            'location', 
            'riskLevel', 
            'period'
        ));
    }

    public function show(FloodRisk $floodRisk)
    {
        $floodRisk->load(['weatherData']);
        
        $relatedRisks = FloodRisk::where('location', $floodRisk->location)
            ->where('id', '!=', $floodRisk->id)
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->latest()
            ->take(5)
            ->get();

        return view('flood-risks.show', compact('floodRisk', 'relatedRisks'));
    }

    public function api(Request $request)
    {
        $location = $request->get('location');
        $limit = min($request->get('limit', 50), 100);

        $query = FloodRisk::with(['weatherData']);

        if ($location) {
            $query->where('location', $location);
        }

        $risks = $query->latest()
            ->take($limit)
            ->get()
            ->map(function ($risk) {
                return [
                    'id' => $risk->id,
                    'location' => $risk->location,
                    'risk_level' => $risk->risk_level,
                    'risk_score' => $risk->risk_score,
                    'prediction_confidence' => $risk->prediction_confidence,
                    'contributing_factors' => json_decode($risk->contributing_factors, true),
                    'predicted_at' => $risk->created_at->toISOString(),
                    'weather_data' => $risk->weatherData ? [
                        'temperature' => $risk->weatherData->temperature,
                        'precipitation' => $risk->weatherData->precipitation,
                        'humidity' => $risk->weatherData->humidity,
                    ] : null,
                ];
            });

        return response()->json([
            'risks' => $risks,
            'count' => $risks->count(),
            'filters' => [
                'location' => $location,
                'limit' => $limit,
            ],
        ]);
    }

    public function current(Request $request)
    {
        $location = $request->get('location');

        if (!$location) {
            return response()->json([
                'error' => 'Location parameter is required'
            ], 400);
        }

        $currentRisk = Cache::remember("current_flood_risk_{$location}", 1800, function () use ($location) {
            return FloodRisk::where('location', $location)
                ->latest()
                ->first();
        });

        if (!$currentRisk) {
            return response()->json([
                'error' => 'No flood risk data available for this location'
            ], 404);
        }

        return response()->json([
            'location' => $currentRisk->location,
            'risk_level' => $currentRisk->risk_level,
            'risk_score' => $currentRisk->risk_score,
            'prediction_confidence' => $currentRisk->prediction_confidence,
            'contributing_factors' => json_decode($currentRisk->contributing_factors, true),
            'recommendations' => json_decode($currentRisk->recommendations, true),
            'predicted_at' => $currentRisk->created_at->toISOString(),
            'expires_at' => $currentRisk->created_at->addHours(6)->toISOString(),
        ]);
    }

    public function predict(FloodRiskRequest $request)
    {
        $location = $request->validated('location');
        
        try {
            $prediction = $this->floodPredictionService->predictFloodRisk($location);
            
            // Store the prediction
            $floodRisk = FloodRisk::create([
                'location' => $location,
                'risk_level' => $prediction['risk_level'],
                'risk_score' => $prediction['risk_score'],
                'prediction_confidence' => $prediction['confidence'],
                'contributing_factors' => json_encode($prediction['factors']),
                'recommendations' => json_encode($prediction['recommendations']),
                'weather_data_id' => $prediction['weather_data_id'] ?? null,
            ]);

            // Clear relevant caches
            $this->clearFloodRiskCaches($location);

            return response()->json([
                'message' => 'Flood risk prediction generated successfully',
                'prediction' => [
                    'id' => $floodRisk->id,
                    'location' => $floodRisk->location,
                    'risk_level' => $floodRisk->risk_level,
                    'risk_score' => $floodRisk->risk_score,
                    'confidence' => $floodRisk->prediction_confidence,
                    'factors' => json_decode($floodRisk->contributing_factors, true),
                    'recommendations' => json_decode($floodRisk->recommendations, true),
                    'predicted_at' => $floodRisk->created_at->toISOString(),
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to generate flood risk prediction',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function alerts(Request $request)
    {
        $location = $request->get('location');
        $minRiskLevel = $request->get('min_risk_level', 'medium');

        $riskLevels = ['low', 'medium', 'high', 'critical'];
        $minIndex = array_search($minRiskLevel, $riskLevels);

        if ($minIndex === false) {
            return response()->json([
                'error' => 'Invalid risk level. Must be one of: low, medium, high, critical'
            ], 400);
        }

        $acceptableRiskLevels = array_slice($riskLevels, $minIndex);

        $query = FloodRisk::whereIn('risk_level', $acceptableRiskLevels)
            ->where('created_at', '>=', Carbon::now()->subHours(24));

        if ($location) {
            $query->where('location', $location);
        }

        $alerts = $query->latest()
            ->get()
            ->map(function ($risk) {
                return [
                    'id' => $risk->id,
                    'location' => $risk->location,
                    'risk_level' => $risk->risk_level,
                    'risk_score' => $risk->risk_score,
                    'message' => $this->generateAlertMessage($risk),
                    'urgency' => $this->getUrgencyLevel($risk->risk_level),
                    'issued_at' => $risk->created_at->toISOString(),
                    'expires_at' => $risk->created_at->addHours(6)->toISOString(),
                ];
            });

        return response()->json([
            'alerts' => $alerts,
            'count' => $alerts->count(),
            'filters' => [
                'location' => $location,
                'min_risk_level' => $minRiskLevel,
            ],
        ]);
    }

    public function analytics(Request $request)
    {
        $location = $request->get('location');
        $days = min($request->get('days', 7), 30);

        $startDate = Carbon::now()->subDays($days);

        $query = FloodRisk::where('created_at', '>=', $startDate);

        if ($location) {
            $query->where('location', $location);
        }

        $risks = $query->get();

        $analytics = [
            'total_predictions' => $risks->count(),
            'risk_distribution' => $risks->groupBy('risk_level')->map->count(),
            'average_risk_score' => round($risks->avg('risk_score'), 2),
            'trends' => $this->calculateRiskTrends($risks, $days),
            'most_at_risk_locations' => $this->getMostAtRiskLocations($startDate),
            'prediction_accuracy' => $this->calculatePredictionAccuracy($risks),
        ];

        return response()->json($analytics);
    }

    protected function getFloodRisks($location, $riskLevel, $period)
    {
        $cacheKey = "flood_risks_{$location}_{$riskLevel}_{$period}";
        
        return Cache::remember($cacheKey, 600, function () use ($location, $riskLevel, $period) {
            $query = FloodRisk::with(['weatherData']);

            if ($location) {
                $query->where('location', $location);
            }

            if ($riskLevel) {
                $query->where('risk_level', $riskLevel);
            }

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

            return $query->orderBy('created_at', 'desc')->paginate(20);
        });
    }

    protected function getAvailableLocations()
    {
        return Cache::remember('flood_risk_locations', 3600, function () {
            return FloodRisk::distinct('location')
                ->pluck('location')
                ->sort()
                ->values();
        });
    }

    protected function getRiskStatistics($period)
    {
        $cacheKey = "risk_statistics_{$period}";
        
        return Cache::remember($cacheKey, 900, function () use ($period) {
            $startDate = match ($period) {
                '6h' => Carbon::now()->subHours(6),
                '24h' => Carbon::now()->subDay(),
                '7d' => Carbon::now()->subWeek(),
                '30d' => Carbon::now()->subMonth(),
                default => Carbon::now()->subDay(),
            };

            $risks = FloodRisk::where('created_at', '>=', $startDate)->get();

            return [
                'total' => $risks->count(),
                'by_level' => $risks->groupBy('risk_level')->map->count(),
                'average_score' => round($risks->avg('risk_score'), 2),
                'highest_risk' => $risks->max('risk_score'),
                'locations_count' => $risks->pluck('location')->unique()->count(),
            ];
        });
    }

    protected function generateAlertMessage($risk)
    {
        $level = ucfirst($risk->risk_level);
        return "{$level} flood risk detected in {$risk->location}. Risk score: {$risk->risk_score}/100. Please take appropriate precautions.";
    }

    protected function getUrgencyLevel($riskLevel)
    {
        return match ($riskLevel) {
            'critical' => 'immediate',
            'high' => 'urgent',
            'medium' => 'moderate',
            'low' => 'low',
            default => 'low',
        };
    }

    protected function calculateRiskTrends($risks, $days)
    {
        $dailyRisks = $risks->groupBy(function ($risk) {
            return $risk->created_at->toDateString();
        });

        $trends = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->toDateString();
            $dayRisks = $dailyRisks->get($date, collect());
            
            $trends[] = [
                'date' => $date,
                'count' => $dayRisks->count(),
                'avg_score' => $dayRisks->avg('risk_score') ?? 0,
                'high_risk_count' => $dayRisks->whereIn('risk_level', ['high', 'critical'])->count(),
            ];
        }

        return $trends;
    }

    protected function getMostAtRiskLocations($startDate)
    {
        return FloodRisk::where('created_at', '>=', $startDate)
            ->select('location')
            ->selectRaw('AVG(risk_score) as avg_risk_score')
            ->selectRaw('COUNT(*) as prediction_count')
            ->groupBy('location')
            ->orderBy('avg_risk_score', 'desc')
            ->take(10)
            ->get()
            ->map(function ($item) {
                return [
                    'location' => $item->location,
                    'avg_risk_score' => round($item->avg_risk_score, 2),
                    'prediction_count' => $item->prediction_count,
                ];
            });
    }

    protected function calculatePredictionAccuracy($risks)
    {
        // This is a simplified accuracy calculation
        // In a real system, you'd compare predictions with actual flood events
        $totalPredictions = $risks->count();
        if ($totalPredictions === 0) {
            return 0;
        }

        // Assume higher confidence predictions are more accurate
        $weightedAccuracy = $risks->avg('prediction_confidence');
        
        return round($weightedAccuracy, 1);
    }

    protected function clearFloodRiskCaches($location)
    {
        $periods = ['6h', '24h', '7d', '30d'];
        $riskLevels = ['low', 'medium', 'high', 'critical'];
        
        foreach ($periods as $period) {
            Cache::forget("flood_risks_{$location}__{$period}");
            Cache::forget("risk_statistics_{$period}");
            
            foreach ($riskLevels as $level) {
                Cache::forget("flood_risks_{$location}_{$level}_{$period}");
            }
        }
        
        Cache::forget("current_flood_risk_{$location}");
        Cache::forget('flood_risk_locations');
    }
}