<?php

namespace App\Jobs;

use App\Models\WeatherData;
use App\Models\FloodRisk;
use App\Services\FloodPredictionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ProcessFloodRiskJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300;
    public $tries = 3;

    protected $location;
    protected $timeframe;

    public function __construct(string $location, int $timeframe = 7)
    {
        $this->location = $location;
        $this->timeframe = $timeframe;
    }

    public function handle(FloodPredictionService $floodService)
    {
        try {
            Log::info("Processing flood risk for location: {$this->location}");
            
            // Get recent weather data for the location
            $weatherData = WeatherData::where('location_name', $this->location)
                ->where('created_at', '>=', Carbon::now()->subDays($this->timeframe))
                ->orderBy('created_at', 'desc')
                ->get();

            if ($weatherData->isEmpty()) {
                Log::warning("No weather data found for location: {$this->location}");
                return;
            }

            // Calculate flood risk using the service
            $riskData = $floodService->calculateFloodRisk($weatherData);

            // Store or update flood risk data
            FloodRisk::updateOrCreate(
                [
                    'location_name' => $this->location,
                    'date' => Carbon::now()->format('Y-m-d')
                ],
                [
                    'risk_level' => $riskData['risk_level'],
                    'probability' => $riskData['probability'],
                    'severity' => $riskData['severity'],
                    'factors' => json_encode($riskData['factors']),
                    'recommendations' => json_encode($riskData['recommendations']),
                    'precipitation_7day' => $riskData['precipitation_7day'],
                    'temperature_avg' => $riskData['temperature_avg'],
                    'humidity_avg' => $riskData['humidity_avg'],
                    'calculated_at' => Carbon::now()
                ]
            );

            Log::info("Flood risk processed successfully for {$this->location}. Risk level: {$riskData['risk_level']}");
            
        } catch (\Exception $e) {
            Log::error("Failed to process flood risk for {$this->location}: " . $e->getMessage());
            throw $e;
        }
    }

    public function failed(\Throwable $exception)
    {
        Log::error("ProcessFloodRiskJob failed for location {$this->location}: " . $exception->getMessage());
    }
}