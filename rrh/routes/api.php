<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WeatherController;
use App\Http\Controllers\FloodRiskController;
use App\Http\Controllers\ReportController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Weather API Routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::prefix('weather')->group(function () {
        Route::get('/', [WeatherController::class, 'index']);
        Route::get('/current/{location}', [WeatherController::class, 'getCurrentWeather']);
        Route::get('/forecast/{location}', [WeatherController::class, 'getForecast']);
        Route::get('/historical', [WeatherController::class, 'getHistorical']);
        Route::get('/locations', [WeatherController::class, 'getLocations']);
    });

    // Flood Risk API Routes
    Route::prefix('flood-risk')->group(function () {
        Route::get('/', [FloodRiskController::class, 'index']);
        Route::get('/{id}', [FloodRiskController::class, 'show']);
        Route::post('/calculate', [FloodRiskController::class, 'calculate']);
        Route::get('/alerts/active', [FloodRiskController::class, 'getActiveAlerts']);
        Route::get('/risk-levels', [FloodRiskController::class, 'getRiskLevels']);
    });

    // Reports API Routes
    Route::prefix('reports')->group(function () {
        Route::get('/', [ReportController::class, 'index']);
        Route::get('/{id}', [ReportController::class, 'show']);
        Route::post('/', [ReportController::class, 'store']);
        Route::get('/export/{type}', [ReportController::class, 'export']);
        Route::get('/statistics', [ReportController::class, 'getStatistics']);
    });

    // Data export endpoints
    Route::prefix('export')->group(function () {
        Route::get('/weather/{format}', [WeatherController::class, 'export']);
        Route::get('/flood-risk/{format}', [FloodRiskController::class, 'export']);
    });
});

// Public API endpoints (rate limited)
Route::middleware(['throttle:60,1'])->group(function () {
    Route::get('/public/weather/current', [WeatherController::class, 'getPublicWeather']);
    Route::get('/public/flood-alerts', [FloodRiskController::class, 'getPublicAlerts']);
});