@props(['floodRisk'])

@php
    $percentage = $floodRisk->risk_score;
    $riskLevel = $percentage >= 80 ? 'High' : ($percentage >= 50 ? 'Medium' : 'Low');
    $colorClass = $percentage >= 80 ? 'text-red-600 dark:text-red-400' : ($percentage >= 50 ? 'text-yellow-600 dark:text-yellow-400' : 'text-green-600 dark:text-green-400');
    $bgColorClass = $percentage >= 80 ? 'bg-red-500' : ($percentage >= 50 ? 'bg-yellow-500' : 'bg-green-500');
@endphp

<div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 border border-amber-200 dark:border-amber-700">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-amber-900 dark:text-amber-100">Flood Risk Assessment</h3>
        <span class="text-sm text-amber-700 dark:text-amber-300">
            {{ $floodRisk->created_at->format('M j, Y H:i') }}
        </span>
    </div>
    
    <div class="text-center mb-6">
        <div class="text-4xl font-bold {{ $colorClass }} mb-2">
            {{ number_format($percentage, 1) }}%
        </div>
        <div class="text-lg font-semibold {{ $colorClass }}">
            {{ $riskLevel }} Risk
        </div>
    </div>
    
    <!-- Risk Meter -->
    <div class="relative mb-6">
        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-4">
            <div class="{{ $bgColorClass }} h-4 rounded-full transition-all duration-500 ease-out" 
                 style="width: {{ $percentage }}%"></div>
        </div>
        <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mt-1">
            <span>0%</span>
            <span>Low</span>
            <span>Medium</span>
            <span>High</span>
            <span>100%</span>
        </div>
    </div>
    
    <!-- Risk Factors -->
    <div class="space-y-3">
        <div class="flex justify-between items-center p-3 bg-amber-50 dark:bg-amber-900/20 rounded-lg">
            <span class="text-sm font-medium text-amber-800 dark:text-amber-200">Location</span>
            <span class="text-sm text-amber-700 dark:text-amber-300">{{ $floodRisk->location }}</span>
        </div>
        
        @if($floodRisk->rainfall_factor)
        <div class="flex justify-between items-center p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
            <span class="text-sm font-medium text-blue-800 dark:text-blue-200">Rainfall Factor</span>
            <span class="text-sm text-blue-700 dark:text-blue-300">{{ $floodRisk->rainfall_factor }}%</span>
        </div>
        @endif
        
        @if($floodRisk->river_level_factor)
        <div class="flex justify-between items-center p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
            <span class="text-sm font-medium text-green-800 dark:text-green-200">River Level</span>
            <span class="text-sm text-green-700 dark:text-green-300">{{ $floodRisk->river_level_factor }}%</span>
        </div>
        @endif
        
        @if($floodRisk->soil_moisture_factor)
        <div class="flex justify-between items-center p-3 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
            <span class="text-sm font-medium text-purple-800 dark:text-purple-200">Soil Moisture</span>
            <span class="text-sm text-purple-700 dark:text-purple-300">{{ $floodRisk->soil_moisture_factor }}%</span>
        </div>
        @endif
    </div>
    
    @if($floodRisk->recommendations)
    <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
        <h4 class="font-semibold text-gray-800 dark:text-gray-200 mb-2">Recommendations</h4>
        <p class="text-sm text-gray-700 dark:text-gray-300">{{ $floodRisk->recommendations }}</p>
    </div>
    @endif
    
    <!-- Alert Level Indicator -->
    <div class="mt-4 flex items-center justify-center">
        @if($percentage >= 80)
            <div class="flex items-center text-red-600 dark:text-red-400">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <span class="font-semibold">ALERT: Take immediate action</span>
            </div>
        @elseif($percentage >= 50)
            <div class="flex items-center text-yellow-600 dark:text-yellow-400">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <span class="font-semibold">WARNING: Monitor conditions</span>
            </div>
        @else
            <div class="flex items-center text-green-600 dark:text-green-400">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span class="font-semibold">SAFE: Low risk conditions</span>
            </div>
        @endif
    </div>
</div>