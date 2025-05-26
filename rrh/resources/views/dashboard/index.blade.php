<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('User Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 dark:bg-gray-900 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header Section -->
            <div class="mb-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-2xl font-bold text-amber-800 dark:text-amber-200">Rwanda Resilience Hub</h1>
                            <p class="text-gray-600 dark:text-gray-400 mt-2">Technology-driven flood early warning and disaster resilience system</p>
                        </div>
                        <div class="flex items-center space-x-4">
                            <div class="bg-amber-100 dark:bg-amber-900 p-3 rounded-full">
                                <svg class="w-8 h-8 text-amber-700 dark:text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.002 4.002 0 003 15z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Data Collection Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- Rainfall Data -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-amber-800 dark:text-amber-200 mb-4">Rainfall data</h3>
                    <div class="mb-4">
                        <div class="flex items-center justify-between mb-2">
                            <input type="text" placeholder="Search for location" class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-amber-500 dark:bg-gray-700 dark:text-white">
                            <button class="ml-2 p-2 bg-amber-600 text-white rounded-md hover:bg-amber-700">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </button>
                        </div>
                        <div class="flex items-center space-x-2 mb-4">
                            <span class="text-sm text-gray-600 dark:text-gray-400">10-20 mm/hr</span>
                            <select class="text-sm border border-gray-300 dark:border-gray-600 rounded px-2 py-1 dark:bg-gray-700 dark:text-white">
                                <option>Daily</option>
                                <option>Weekly</option>
                                <option>Monthly</option>
                            </select>
                        </div>
                        <div class="relative">
                            <div class="w-24 h-24 mx-auto relative">
                                <svg class="w-24 h-24 transform -rotate-90" viewBox="0 0 36 36">
                                    <path class="text-gray-300 dark:text-gray-600" stroke="currentColor" stroke-width="3" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"></path>
                                    <path class="text-red-600" stroke="currentColor" stroke-width="3" fill="none" stroke-dasharray="75, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"></path>
                                </svg>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <span class="text-xs text-gray-600 dark:text-gray-400">Heavy rain</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- River Level -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-amber-800 dark:text-amber-200 mb-4">River level</h3>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm text-gray-600 dark:text-gray-400 mb-2">Water Level</label>
                            <input type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 dark:text-gray-400 mb-2">Flow level</label>
                            <input type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-gray-600 dark:text-gray-400 mb-2">Timestamp</label>
                            <input type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 dark:text-gray-400 mb-2">Threshold Indicator</label>
                            <input type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white">
                        </div>
                    </div>
                </div>

                <!-- Soil Moisture -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-amber-800 dark:text-amber-200 mb-4">Soil moisture</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm text-gray-600 dark:text-gray-400 mb-2">Soil moisture level</label>
                            <input type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 dark:text-gray-400 mb-2">Measurement dept</label>
                            <input type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 dark:text-gray-400 mb-2">Sensor identification and location</label>
                            <input type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Flood Risk Section -->
            <div class="mb-8">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200 mb-2">Flood Risk: {{ number_format($floodRisk ?? 82.345, 3) }}%</h2>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        <!-- Real-time Prediction Chart -->
                        <div class="lg:col-span-1">
                            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-4">Real time prediction</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Daily Flood Prediction for the Next 7 Days</p>
                            <div class="h-64 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                                <canvas id="predictionChart" width="300" height="200"></canvas>
                            </div>
                        </div>

                        <!-- Live Stream Map -->
                        <div class="lg:col-span-1">
                            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-4">Live stream</h3>
                            <div class="h-64 bg-gray-100 dark:bg-gray-700 rounded-lg overflow-hidden">
                                <div class="w-full h-full bg-gradient-to-br from-blue-400 via-green-400 to-yellow-400 flex items-center justify-center">
                                    <span class="text-white font-semibold">Rwanda Live Weather Map</span>
                                </div>
                            </div>
                        </div>

                        <!-- Conclusion -->
                        <div class="lg:col-span-1">
                            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-4">Conclusion of prediction</h3>
                            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                                <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                                    The system predicts flood risks by analyzing rainfall, river levels, and soil moisture. It forecasts rainfall for 10 days, with future upgrades including real-time IoT sensor integration for improved accuracy.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Generate Report Button -->
            <div class="text-center mb-8">
                <button onclick="generateReport()" class="bg-amber-700 hover:bg-amber-800 text-white font-semibold py-3 px-6 rounded-lg shadow-lg transition duration-300">
                    Generate Report
                </button>
            </div>

            <!-- Contact Information -->
            <div class="bg-amber-800 text-white rounded-lg p-6">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="font-medium">Rwanda Resilience Hub is technology-driven flood early warning and disaster resilience system to enhance Rwanda's flood preparedness and response capabilities.</p>
                    </div>
                    <div class="text-right">
                        <p class="font-semibold mb-2">Contact us via:</p>
                        <p class="text-sm">📞 0782467835</p>
                        <p class="text-sm">✉️ rrh@gmail.com</p>
                        <p class="text-sm">You can visit us on our website www.rrh.com</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function generateReport() {
            window.location.href = '{{ route("reports.index") }}';
        }

        // Simple chart rendering
        document.addEventListener('DOMContentLoaded', function() {
            const canvas = document.getElementById('predictionChart');
            if (canvas) {
                const ctx = canvas.getContext('2d');
                
                // Sample data for flood prediction
                const data = [30, 45, 60, 75, 82, 78, 65];
                const labels = ['Day 1', 'Day 2', 'Day 3', 'Day 4', 'Day 5', 'Day 6', 'Day 7'];
                
                // Draw simple bar chart
                const barWidth = 30;
                const barSpacing = 10;
                const maxHeight = 150;
                const startX = 20;
                const startY = 170;
                
                ctx.fillStyle = '#f59e0b';
                
                data.forEach((value, index) => {
                    const barHeight = (value / 100) * maxHeight;
                    const x = startX + (index * (barWidth + barSpacing));
                    const y = startY - barHeight;
                    
                    ctx.fillRect(x, y, barWidth, barHeight);
                    
                    // Add labels
                    ctx.fillStyle = '#374151';
                    ctx.font = '10px Arial';
                    ctx.fillText(labels[index], x, startY + 15);
                    ctx.fillText(value + '%', x, y - 5);
                    ctx.fillStyle = '#f59e0b';
                });
            }
        });
    </script>
    @endpush
</x-app-layout>