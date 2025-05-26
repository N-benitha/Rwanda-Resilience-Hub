@props(['weather'])

<div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 border border-amber-200 dark:border-amber-700">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-amber-900 dark:text-amber-100">
            {{ $weather->location ?? 'Weather Data' }}
        </h3>
        <span class="text-sm text-amber-700 dark:text-amber-300">
            {{ $weather->created_at->format('M j, Y H:i') }}
        </span>
    </div>
    
    <div class="grid grid-cols-2 gap-4">
        <div class="text-center p-3 bg-amber-50 dark:bg-amber-900/20 rounded-lg">
            <div class="text-2xl font-bold text-amber-800 dark:text-amber-200">
                {{ round($weather->temperature) }}°C
            </div>
            <div class="text-sm text-amber-600 dark:text-amber-400">Temperature</div>
        </div>
        
        <div class="text-center p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
            <div class="text-2xl font-bold text-blue-800 dark:text-blue-200">
                {{ $weather->humidity }}%
            </div>
            <div class="text-sm text-blue-600 dark:text-blue-400">Humidity</div>
        </div>
        
        <div class="text-center p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
            <div class="text-2xl font-bold text-green-800 dark:text-green-200">
                {{ $weather->rainfall }} mm
            </div>
            <div class="text-sm text-green-600 dark:text-green-400">Rainfall</div>
        </div>
        
        <div class="text-center p-3 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
            <div class="text-2xl font-bold text-purple-800 dark:text-purple-200">
                {{ $weather->wind_speed }} km/h
            </div>
            <div class="text-sm text-purple-600 dark:text-purple-400">Wind Speed</div>
        </div>
    </div>
    
    @if($weather->description)
    <div class="mt-4 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
        <p class="text-sm text-gray-700 dark:text-gray-300">{{ $weather->description }}</p>
    </div>
    @endif
</div>