<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\FetchWeatherDataJob;
use App\Models\WeatherData;

class FetchWeatherDataCommand extends Command
{
    protected $signature = 'weather:fetch 
                            {--location=* : Specific locations to fetch (default: all Rwanda cities)}
                            {--force : Force fetch even if recent data exists}
                            {--forecast : Include forecast data}';

    protected $description = 'Fetch weather data for Rwanda locations and store in database';

    private array $rwandaCities = [
        ['name' => 'Kigali', 'lat' => -1.9441, 'lon' => 30.0619],
        ['name' => 'Huye', 'lat' => -2.5967, 'lon' => 29.7387],
        ['name' => 'Musanze', 'lat' => -1.4999, 'lon' => 29.6357],
        ['name' => 'Rubavu', 'lat' => -1.6792, 'lon' => 29.2678],
        ['name' => 'Nyagatare', 'lat' => -1.2918, 'lon' => 30.3392],
        ['name' => 'Muhanga', 'lat' => -2.0853, 'lon' => 29.7389],
        ['name' => 'Karongi', 'lat' => -2.0069, 'lon' => 29.3265],
        ['name' => 'Kayonza', 'lat' => -1.8833, 'lon' => 30.6167]
    ];

    public function handle(): int
    {
        $this->info('Starting weather data fetch process...');

        $locations = $this->option('location');
        $force = $this->option('force');
        $includeForecast = $this->option('forecast');

        // Use specific locations or all Rwanda cities
        $locationsToFetch = empty($locations) ? $this->rwandaCities : $this->parseLocations($locations);

        if (empty($locationsToFetch)) {
            $this->error('No valid locations found to fetch weather data.');
            return Command::FAILURE;
        }

        $this->withProgressBar($locationsToFetch, function ($location) use ($force, $includeForecast) {
            // Check if recent data exists (within last hour)
            if (!$force && $this->hasRecentData($location)) {
                return;
            }

            // Dispatch job for current weather
            FetchWeatherDataJob::dispatch(
                $location['lat'],
                $location['lon'],
                $location['name'],
                'current'
            );

            // Dispatch job for forecast if requested
            if ($includeForecast) {
                FetchWeatherDataJob::dispatch(
                    $location['lat'],
                    $location['lon'],
                    $location['name'],
                    'forecast'
                );
            }
        });

        $this->newLine(2);
        $this->info('Weather data fetch jobs dispatched successfully!');
        $this->info('Total locations: ' . count($locationsToFetch));

        return Command::SUCCESS;
    }

    private function parseLocations(array $locations): array
    {
        $parsed = [];
        
        foreach ($locations as $location) {
            // Check if it's a predefined city
            $city = collect($this->rwandaCities)->firstWhere('name', ucfirst(strtolower($location)));
            
            if ($city) {
                $parsed[] = $city;
                continue;
            }

            // Try to parse as "name,lat,lon" format
            $parts = explode(',', $location);
            if (count($parts) === 3) {
                $name = trim($parts[0]);
                $lat = floatval(trim($parts[1]));
                $lon = floatval(trim($parts[2]));

                // Validate Rwanda boundaries
                if ($lat >= -2.9 && $lat <= -1.0 && $lon >= 28.8 && $lon <= 30.9) {
                    $parsed[] = ['name' => $name, 'lat' => $lat, 'lon' => $lon];
                } else {
                    $this->warn("Location '{$location}' is outside Rwanda boundaries, skipping.");
                }
            } else {
                $this->warn("Invalid location format: '{$location}'. Use 'name,lat,lon' or predefined city names.");
            }
        }

        return $parsed;
    }

    private function hasRecentData(array $location): bool
    {
        return WeatherData::where('location', $location['name'])
            ->where('latitude', $location['lat'])
            ->where('longitude', $location['lon'])
            ->where('created_at', '>=', now()->subHour())
            ->exists();
    }
}