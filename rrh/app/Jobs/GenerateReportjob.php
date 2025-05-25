<?php

namespace App\Jobs;

use App\Models\Report;
use App\Services\ReportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class GenerateReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600;
    public $tries = 3;

    protected $reportType;
    protected $parameters;
    protected $userId;

    public function __construct(string $reportType, array $parameters = [], ?int $userId = null)
    {
        $this->reportType = $reportType;
        $this->parameters = $parameters;
        $this->userId = $userId;
    }

    public function handle(ReportService $reportService)
    {
        try {
            Log::info("Generating report of type: {$this->reportType}");
            
            // Create report record
            $report = Report::create([
                'type' => $this->reportType,
                'parameters' => json_encode($this->parameters),
                'user_id' => $this->userId,
                'status' => 'processing',
                'generated_at' => Carbon::now()
            ]);

            // Generate report data based on type
            $reportData = match ($this->reportType) {
                'weekly_flood_risk' => $reportService->generateWeeklyFloodRiskReport($this->parameters),
                'monthly_weather_summary' => $reportService->generateMonthlyWeatherReport($this->parameters),
                'location_risk_assessment' => $reportService->generateLocationRiskAssessment($this->parameters),
                'comparative_analysis' => $reportService->generateComparativeAnalysis($this->parameters),
                default => throw new \InvalidArgumentException("Unknown report type: {$this->reportType}")
            };

            // Update report with generated data
            $report->update([
                'data' => json_encode($reportData),
                'status' => 'completed',
                'file_path' => $reportData['file_path'] ?? null,
                'summary' => $reportData['summary'] ?? null
            ]);

            Log::info("Report generated successfully. ID: {$report->id}");
            
        } catch (\Exception $e) {
            if (isset($report)) {
                $report->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage()
                ]);
            }
            
            Log::error("Failed to generate report of type {$this->reportType}: " . $e->getMessage());
            throw $e;
        }
    }

    public function failed(\Throwable $exception)
    {
        Log::error("GenerateReportJob failed for type {$this->reportType}: " . $exception->getMessage());
        
        if ($reportId = $this->getReportId()) {
            Report::where('id', $reportId)->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage()
            ]);
        }
    }

    private function getReportId(): ?int
    {
        return Report::where('type', $this->reportType)
            ->where('user_id', $this->userId)
            ->where('status', 'processing')
            ->latest()
            ->value('id');
    }
}