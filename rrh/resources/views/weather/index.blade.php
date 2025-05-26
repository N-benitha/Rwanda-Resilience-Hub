@extends('layouts.app')

@section('content')
<div class="py-12 bg-gradient-to-br from-amber-50 to-orange-100 dark:from-gray-900 dark:to-gray-800 min-h-screen">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-6 lg:p-8 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-amber-800 dark:bg-amber-700 rounded-lg flex items-center justify-center mr-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"></path>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Weather Monitoring</h1>
                            <p class="text-gray-600 dark:text-gray-400">Real-time weather data and forecasts</p>
                        </div>
                    </div>
                    <button onclick="refreshWeatherData()" class="bg-amber-800 hover:bg-amber-900 text-white px-4 py-2 rounded-lg transition duration-200">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Refresh
                    </button>
                </div>
            </div>

            <div class="p-6 lg:p-8">
                <!-- Current Weather Overview -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-blue-100">Current Temperature</p>
                                <p class="text-3xl font-bold">{{ $currentWeather->temperature ?? '--' }}°C</p>
                            </div>
                            <div class="text-4xl">🌡️</div>
                        </div>
                    </div>
                    
                    <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-xl p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-green-100">Humidity</p>
                                <p class="text-3xl font-bold">{{ $currentWeather->humidity ?? '--' }}%</p>
                            </div>
                            <div class="text-4xl">💧</div>
                        </div>
                    </div>
                    
                    <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-purple-100">Wind Speed</p>
                                <p class="text-3xl font-bold">{{ $currentWeather->wind_speed ?? '--' }} km/h</p>
                            </div>
                            <div class="text-4xl">💨</div>
                        </div>
                    </div>
                </div>

                <!-- Weather Data Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Recent Weather Data -->
                    <div class="bg-white dark:bg-gray-700 rounded-xl p-6 shadow-lg">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Recent Weather Data</h3>
                        <div class="space-y-4">
                            @forelse($recentWeather as $weather)
                            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-600 rounded-lg">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-amber-800 dark:bg-amber-700 rounded-full flex items-center justify-center mr-4">
                                        <span class="text-white text-sm font-bold">{{ $weather->created_at->format('H') }}</span>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ $weather->location }}</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $weather->created_at->format('M d, Y H:i') }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-gray-900 dark:text-white">{{ $weather->temperature }}°C</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $weather->humidity }}% humidity</p>
                                </div>
                            </div>
                            @empty
                            <div class="text-center py-8">
                                <p class="text-gray-500 dark:text-gray-400">No weather data available</p>
                                <button onclick="fetchWeatherData()" class="mt-4 bg-amber-800 hover:bg-amber-900 text-white px-4 py-2 rounded-lg">
                                    Fetch Weather Data
                                </button>
                            </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Weather Chart -->
                    <div class="bg-white dark:bg-gray-700 rounded-xl p-6 shadow-lg">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Temperature Trend (24h)</h3>
                        <div class="h-64 flex items-center justify-center">
                            <canvas id="temperatureChart" class="w-full h-full"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Location-based Weather -->
                <div class="mt-8 bg-white dark:bg-gray-700 rounded-xl p-6 shadow-lg">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Weather by Location</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @forelse($locationWeather as $location => $weather)
                        <x-weather-card :weather="$weather" :location="$location" />
                        @empty
                        <div class="col-span-full text-center py-8">
                            <p class="text-gray-500 dark:text-gray-400">No location data available</p>
                        </div>
                        @endforelse
                    </div>
                </div>

                <!-- Weather Alerts -->
                @if($weatherAlerts && $weatherAlerts->count() > 0)
                <div class="mt-8 bg-red-50 dark:bg-red-900/20 rounded-xl p-6">
                    <h3 class="text-lg font-semibold text-red-800 dark:text-red-400 mb-4">Weather Alerts</h3>
                    <div class="space-y-4">
                        @foreach($weatherAlerts as $alert)
                        <div class="bg-red-100 dark:bg-red-800/30 border border-red-200 dark:border-red-700 rounded-lg p-4">
                            <div class="flex items-start">
                                <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center mr-3 mt-1">
                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-medium text-red-800 dark:text-red-400">{{ $alert->title }}</h4>
                                    <p class="text-red-700 dark:text-red-300 text-sm mt-1">{{ $alert->description }}</p>
                                    <p class="text-red-600 dark:text-red-400 text-xs mt-2">{{ $alert->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
function refreshWeatherData() {
    window.location.reload();
}

function fetchWeatherData() {
    fetch('/api/weather/fetch', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => console.error('Error:', error));
}

// Temperature Chart
const ctx = document.getElementById('temperatureChart').getContext('2d');
const temperatureData = @json($chartData ?? []);

new Chart(ctx, {
    type: 'line',
    data: {
        labels: temperatureData.map(d => d.time),
        datasets: [{
            label: 'Temperature (°C)',
            data: temperatureData.map(d => d.temperature),
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
                beginAtZero: false,
                grid: {
                    color: 'rgba(156, 163, 175, 0.2)'
                }
            },
            x: {
                grid: {
                    color: 'rgba(156, 163, 175, 0.2)'
                }
            }
        }
    }
});
</script>
@endsection