<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\FloodPrediction;
use App\Models\Report;
use App\Models\Sensor;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display the user dashboard based on user type
     */
    public function index()
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            return $this->adminDashboard();
        } elseif ($user->isGovernment()) {
            return $this->governmentDashboard();
        } else {
            return $this->civilianDashboard();
        }
    }

    /**
     * Display admin dashboard with system statistics
     */
    private function adminDashboard()
    {
        $userCount = User::count();
        $reportCount = Report::count();
        $predictionCount = FloodPrediction::count();
        $correctPredictionCount = FloodPrediction::where('is_correct', true)->count();
        
        // Get new accounts since 2024
        $newAccountsData = User::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
            ->whereYear('created_at', '>=', 2024)
            ->groupBy('month')
            ->orderBy('month')
            ->get();
        
        // Common user issues/complaints
        $userIssues = [
            'Some features are hard to use',
            'I am not receiving reports',
            'Response too long'
        ];

        return view('dashboard.admin', compact(
            'userCount', 
            'reportCount', 
            'predictionCount', 
            'correctPredictionCount',
            'newAccountsData',
            'userIssues'
        ));
    }

    /**
     * Display government dashboard with monitoring capabilities
     */
    private function governmentDashboard()
    {
        $latestPredictions = FloodPrediction::with('user')
            ->latest()
            ->take(5)
            ->get();
            
        $highRiskLocations = FloodPrediction::where('risk_percentage', '>', 70)
            ->distinct('location')
            ->pluck('location');
            
        return view('dashboard.government', compact('latestPredictions', 'highRiskLocations'));
    }

    /**
     * Display civilian dashboard with basic flood information
     */
    private function civilianDashboard()
    {
        $user = auth()->user();
        
        // User's sensors
        $sensors = $user->sensors;
        
        // User's predictions
        $predictions = $user->predictions()
            ->latest()
            ->take(5)
            ->get();
            
        // Latest reports
        $latestReports = Report::latest('published_at')
            ->take(3)
            ->get();
            
        return view('dashboard.civilian', compact('sensors', 'predictions', 'latestReports'));
    }
}