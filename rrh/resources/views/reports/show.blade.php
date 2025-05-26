@extends('layouts.app')

@section('content')
<div class="py-12 bg-gradient-to-br from-amber-50 to-orange-100 dark:from-gray-900 dark:to-gray-800 min-h-screen">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
            <!-- Header -->
            <div class="p-6 lg:p-8 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <a href="{{ route('reports.index') }}" class="mr-4 text-amber-800 hover:text-amber-900 dark:text-amber-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </a>
                        <div class="w-12 h-12 bg-amber-800 dark:bg-amber-700 rounded-lg flex items-center justify-center mr-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Report #{{ $report->id }}</h1>
                            <p class="text-gray-600 dark:text-gray-400">{{ $report->title ?? 'Flood Risk Assessment Report' }}</p>
                        </div>
                    </div>
                    <div class="flex space-x-3">
                        <button onclick="printReport()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition duration-200">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                            </svg>
                            Print
                        </button>
                        <a href="{{ route('reports.download', $report) }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition duration-200">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Download PDF
                        </a>
                    </div>
                </div>
            </div>

            <div class="p-6 lg:p-8" id="reportContent">
                <!-- Report Summary -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-blue-100">Risk Level</p>
                                <p class="text-2xl font-bold">{{ ucfirst($report->risk_level) }}</p>
                            </div>
                            <div class="text-3xl">
                                @if($report->risk_level === 'critical')
                                    🚨
                                @elseif($report->risk_level === 'high')
                                    ⚠️
                                @elseif($report->risk_level === 'medium')
                                    ⚡
                                @else
                                    ✅
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-xl p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-green-100">Location</p>
                                <p class="text-lg font-bold">{{ $report->location }}</p>
                            </div>
                            <div class="text-3xl">📍</div>
                        </div>
                    </div>
                    
                    <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-purple-100">Generated</p>
                                <p class="text-lg font-bold">{{ $report->created_at->format('M d, Y') }}</p>
                            </div>
                            <div class="text-3xl">📅</div>
                        </div>
                    </div>
                </div>

                <!-- Report Content -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                    <!-- Weather Data Analysis -->
                    <div class="bg-white dark:bg-gray-700 rounded-xl p-6 shadow-lg">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-amber-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"></path>
                            </svg>
                            Weather Data Analysis
                        </h3>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-600 rounded-lg">
                                <span class="text-gray-700 dark:text-gray-300">Temperature</span>
                                <span class="font-semibold text-gray-900 dark:text-white">{{ $report->weather_data['temperature'] ?? 'N/A' }}°C</span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-600 rounded-lg">
                                <span class="text-gray-700 dark:text-gray-300">Humidity</span>
                                <span class="font-semibold text-gray-900 dark:text-white">{{ $report->weather_data['humidity'] ?? 'N/A' }}%</span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-600 rounded-lg">
                                <span class="text-gray-700 dark:text-gray-300">Wind Speed</span>
                                <span class="font-semibold text-gray-900 dark:text-white">{{ $report->weather_data['wind_speed'] ?? 'N/A' }} km/h</span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-600 rounded-lg">
                                <span class="text-gray-700 dark:text-gray-300">Precipitation</span>
                                <span class="font-semibold text-gray-900 dark:text-white">{{ $report->weather_data['precipitation'] ?? 'N/A' }} mm</span>
                            </div>
                        </div>
                    </div>

                    <!-- Risk Assessment -->
                    <div class="bg-white dark:bg-gray-700 rounded-xl p-6 shadow-lg">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.732 15.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                            Risk Assessment
                        </h3>
                        <div class="space-y-4">
                            <div class="p-4 border-l-4 border-{{ $report->risk_level === 'high' ? 'red' : ($report->risk_level === 'medium' ? 'yellow' : 'green') }}-500 bg-{{ $report->risk_level === 'high' ? 'red' : ($report->risk_level === 'medium' ? 'yellow' : 'green') }}-50 dark:bg-gray-600">
                                <p class="font-semibold text-gray-900 dark:text-white mb-2">Overall Risk Level: {{ ucfirst($report->risk_level) }}</p>
                                <p class="text-sm text-gray-700 dark:text-gray-300">{{ $report->risk_description ?? 'Based on current weather conditions and historical data analysis.' }}</p>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div class="text-center p-3 bg-gray-50 dark:bg-gray-600 rounded-lg">
                                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $report->risk_score ?? '75' }}%</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Risk Score</p>
                                </div>
                                <div class="text-center p-3 bg-gray-50 dark:bg-gray-600 rounded-lg">
                                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $report->confidence ?? '85' }}%</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Confidence</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detailed Analysis -->
                <div class="bg-white dark:bg-gray-700 rounded-xl p-6 shadow-lg mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-amber-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        Detailed Analysis
                    </h3>
                    
                    <div class="prose dark:prose-invert max-w-none">
                        <h4>Executive Summary</h4>
                        <p class="text-gray-700 dark:text-gray-300 mb-4">
                            {{ $report->summary ?? 'This flood risk assessment analyzes current weather conditions, historical data, and predictive models to determine the likelihood of flooding in the specified area. The analysis considers multiple factors including precipitation levels, soil moisture, river levels, and terrain characteristics.' }}
                        </p>

                        <h4>Key Findings</h4>
                        <ul class="text-gray-700 dark:text-gray-300 space-y-2 mb-4">
                            @if($report->findings && is_array($report->findings))
                                @foreach($report->findings as $finding)
                                <li>{{ $finding }}</li>
                                @endforeach
                            @else
                            <li>Current weather conditions show {{ $report->weather_data['precipitation'] ?? 'moderate' }} precipitation levels</li>
                            <li>Soil moisture levels are within normal parameters for the season</li>
                            <li>River levels are being monitored and remain stable</li>
                            <li>Historical data indicates {{ $report->risk_level }} risk probability for this time period</li>
                            @endif
                        </ul>

                        <h4>Recommendations</h4>
                        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg p-4 mb-4">
                            <ul class="text-gray-700 dark:text-gray-300 space-y-2">
                                @if($report->recommendations && is_array($report->recommendations))
                                    @foreach($report->recommendations as $recommendation)
                                    <li class="flex items-start">
                                        <svg class="w-4 h-4 text-blue-500 mt-1 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                        {{ $recommendation }}
                                    </li>
                                    @endforeach
                                @else
                                <li class="flex items-start">
                                    <svg class="w-4 h-4 text-blue-500 mt-1 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    Continue monitoring weather conditions and sensor data
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-4 h-4 text-blue-500 mt-1 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    Maintain emergency preparedness protocols
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-4 h-4 text-blue-500 mt-1 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    Review and update flood response plans as needed
                                </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Charts and Visualizations -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <div class="bg-white dark:bg-gray-700 rounded-xl p-6 shadow-lg">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Risk Factors</h3>
                        <div class="h-64">
                            <canvas id="riskFactorsChart"></canvas>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-700 rounded-xl p-6 shadow-lg">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Historical Comparison</h3>
                        <div class="h-64">
                            <canvas id="historicalChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Report Metadata -->
                <div class="mt-8 bg-gray-50 dark:bg-gray-600 rounded-xl p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Report Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Generated By</p>
                            <p class="font-medium text-gray-900 dark:text-white">{{ $report->user->name ?? 'System' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Report Type</p>
                            <p class="font-medium text-gray-900 dark:text-white">{{ ucfirst(str_replace('_', ' ', $report->type ?? 'flood_risk')) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Data Sources</p>
                            <p class="font-medium text-gray-900 dark:text-white">OpenWeatherMap, NASA POWER, Local Sensors</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function printReport() {
    window.print();
}

// Risk Factors Chart
const riskCtx = document.getElementById('riskFactorsChart').getContext('2d');
const riskData = @json($report->risk_factors ?? [
    'Precipitation' => 65,
    'Soil Moisture' => 45,
    'River Level' => 30,
    'Wind Speed' => 25,
    'Temperature' => 20
]);

new Chart(riskCtx, {
    type: 'doughnut',
    data: {
        labels: Object.keys(riskData),
        datasets: [{
            data: Object.values(riskData),
            backgroundColor: [
                '#3b82f6',
                '#10b981',
                '#f59e0b',
                '#ef4444',
                '#8b5cf6'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Historical Chart
const histCtx = document.getElementById('historicalChart').getContext('2d');
const historicalData = @json($report->historical_data ?? [
    ['Jan', 45], ['Feb', 52], ['Mar', 38], ['Apr', 65], ['May', 72], ['Jun', 58]
]);

new Chart(histCtx, {
    type: 'line',
    data: {
        labels: historicalData.map(d => d[0]),
        datasets: [{
            label: 'Risk Level (%)',
            data: historicalData.map(d => d[1]),
            borderColor: '#b45309',
            backgroundColor: 'rgba(180, 83, 9, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                max: 100
            }
        }
    }
});
</script>

<style>
@media print {
    .no-print {
        display: none !important;
    }
    
    body {
        background: white !important;
    }
    
    .bg-gradient-to-br {
        background: white !important;
    }
}
</style>
@endsection