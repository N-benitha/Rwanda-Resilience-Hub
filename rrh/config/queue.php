<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Queue Connection Name
    |--------------------------------------------------------------------------
    |
    | Laravel's queue API supports an assortment of back-ends via a single
    | API, giving you convenient access to each back-end using the same
    | syntax for every one. Here you may define a default connection.
    |
    */

    'default' => env('QUEUE_CONNECTION', 'database'),

    /*
    |--------------------------------------------------------------------------
    | Queue Connections
    |--------------------------------------------------------------------------
    |
    | Here you may configure the connection information for each server that
    | is used by your application. A default configuration has been added
    | for each back-end shipped with Laravel. You are free to add more.
    |
    | Drivers: "sync", "database", "beanstalkd", "sqs", "redis", "null"
    |
    */

    'connections' => [

        'sync' => [
            'driver' => 'sync',
        ],

        'database' => [
            'driver' => 'database',
            'table' => 'jobs',
            'queue' => 'default',
            'retry_after' => 90,
            'after_commit' => false,
        ],

        'beanstalkd' => [
            'driver' => 'beanstalkd',
            'host' => 'localhost',
            'queue' => 'default',
            'retry_after' => 90,
            'block_for' => 0,
            'after_commit' => false,
        ],

        'sqs' => [
            'driver' => 'sqs',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'prefix' => env('SQS_PREFIX', 'https://sqs.us-east-1.amazonaws.com/your-account-id'),
            'queue' => env('SQS_QUEUE', 'default'),
            'suffix' => env('SQS_SUFFIX'),
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'after_commit' => false,
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
            'queue' => env('REDIS_QUEUE', 'default'),
            'retry_after' => 90,
            'block_for' => null,
            'after_commit' => false,
        ],

        // Weather data processing queue
        'weather' => [
            'driver' => 'database',
            'table' => 'jobs',
            'queue' => 'weather',
            'retry_after' => 180,
            'after_commit' => false,
        ],

        // Flood risk processing queue
        'flood_risk' => [
            'driver' => 'database',
            'table' => 'jobs',
            'queue' => 'flood_risk',
            'retry_after' => 300,
            'after_commit' => false,
        ],

        // Report generation queue
        'reports' => [
            'driver' => 'database',
            'table' => 'jobs',
            'queue' => 'reports',
            'retry_after' => 600,
            'after_commit' => false,
        ],

        // High priority queue for critical alerts
        'alerts' => [
            'driver' => 'database',
            'table' => 'jobs',
            'queue' => 'alerts',
            'retry_after' => 60,
            'after_commit' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Job Batching
    |--------------------------------------------------------------------------
    |
    | The following options configure the database and table that store job
    | batching information. These options can be updated to any database
    | connection and table which has been defined by your application.
    |
    */

    'batching' => [
        'database' => env('DB_CONNECTION', 'pgsql'),
        'table' => 'job_batches',
    ],

    /*
    |--------------------------------------------------------------------------
    | Failed Queue Jobs
    |--------------------------------------------------------------------------
    |
    | These options configure the behavior of failed queue job logging so you
    | can control which database and table are used to store the jobs that
    | have failed. You may change them to any database / table you wish.
    |
    */

    'failed' => [
        'driver' => env('QUEUE_FAILED_DRIVER', 'database'),
        'database' => env('DB_CONNECTION', 'pgsql'),
        'table' => 'failed_jobs',
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Worker Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure the queue worker behavior including timeouts,
    | memory limits, and sleep duration when no jobs are available.
    |
    */

    'worker' => [
        'sleep' => 3,
        'timeout' => 60,
        'memory' => 128,
        'tries' => 3,
        'force' => false,
        'stop_when_empty' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Processing Priorities
    |--------------------------------------------------------------------------
    |
    | Define the priority order for queue processing. Higher priority queues
    | will be processed first when multiple queues have pending jobs.
    |
    */

    'priorities' => [
        'alerts' => 10,      // Highest priority for critical flood alerts
        'flood_risk' => 8,   // High priority for risk calculations
        'weather' => 5,      // Medium priority for weather data
        'reports' => 3,      // Lower priority for report generation
        'default' => 1,      // Lowest priority for general tasks
    ],

    /*
    |--------------------------------------------------------------------------
    | Retry Configuration
    |--------------------------------------------------------------------------
    |
    | Configure retry behavior for different types of jobs.
    |
    */

    'retry' => [
        'weather_fetch' => [
            'tries' => 5,
            'backoff' => [60, 120, 300, 600, 1200], // Exponential backoff in seconds
        ],
        'flood_risk_calculation' => [
            'tries' => 3,
            'backoff' => [30, 90, 180],
        ],
        'report_generation' => [
            'tries' => 2,
            'backoff' => [300, 900],
        ],
        'alert_notification' => [
            'tries' => 5,
            'backoff' => [10, 30, 60, 120, 300],
        ],
    ],

];