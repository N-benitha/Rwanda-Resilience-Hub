<?php

namespace App\Services;

use App\Models\Report;
use App\Models\WeatherData;
use App\Models\FloodRisk;
use App\Models\SensorData;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ReportService
{
    private Client $client;
    private string $groqApiKey;
    private string $groqBaseUrl;
    private string $groqModel;

    public function __construct()
    {
        $this->client = new Client(['timeout' => 120]);
        $this->groqApiKey = config('services.groq.api_key');
        $this->groqBaseUrl = config('services.groq.base_url');
        $this->groqModel = config('services.groq.model');
    }

    public function generateFloodRiskReport(int $userId, array $parameters = []): Report
    {
        $periodStart = Carbon::parse($parameters['period_start'] ?? now()->subDays(7));
        $periodEnd = Carbon::parse($parameters['period_end'] ?? now());
        $location = $parameters['location_name'] ?? null;

        // Gather data
        $floodRisks = $this->getFloodRiskData($periodStart, $periodEnd, $location);
        $weatherData = $this->getWeatherDataForReport($periodStart, $periodEnd, $location);
        $sensorData = $this->getSensorDataForReport($periodStart, $periodEnd, $location);

        // Generate AI content
        $aiContent = $this->generateAIReport('flood_risk', [
            'flood_risks' => $floodRisks,
            'weather_data' => $weatherData,
            'sensor_data' => $sensorData,
            'period_start' => $periodStart,
            'period_end' => $periodEnd
        ]);

        // Create charts configuration
        $chartsConfig = $this->generateChartsConfig('flood_risk', $floodRisks, $weatherData);

        return Report::create([
            'user_id' => $userId,
            'title' => "Flood Risk Analysis Report - {$periodStart->format('M d')} to {$periodEnd->format('M d, Y')}",
            'type' => 'flood_risk',
            'summary' => $aiContent['summary'] ?? 'Comprehensive flood risk analysis for the specified period.',
            'content' => $aiContent['content'] ?? 'Report content could not be generated.',
            'data_sources' => [
                'flood_risks_count' => count($floodRisks),
                'weather_records_count' => count($weatherData),
                'sensor_records_count' => count($sensorData)
            ],
            'charts_config' => $chartsConfig,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'status' => 'generated'
        ]);
    }

    public function generateWeatherAnalysisReport(int $userId, array $parameters = []): Report
    {
        $periodStart = Carbon::parse($parameters['period_start'] ?? now()->subDays(30));
        $periodEnd = Carbon::parse($parameters['period_end'] ?? now());
        $location = $parameters['location_name'] ?? null;

        $weatherData = $this->getWeatherDataForReport($periodStart, $periodEnd, $location);
        $trends = $this->calculateWeatherTrends($weatherData);

        $aiContent = $this->generateAIReport('weather_analysis', [
            'weather_data' => $weatherData,
            'trends' => $trends,
            'period_start' => $periodStart,
            'period_end' => $periodEnd
        ]);

        $chartsConfig = $this->generateChartsConfig('weather_analysis', $weatherData, $trends);

        return Report::create([
            'user_id' => $userId,
            'title' => "Weather Analysis Report - {$periodStart->format('M d')} to {$periodEnd->format('M d, Y')}",
            'type' => 'weather_analysis',
            'summary' => $aiContent['summary'] ?? 'Detailed weather pattern analysis for the specified period.',
            'content' => $aiContent['content'] ?? 'Weather analysis could not be generated.',
            'data_sources' => [
                'weather_records_count' => count($weatherData),
                'data_sources' => array_unique(array_column($weatherData, 'data_source'))
            ],
            'charts_config' => $chartsConfig,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'status' => 'generated'
        ]);
    }

    public function generateSensorSummaryReport(int $userId, array $parameters = []): Report
    {
        $periodStart = Carbon::parse($parameters['period_start'] ?? now()->subDays(7));
        $periodEnd = Carbon::parse($parameters['period_end'] ?? now());

        $sensorData = $this->getSensorDataForReport($periodStart, $periodEnd);
        $summary = $this->calculateSensorSummary($sensorData);

        $aiContent = $this->generateAIReport('sensor_summary', [
            'sensor_data' => $sensorData,
            'summary' => $summary,
            'period_start' => $periodStart,
            'period_end' => $periodEnd
        ]);

        $chartsConfig = $this->generateChartsConfig('sensor_summary', $sensorData, $summary);

        return Report::create([
            'user_id' => $userId,
            'title' => "Sensor Data Summary - {$periodStart->format('M d')} to {$periodEnd->format('M d, Y')}",
            'type' => 'sensor_summary',
            'summary' => $aiContent['summary'] ?? 'Summary of sensor data collected during the period.',
            'content' => $aiContent['content'] ?? 'Sensor summary could not be generated.',
            'data_sources' => [
                'total_submissions' => count($sensorData),
                'unique_users' => count(array_unique(array_column($sensorData, 'user_id'))),
                'validated_records' => count(array_filter($sensorData, fn($item) => $item['is_validated']))
            ],
            'charts_config' => $chartsConfig,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'status' => 'generated'
        ]);
    }

    private function generateAIReport(string $reportType, array $data): array
    {
        if (!$this->groqApiKey) {
            return [
                'summary' => 'AI analysis unavailable - API key not configured.',
                'content' => 'Report generated without AI analysis.'
            ];
        }

        $cacheKey = 'ai_report_' . $reportType . '_' . md5(json_encode($data));
        
        return Cache::remember($cacheKey, 3600, function () use ($reportType, $data) {
            try {
                $prompt = $this->buildReportPrompt($reportType, $data);
                
                $response = $this->client->post($this->groqBaseUrl . '/chat/completions', [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->groqApiKey,
                        'Content-Type' => 'application/json',
                    ],
                    'json' => [
                        'model' => $this->groqModel,
                        'messages' => [
                            ['role' => 'system', 'content' => 'You are a meteorological and flood risk expert specializing in Rwanda. Generate professional reports with clear insights and recommendations.'],
                            ['role' => 'user', 'content' => $prompt]
                        ],
                        'max_tokens' => 2000,
                        'temperature' => 0.4
                    ]
                ]);

                $result = json_decode($response->getBody()->getContents(), true);
                $content = $result['choices'][0]['message']['content'] ?? '';
                
                // Try to parse as JSON, fallback to plain text
                $parsed = json_decode($content, true);
                if ($parsed && isset($parsed['summary']) && isset($parsed['content'])) {
                    return $parsed;
                }
                
                // Fallback parsing
                return $this->parseReportContent($content);
                
            } catch (GuzzleException $e) {
                Log::error('Groq AI Report Generation Error: ' . $e->getMessage());
                return [
                    'summary' => 'AI analysis temporarily unavailable.',
                    'content' => 'Report generated with available data analysis.'
                ];
            }
        });
    }

    private function buildReportPrompt(string $reportType, array $data): string
    {
        $basePrompt = "Generate a professional report for Rwanda's flood resilience system.\n\n";
        
        switch ($reportType) {
            case 'flood_risk':
                return $basePrompt . "FLOOD RISK ANALYSIS REPORT

Data Summary:
- Period: {$data['period_start']->format('Y-m-d')} to {$data['period_end']->format('Y-m-d')}
- Flood Risk Records: " . count($data['flood_risks']) . "
- Weather Records: " . count($data['weather_data']) . "
- Sensor Records: " . count($data['sensor_data']) . "

Generate a JSON response with:
{
  \"summary\": \"Executive summary (2-3 sentences)\",
  \"content\": \"## Key Findings\\n\\n[detailed analysis]\\n\\n## Risk Assessment\\n\\n[risk analysis]\\n\\n## Recommendations\\n\\n[actionable recommendations]\"
}";

            case 'weather_analysis':
                return $basePrompt . "WEATHER ANALYSIS REPORT

Data Summary:
- Period: {$data['period_start']->format('Y-m-d')} to {$data['period_end']->format('Y-m-d')}
- Weather Records: " . count($data['weather_data']) . "
- Trend Analysis Available: " . (isset($data['trends']) ? 'Yes' : 'No') . "

Generate a JSON response with:
{
  \"summary\": \"Weather pattern summary (2-3 sentences)\",
  \"content\": \"## Weather Patterns\\n\\n[pattern analysis]\\n\\n## Precipitation Trends\\n\\n[rainfall analysis]\\n\\n## Climate Insights\\n\\n[climate observations]\"
}";

            case 'sensor_summary':
                return $basePrompt . "SENSOR DATA SUMMARY REPORT

Data Summary:
- Period: {$data['period_start']->format('Y-m-d')} to {$data['period_end']->format('Y-m-d')}
- Total Sensor Records: " . count($data['sensor_data']) . "
- Validation Status: Available

Generate a JSON response with:
{
  \"summary\": \"Sensor data collection summary (2-3 sentences)\",
  \"content\": \"## Data Collection Overview\\n\\n[collection summary]\\n\\n## Data Quality Assessment\\n\\n[quality analysis]\\n\\n## Community Engagement\\n\\n[participation insights]\"
}";

            default:
                return $basePrompt . "Generate a general report analysis.";
        }
    }

    private function parseReportContent(string $content): array
    {
        $lines = explode("\n", $content);
        $summary = '';
        $reportContent = $content;
        
        // Try to extract summary from first paragraph
        foreach ($lines as $line) {
            $line = trim($line);
            if (!empty($line) && !str_starts_with($line, '#') && strlen($line) > 50) {
                $summary = substr($line, 0, 200) . (strlen($line) > 200 ? '...' : '');
                break;
            }
        }
        
        return [
            'summary' => $summary ?: 'Report analysis completed.',
            'content' => $reportContent
        ];
    }

    private function getFloodRiskData(Carbon $start, Carbon $end, ?array $location = null): array
    {
        $query = FloodRisk::whereBetween('prediction_date', [$start, $end]);
        
        if ($location && isset($location['lat'], $location['lon'])) {
            $query->where('latitude', '>=', $location['lat'] - 0.1)
                  ->where('latitude', '<=', $location['lat'] + 0.1)
                  ->where('longitude', '>=', $location['lon'] - 0.1)
                  ->where('longitude', '<=', $location['lon'] + 0.1);
        }
        
        return $query->orderBy('prediction_date')->get()->toArray();
    }

    private function getWeatherDataForReport(Carbon $start, Carbon $end, ?array $location = null): array
    {
        $query = WeatherData::whereBetween('recorded_at', [$start, $end]);
        
        if ($location && isset($location['lat'], $location['lon'])) {
            $query->where('latitude', '>=', $location['lat'] - 0.1)
                  ->where('latitude', '<=', $location['lat'] + 0.1)
                  ->where('longitude', '>=', $location['lon'] - 0.1)
                  ->where('longitude', '<=', $location['lon'] + 0.1);
        }
        
        return $query->orderBy('recorded_at')->get()->toArray();
    }

    private function getSensorDataForReport(Carbon $start, Carbon $end, ?array $location = null): array
    {
        $query = SensorData::whereBetween('created_at', [$start, $end]);
        
        if ($location && isset($location['lat'], $location['lon'])) {
            $query->where('latitude', '>=', $location['lat'] - 0.1)
                  ->where('latitude', '<=', $location['lat'] + 0.1)
                  ->where('longitude', '>=', $location['lon'] - 0.1)
                  ->where('longitude', '<=', $location['lon'] + 0.1);
        }
        
        return $query->orderBy('created_at')->get()->toArray();
    }

    private function calculateWeatherTrends(array $weatherData): array
    {
        if (empty($weatherData)) return [];
        
        $grouped = collect($weatherData)->groupBy(function ($item) {
            return Carbon::parse($item['recorded_at'])->format('Y-m-d');
        });
        
        return $grouped->map(function ($dayData) {
            return [
                'avg_temperature' => $dayData->avg('temperature'),
                'avg_humidity' => $dayData->avg('humidity'),
                'total_precipitation' => $dayData->sum('precipitation'),
                'avg_wind_speed' => $dayData->avg('wind_speed'),
                'avg_pressure' => $dayData->avg('pressure'),
            ];
        })->toArray();
    }

    private function calculateSensorSummary(array $sensorData): array
    {
        if (empty($sensorData)) return [];
        
        $collection = collect($sensorData);
        
        return [
            'total_records' => $collection->count(),
            'validated_records' => $collection->where('is_validated', true)->count(),
            'unique_locations' => $collection->unique(function ($item) {
                return round($item['latitude'], 3) . ',' . round($item['longitude'], 3);
            })->count(),
            'avg_rainfall' => $collection->whereNotNull('rainfall')->avg('rainfall'),
            'avg_temperature' => $collection->whereNotNull('temperature')->avg('temperature'),
            'avg_humidity' => $collection->whereNotNull('humidity')->avg('humidity'),
        ];
    }

    private function generateChartsConfig(string $reportType, array $primaryData, array $secondaryData = []): array
    {
        switch ($reportType) {
            case 'flood_risk':
                return [
                    'risk_levels' => [
                        'type' => 'pie',
                        'title' => 'Flood Risk Distribution',
                        'data' => $this->getRiskLevelDistribution($primaryData)
                    ],
                    'risk_timeline' => [
                        'type' => 'line',
                        'title' => 'Risk Percentage Over Time',
                        'data' => $this->getRiskTimeline($primaryData)
                    ]
                ];
                
            case 'weather_analysis':
                return [
                    'temperature_trend' => [
                        'type' => 'line',
                        'title' => 'Temperature Trend',
                        'data' => $this->getTemperatureTrend($primaryData)
                    ],
                    'precipitation_bars' => [
                        'type' => 'bar',
                        'title' => 'Daily Precipitation',
                        'data' => $this->getPrecipitationBars($primaryData)
                    ]
                ];
                
            case 'sensor_summary':
                return [
                    'data_sources' => [
                        'type' => 'pie',
                        'title' => 'Data Collection Sources',
                        'data' => $this->getDataSourceDistribution($primaryData)
                    ],
                    'validation_status' => [
                        'type' => 'doughnut',
                        'title' => 'Data Validation Status',
                        'data' => $this->getValidationDistribution($primaryData)
                    ]
                ];
                
            default:
                return [];
        }
    }

    private function getRiskLevelDistribution(array $floodRisks): array
    {
        $distribution = collect($floodRisks)->countBy('risk_level');
        return $distribution->map(fn($count, $level) => ['label' => ucfirst($level), 'value' => $count])->values()->toArray();
    }

    private function getRiskTimeline(array $floodRisks): array
    {
        return collect($floodRisks)->map(function ($risk) {
            return [
                'date' => Carbon::parse($risk['prediction_date'])->format('Y-m-d'),
                'risk_percentage' => $risk['risk_percentage']
            ];
        })->toArray();
    }

    private function getTemperatureTrend(array $weatherData): array
    {
        return collect($weatherData)->map(function ($data) {
            return [
                'date' => Carbon::parse($data['recorded_at'])->format('Y-m-d H:i'),
                'temperature' => $data['temperature']
            ];
        })->toArray();
    }

    private function getPrecipitationBars(array $weatherData): array
    {
        $grouped = collect($weatherData)->groupBy(function ($item) {
            return Carbon::parse($item['recorded_at'])->format('Y-m-d');
        });
        
        return $grouped->map(function ($dayData, $date) {
            return [
                'date' => $date,
                'precipitation' => $dayData->sum('precipitation')
            ];
        })->values()->toArray();
    }

    private function getDataSourceDistribution(array $sensorData): array
    {
        $userCount = collect($sensorData)->countBy('user_id');
        return [
            ['label' => 'Community Reports', 'value' => $userCount->count()],
            ['label' => 'Validated Data', 'value' => collect($sensorData)->where('is_validated', true)->count()]
        ];
    }

    private function getValidationDistribution(array $sensorData): array
    {
        $collection = collect($sensorData);
        return [
            ['label' => 'Validated', 'value' => $collection->where('is_validated', true)->count()],
            ['label' => 'Pending', 'value' => $collection->where('is_validated', false)->count()]
        ];
    }

    public function exportReportToPDF(int $reportId): ?string
    {
        // Implementation for PDF export will go here
        return null;
    }
}