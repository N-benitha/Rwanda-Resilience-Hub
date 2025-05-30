<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Cache Store
    |--------------------------------------------------------------------------
    |
    | This option controls the default cache connection that gets used while
    | using this caching library. This connection is used when another is
    | not explicitly specified when executing a given caching function.
    |
    */

    'default' => env('CACHE_DRIVER', 'redis'),

    /*
    |--------------------------------------------------------------------------
    | Cache Stores
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the cache "stores" for your application as
    | well as their drivers. You may even define multiple stores for the
    | same cache driver to group types of items stored in your caches.
    |
    | Supported drivers: "apc", "array", "database", "file",
    |            "memcached", "redis", "dynamodb", "octane", "null"
    |
    */

    'stores' => [

        'apc' => [
            'driver' => 'apc',
        ],

        'array' => [
            'driver' => 'array',
            'serialize' => false,
        ],

        'database' => [
            'driver' => 'database',
            'table' => 'cache',
            'connection' => null,
            'lock_connection' => null,
        ],

        'file' => [
            'driver' => 'file',
            'path' => storage_path('framework/cache/data'),
        ],

        'memcached' => [
            'driver' => 'memcached',
            'persistent_id' => env('MEMCACHED_PERSISTENT_ID'),
            'sasl' => [
                env('MEMCACHED_USERNAME'),
                env('MEMCACHED_PASSWORD'),
            ],
            'options' => [
                // Memcached::OPT_CONNECT_TIMEOUT => 2000,
            ],
            'servers' => [
                [
                    'host' => env('MEMCACHED_HOST', '127.0.0.1'),
                    'port' => env('MEMCACHED_PORT', 11211),
                    'weight' => 100,
                ],
            ],
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => 'cache',
            'lock_connection' => 'default',
        ],

        'dynamodb' => [
            'driver' => 'dynamodb',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'table' => env('DYNAMODB_CACHE_TABLE', 'cache'),
            'endpoint' => env('DYNAMODB_ENDPOINT'),
        ],

        'octane' => [
            'driver' => 'octane',
        ],

        // Weather API specific cache store with longer TTL
        'weather_cache' => [
            'driver' => 'redis',
            'connection' => 'cache',
            'prefix' => 'weather:',
            'lock_connection' => 'default',
        ],

        // Flood prediction cache with medium TTL
        'flood_cache' => [
            'driver' => 'redis',
            'connection' => 'cache',
            'prefix' => 'flood:',
            'lock_connection' => 'default',
        ],

        // Reports cache for frequently accessed reports
        'reports_cache' => [
            'driver' => 'redis',
            'connection' => 'cache',
            'prefix' => 'reports:',
            'lock_connection' => 'default',
        ],

        // Short-term cache for real-time data
        'realtime_cache' => [
            'driver' => 'redis',
            'connection' => 'cache',
            'prefix' => 'realtime:',
            'lock_connection' => 'default',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Key Prefix
    |--------------------------------------------------------------------------
    |
    | When utilizing a RAM based store such as APC or Memcached, there might
    | be other applications utilizing the same cache. So, we'll specify a
    | value to get prefixed to all our keys so we can avoid collisions.
    |
    */

    'prefix' => env('CACHE_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_cache'),

    /*
    |--------------------------------------------------------------------------
    | Cache TTL Configuration
    |--------------------------------------------------------------------------
    |
    | Define default TTL values for different types of cached data
    | in the Rwanda Resilience Hub application.
    |
    */

    'ttl' => [
        // Weather data cache (1 hour as specified)
        'weather_data' => 3600, // 60 minutes
        
        // NASA POWER API data (longer cache due to historical nature)
        'nasa_power_data' => 7200, // 2 hours
        
        // OpenWeatherMap current weather
        'current_weather' => 1800, // 30 minutes
        
        // Weather forecast data
        'weather_forecast' => 3600, // 1 hour
        
        // Flood risk predictions
        'flood_predictions' => 1800, // 30 minutes
        
        // Historical flood data
        'historical_floods' => 86400, // 24 hours
        
        // Reports cache
        'reports' => 7200, // 2 hours
        
        // Dashboard data
        'dashboard_data' => 900, // 15 minutes
        
        // User statistics
        'user_stats' => 3600, // 1 hour
        
        // System alerts
        'system_alerts' => 300, // 5 minutes
        
        // Sensor data aggregates
        'sensor_aggregates' => 1800, // 30 minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Tags Configuration
    |--------------------------------------------------------------------------
    |
    | Define cache tags for better cache invalidation strategies.
    | Tags help group related cache entries for bulk operations.
    |
    */

    'tags' => [
        'weather' => 'weather_data',
        'flood' => 'flood_risk',
        'reports' => 'generated_reports',
        'sensors' => 'sensor_data',
        'users' => 'user_data',
        'dashboard' => 'dashboard_cache',
        'alerts' => 'system_alerts',
        'api' => 'external_api',
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Lock Configuration
    |--------------------------------------------------------------------------
    |
    | Configure cache locks to prevent race conditions when updating
    | cached data, especially important for weather data fetching.
    |
    */

    'locks' => [
        'weather_fetch' => 300, // 5 minutes lock for weather data fetching
        'flood_calculation' => 600, // 10 minutes for flood risk calculations
        'report_generation' => 900, // 15 minutes for report generation
        'sensor_processing' => 180, // 3 minutes for sensor data processing
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Warming Configuration
    |--------------------------------------------------------------------------
    |
    | Define which cache keys should be warmed up during application
    | startup or scheduled maintenance.
    |
    */

    'warming' => [
        'enabled' => env('CACHE_WARMING_ENABLED', true),
        'keys' => [
            'dashboard_summary',
            'current_weather_kigali',
            'active_flood_alerts',
            'system_status',
        ],
    ],

];