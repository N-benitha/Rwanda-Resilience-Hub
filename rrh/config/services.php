<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'openweathermap' => [
        'api_key' => env('OPENWEATHERMAP_API_KEY'),
        'base_url' => 'https://api.openweathermap.org/data/2.5/',
        'one_call_url' => 'https://api.openweathermap.org/data/3.0/onecall',
    ],

    'nasa_power' => [
        'base_url' => 'https://power.larc.nasa.gov/api/temporal/daily/point',
        'parameters' => 'PRECTOTCORR,T2M,RH2M,WS10M,PS',
    ],

    'groq' => [
        'api_key' => env('GROQ_API_KEY'),
        'base_url' => 'https://api.groq.com/openai/v1',
        'model' => 'llama3-8b-8192',
    ],

];
