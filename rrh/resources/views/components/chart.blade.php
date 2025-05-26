@props(['chartId', 'title', 'data', 'type' => 'line'])

<div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 border border-amber-200 dark:border-amber-700">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-amber-900 dark:text-amber-100">{{ $title }}</h3>
        <div class="flex space-x-2">
            <button class="chart-toggle px-3 py-1 text-xs bg-amber-100 dark:bg-amber-900 text-amber-800 dark:text-amber-200 rounded-md hover:bg-amber-200 dark:hover:bg-amber-800 transition-colors" 
                    data-type="line" data-chart="{{ $chartId }}">Line</button>
            <button class="chart-toggle px-3 py-1 text-xs bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors" 
                    data-type="bar" data-chart="{{ $chartId }}">Bar</button>
        </div>
    </div>
    
    <div class="relative">
        <canvas id="{{ $chartId }}" class="w-full h-64"></canvas>
    </div>
    
    <!-- Chart Legend -->
    <div class="mt-4 flex flex-wrap gap-4 text-sm">
        <div class="flex items-center">
            <div class="w-3 h-3 bg-blue-500 rounded-full mr-2"></div>
            <span class="text-gray-700 dark:text-gray-300">Rainfall (mm)</span>
        </div>
        <div class="flex items-center">
            <div class="w-3 h-3 bg-red-500 rounded-full mr-2"></div>
            <span class="text-gray-700 dark:text-gray-300">Temperature (°C)</span>
        </div>
        <div class="flex items-center">
            <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
            <span class="text-gray-700 dark:text-gray-300">Humidity (%)</span>
        </div>
        <div class="flex items-center">
            <div class="w-3 h-3 bg-amber-500 rounded-full mr-2"></div>
            <span class="text-gray-700 dark:text-gray-300">Flood Risk (%)</span>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('{{ $chartId }}').getContext('2d');
    const chartData = @json($data);
    
    const chart = new Chart(ctx, {
        type: '{{ $type }}',
        data: {
            labels: chartData.labels || [],
            datasets: chartData.datasets || []
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    borderColor: '#d97706',
                    borderWidth: 1
                }
            },
            scales: {
                x: {
                    grid: {
                        color: 'rgba(156, 163, 175, 0.3)'
                    },
                    ticks: {
                        color: '#6b7280'
                    }
                },
                y: {
                    grid: {
                        color: 'rgba(156, 163, 175, 0.3)'
                    },
                    ticks: {
                        color: '#6b7280'
                    },
                    beginAtZero: true
                }
            },
            elements: {
                line: {
                    tension: 0.4
                },
                point: {
                    radius: 4,
                    hoverRadius: 6
                }
            }
        }
    });
    
    // Chart type toggle functionality
    document.querySelectorAll('.chart-toggle[data-chart="{{ $chartId }}"]').forEach(button => {
        button.addEventListener('click', function() {
            const newType = this.dataset.type;
            
            // Update button states
            document.querySelectorAll('.chart-toggle[data-chart="{{ $chartId }}"]').forEach(btn => {
                btn.className = btn.className.replace(/bg-amber-\d+/g, 'bg-gray-100').replace(/text-amber-\d+/g, 'text-gray-700');
            });
            this.className = this.className.replace(/bg-gray-\d+/g, 'bg-amber-100').replace(/text-gray-\d+/g, 'text-amber-800');
            
            // Update chart type
            chart.config.type = newType;
            chart.update();
        });
    });
});
</script>