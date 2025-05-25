<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class ReportController extends Controller
{
    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $reportType = $request->get('type');
        $status = $request->get('status');
        $period = $request->get('period', '30d');

        $query = Report::with(['user']);

        if ($user->user_type !== 'admin') {
            $query->where('user_id', $user->id);
        }

        if ($reportType) {
            $query->where('report_type', $reportType);
        }

        if ($status) {
            $query->where('status', $status);
        }

        switch ($period) {
            case '7d':
                $query->where('created_at', '>=', Carbon::now()->subWeek());
                break;
            case '30d':
                $query->where('created_at', '>=', Carbon::now()->subMonth());
                break;
            case '90d':
                $query->where('created_at', '>=', Carbon::now()->subMonths(3));
                break;
        }

        $reports = $query->latest()->paginate(15);
        $statistics = $this->getReportStatistics($user);

        return view('reports.index', compact('reports', 'statistics', 'reportType', 'status', 'period'));
    }

    public function show(Report $report)
    {
        $this->authorize('view', $report);
        
        $report->load(['user']);
        
        return view('reports.show', compact('report'));
    }

    public function create(Request $request)
    {
        $reportType = $request->get('type', 'weather');
        $availableTypes = $this->getAvailableReportTypes();

        return view('reports.create', compact('reportType', 'availableTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'report_type' => 'required|in:weather,flood_risk,environmental,custom',
            'description' => 'nullable|string|max:1000',
            'parameters' => 'nullable|array',
            'scheduled_for' => 'nullable|date|after:now',
        ]);

        $user = auth()->user();

        $report = Report::create([
            'user_id' => $user->id,
            'title' => $request->title,
            'report_type' => $request->report_type,
            'description' => $request->description,
            'parameters' => json_encode($request->parameters ?? []),
            'status' => $request->scheduled_for ? 'scheduled' : 'pending',
            'scheduled_for' => $request->scheduled_for,
        ]);

        if (!$request->scheduled_for) {
            // Generate report immediately
            \App\Jobs\GenerateReportJob::dispatch($report);
        }

        return redirect()->route('reports.show', $report)
            ->with('success', 'Report created successfully and is being generated.');
    }

    public function download(Report $report)
    {
        $this->authorize('view', $report);

        if ($report->status !== 'completed' || !$report->file_path) {
            return redirect()->back()
                ->with('error', 'Report is not ready for download.');
        }

        $filePath = storage_path('app/' . $report->file_path);

        if (!file_exists($filePath)) {
            return redirect()->back()
                ->with('error', 'Report file not found.');
        }

        $fileName = $report->title . '_' . $report->created_at->format('Y-m-d') . '.pdf';

        return response()->download($filePath, $fileName);
    }

    public function regenerate(Report $report)
    {
        $this->authorize('update', $report);

        $report->update([
            'status' => 'pending',
            'generated_at' => null,
            'file_path' => null,
        ]);

        \App\Jobs\GenerateReportJob::dispatch($report);

        return redirect()->back()
            ->with('success', 'Report regeneration started.');
    }

    public function destroy(Report $report)
    {
        $this->authorize('delete', $report);

        // Delete associated file if exists
        if ($report->file_path) {
            $filePath = storage_path('app/' . $report->file_path);
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        $report->delete();

        return redirect()->route('reports.index')
            ->with('success', 'Report deleted successfully.');
    }

    public function api(Request $request)
    {
        $user = auth()->user();
        $limit = min($request->get('limit', 20), 100);

        $query = Report::with(['user']);

        if ($user->user_type !== 'admin') {
            $query->where('user_id', $user->id);
        }

        $reports = $query->latest()
            ->take($limit)
            ->get()
            ->map(function ($report) {
                return [
                    'id' => $report->id,
                    'title' => $report->title,
                    'type' => $report->report_type,
                    'status' => $report->status,
                    'created_at' => $report->created_at->toISOString(),
                    'generated_at' => $report->generated_at?->toISOString(),
                    'scheduled_for' => $report->scheduled_for?->toISOString(),
                    'user' => [
                        'id' => $report->user->id,
                        'name' => $report->user->name,
                    ],
                    'download_url' => $report->status === 'completed' && $report->file_path 
                        ? route('reports.download', $report) 
                        : null,
                ];
            });

        return response()->json([
            'reports' => $reports,
            'count' => $reports->count(),
        ]);
    }

    public function generate(Request $request)
    {
        $request->validate([
            'type' => 'required|in:weather,flood_risk,environmental,custom',
            'title' => 'required|string|max:255',
            'parameters' => 'nullable|array',
        ]);

        $user = auth()->user();

        try {
            $reportData = $this->reportService->generateReport(
                $request->type,
                $request->parameters ?? [],
                $user
            );

            return response()->json([
                'message' => 'Report generated successfully',
                'report' => [
                    'type' => $request->type,
                    'title' => $request->title,
                    'data' => $reportData,
                    'generated_at' => now()->toISOString(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to generate report',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function templates()
    {
        $templates = [
            'weather' => [
                'name' => 'Weather Summary Report',
                'description' => 'Comprehensive weather data analysis',
                'parameters' => [
                    'location' => 'Location to analyze',
                    'period' => 'Time period (7d, 30d, 90d)',
                    'include_forecast' => 'Include weather forecast',
                ],
            ],
            'flood_risk' => [
                'name' => 'Flood Risk Assessment',
                'description' => 'Flood risk analysis and predictions',
                'parameters' => [
                    'location' => 'Location to analyze',
                    'risk_threshold' => 'Minimum risk level to include',
                    'include_recommendations' => 'Include safety recommendations',
                ],
            ],
            'environmental' => [
                'name' => 'Environmental Impact Report',
                'description' => 'Environmental data and trends analysis',
                'parameters' => [
                    'location' => 'Location to analyze',
                    'metrics' => 'Environmental metrics to include',
                    'comparison_period' => 'Period for trend comparison',
                ],
            ],
            'custom' => [
                'name' => 'Custom Data Report',
                'description' => 'Custom analysis based on specific requirements',
                'parameters' => [
                    'data_sources' => 'Data sources to include',
                    'analysis_type' => 'Type of analysis to perform',
                    'output_format' => 'Preferred output format',
                ],
            ],
        ];

        return response()->json(['templates' => $templates]);
    }

    public function schedule(Request $request)
    {
        $request->validate([
            'report_id' => 'required|exists:reports,id',
            'scheduled_for' => 'required|date|after:now',
            'recurring' => 'nullable|in:daily,weekly,monthly',
        ]);

        $report = Report::findOrFail($request->report_id);
        $this->authorize('update', $report);

        $report->update([
            'scheduled_for' => $request->scheduled_for,
            'recurring_pattern' => $request->recurring,
            'status' => 'scheduled',
        ]);

        return response()->json([
            'message' => 'Report scheduled successfully',
            'scheduled_for' => $report->scheduled_for->toISOString(),
        ]);
    }

    protected function getReportStatistics($user)
    {
        $cacheKey = 'report_statistics_' . $user->id;
        
        return Cache::remember($cacheKey, 1800, function () use ($user) {
            $query = Report::query();

            if ($user->user_type !== 'admin') {
                $query->where('user_id', $user->id);
            }

            $reports = $query->get();

            return [
                'total' => $reports->count(),
                'by_status' => $reports->groupBy('status')->map->count(),
                'by_type' => $reports->groupBy('report_type')->map->count(),
                'completed_this_month' => $reports->where('status', 'completed')
                    ->where('generated_at', '>=', Carbon::now()->startOfMonth())->count(),
                'average_generation_time' => $this->calculateAverageGenerationTime($reports),
            ];
        });
    }

    protected function getAvailableReportTypes()
    {
        return [
            'weather' => 'Weather Data Report',
            'flood_risk' => 'Flood Risk Assessment',
            'environmental' => 'Environmental Impact Report',
            'custom' => 'Custom Analysis Report',
        ];
    }

    protected function calculateAverageGenerationTime($reports)
    {
        $completedReports = $reports->where('status', 'completed')
            ->whereNotNull('generated_at');

        if ($completedReports->isEmpty()) {
            return 0;
        }

        $totalTime = $completedReports->sum(function ($report) {
            return $report->generated_at->diffInSeconds($report->created_at);
        });

        return round($totalTime / $completedReports->count());
    }
}