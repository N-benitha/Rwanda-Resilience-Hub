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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Reports & Analytics</h1>
                            <p class="text-gray-600 dark:text-gray-400">Flood risk reports and data analysis</p>
                        </div>
                    </div>
                    <div class="flex space-x-3">
                        <button onclick="generateReport()" class="bg-amber-800 hover:bg-amber-900 text-white px-4 py-2 rounded-lg transition duration-200">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Generate Report
                        </button>
                        <button onclick="exportReports()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition duration-200">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Export
                        </button>
                    </div>
                </div>
            </div>

            <div class="p-6 lg:p-8">
                <!-- Report Statistics -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-blue-100">Total Reports</p>
                                <p class="text-3xl font-bold">{{ $reportStats['total'] ?? 0 }}</p>
                            </div>
                            <div class="text-3xl">📊</div>
                        </div>
                    </div>
                    
                    <div class="bg-gradient-to-r from-red-500 to-red-600 rounded-xl p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-red-100">High Risk Areas</p>
                                <p class="text-3xl font-bold">{{ $reportStats['high_risk'] ?? 0 }}</p>
                            </div>
                            <div class="text-3xl">🚨</div>
                        </div>
                    </div>
                    
                    <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 rounded-xl p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-yellow-100">This Month</p>
                                <p class="text-3xl font-bold">{{ $reportStats['this_month'] ?? 0 }}</p>
                            </div>
                            <div class="text-3xl">📅</div>
                        </div>
                    </div>
                    
                    <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-xl p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-green-100">Accuracy Rate</p>
                                <p class="text-3xl font-bold">{{ $reportStats['accuracy'] ?? 0 }}%</p>
                            </div>
                            <div class="text-3xl">✅</div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-white dark:bg-gray-700 rounded-xl p-6 shadow-lg mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Filter Reports</h3>
                    <form action="{{ route('reports.index') }}" method="GET" class="flex flex-wrap gap-4">
                        <div class="flex-1 min-w-48">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date Range</label>
                            <select name="date_range" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                                <option value="">All Time</option>
                                <option value="today" {{ request('date_range') == 'today' ? 'selected' : '' }}>Today</option>
                                <option value="week" {{ request('date_range') == 'week' ? 'selected' : '' }}>This Week</option>
                                <option value="month" {{ request('date_range') == 'month' ? 'selected' : '' }}>This Month</option>
                                <option value="quarter" {{ request('date_range') == 'quarter' ? 'selected' : '' }}>This Quarter</option>
                            </select>
                        </div>
                        
                        <div class="flex-1 min-w-48">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Risk Level</label>
                            <select name="risk_level" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                                <option value="">All Levels</option>
                                <option value="low" {{ request('risk_level') == 'low' ? 'selected' : '' }}>Low</option>
                                <option value="medium" {{ request('risk_level') == 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="high" {{ request('risk_level') == 'high' ? 'selected' : '' }}>High</option>
                                <option value="critical" {{ request('risk_level') == 'critical' ? 'selected' : '' }}>Critical</option>
                            </select>
                        </div>
                        
                        <div class="flex-1 min-w-48">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Location</label>
                            <input type="text" name="location" value="{{ request('location') }}" placeholder="Enter location..." 
                                   class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                        </div>
                        
                        <div class="flex items-end">
                            <button type="submit" class="bg-amber-800 hover:bg-amber-900 text-white px-6 py-2 rounded-lg transition duration-200">
                                Filter
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Reports List -->
                <div class="bg-white dark:bg-gray-700 rounded-xl shadow-lg overflow-hidden">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-600">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Reports</h3>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 dark:bg-gray-600">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Report ID
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Location
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Risk Level
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Generated
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-700 divide-y divide-gray-200 dark:divide-gray-600">
                                @forelse($reports as $report)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-600">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            #{{ $report->id }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-white">{{ $report->location }}</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ $report->coordinates }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $riskColors = [
                                                'low' => 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-200',
                                                'medium' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-200',
                                                'high' => 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-200',
                                                'critical' => 'bg-purple-100 text-purple-800 dark:bg-purple-800 dark:text-purple-200'
                                            ];
                                        @endphp
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $riskColors[$report->risk_level] ?? $riskColors['low'] }}">
                                            {{ ucfirst($report->risk_level) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $report->created_at->format('M d, Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($report->status === 'completed')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-200">
                                            Completed
                                        </span>
                                        @elseif($report->status === 'processing')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-200">
                                            Processing
                                        </span>
                                        @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200">
                                            {{ ucfirst($report->status) }}
                                        </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('reports.show', $report) }}" class="text-amber-800 hover:text-amber-900 dark:text-amber-400 dark:hover:text-amber-300">
                                                View
                                            </a>
                                            <a href="{{ route('reports.download', $report) }}" class="text-green-600 hover:text-green-700 dark:text-green-400 dark:hover:text-green-300">
                                                Download
                                            </a>
                                            @can('delete', $report)
                                            <button onclick="deleteReport({{ $report->id }})" class="text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300">
                                                Delete
                                            </button>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center">
                                        <div class="text-gray-500 dark:text-gray-400">
                                            <svg class="w-12 h-12 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            <p class="text-lg font-medium">No reports found</p>
                                            <p class="text-sm">Generate your first report to get started</p>
                                            <button onclick="generateReport()" class="mt-4 bg-amber-800 hover:bg-amber-900 text-white px-4 py-2 rounded-lg">
                                                Generate Report
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    @if($reports->hasPages())
                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-600">
                        {{ $reports->appends(request()->query())->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Generate Report Modal -->
<div id="generateReportModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg max-w-md w-full p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Generate New Report</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form id="reportForm" onsubmit="submitReportForm(event)">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Report Type</label>
                    <select name="type" required class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        <option value="">Select type...</option>
                        <option value="flood_risk">Flood Risk Assessment</option>
                        <option value="weather_analysis">Weather Analysis</option>
                        <option value="sensor_data">Sensor Data Summary</option>
                        <option value="comprehensive">Comprehensive Report</option>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Location</label>
                    <input type="text" name="location" placeholder="Enter location or coordinates..." required
                           class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Time Period</label>
                    <select name="period" required class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        <option value="24h">Last 24 Hours</option>
                        <option value="7d">Last 7 Days</option>
                        <option value="30d">Last 30 Days</option>
                        <option value="90d">Last 90 Days</option>
                    </select>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-amber-800 hover:bg-amber-900 text-white rounded-lg">
                        Generate Report
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function generateReport() {
    document.getElementById('generateReportModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('generateReportModal').classList.add('hidden');
}

function submitReportForm(event) {
    event.preventDefault();
    const formData = new FormData(event.target);
    
    fetch('{{ route("reports.generate") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeModal();
            window.location.reload();
        } else {
            alert('Error generating report: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error generating report');
    });
}

function exportReports() {
    window.location.href = '{{ route("reports.export") }}?' + new URLSearchParams({{ Js::from(request()->query()) }});
}

function deleteReport(reportId) {
    if (confirm('Are you sure you want to delete this report?')) {
        fetch(`/reports/${reportId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert('Error deleting report');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting report');
        });
    }
}
</script>
@endsection