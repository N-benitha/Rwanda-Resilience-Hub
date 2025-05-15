<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
});

Route::middleware('auth')->group(function () {
    // Logout route
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Flood prediction routes
    Route::prefix('predictions')->group(function () {
        Route::get('/', [FloodPredictionController::class, 'index'])->name('predictions.index');
        Route::post('/location', [FloodPredictionController::class, 'predictByLocation'])->name('predictions.location');
        Route::post('/sensor', [FloodPredictionController::class, 'predictBySensor'])->name('predictions.sensor');
        Route::get('/{prediction}', [FloodPredictionController::class, 'show'])->name('predictions.show');
    });
    
    // Sensor routes available to all authenticated users
    Route::prefix('sensors')->group(function () {
        Route::get('/', [SensorController::class, 'index'])->name('sensors.index');
        Route::get('/create', [SensorController::class, 'create'])->name('sensors.create');
        Route::post('/', [SensorController::class, 'store'])->name('sensors.store');
        Route::get('/{sensor}', [SensorController::class, 'show'])->name('sensors.show');
        Route::get('/{sensor}/edit', [SensorController::class, 'edit'])->name('sensors.edit');
        Route::put('/{sensor}', [SensorController::class, 'update'])->name('sensors.update');
        Route::delete('/{sensor}', [SensorController::class, 'destroy'])->name('sensors.destroy');
        Route::post('/readings', [FloodPredictionController::class, 'storeSensorReading'])->name('sensor.readings.store');
    });
    
    // Reports routes
    Route::prefix('reports')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/create', [ReportController::class, 'create'])->name('reports.create');
        Route::post('/', [ReportController::class, 'store'])->name('reports.store');
        Route::get('/{report}', [ReportController::class, 'show'])->name('reports.show');
        Route::get('/generate/{prediction}', [ReportController::class, 'generateFromPrediction'])->name('reports.generate');
    });
    
    // Admin and Government routes
    Route::middleware(['government'])->prefix('admin')->group(function () {
        // User management (admin only)
        Route::middleware(['admin'])->group(function () {
            Route::get('/users', [UserController::class, 'index'])->name('admin.users.index');
            Route::get('/users/create', [UserController::class, 'create'])->name('admin.users.create');
            Route::post('/users', [UserController::class, 'store'])->name('admin.users.store');
            Route::get('/users/{user}', [UserController::class, 'show'])->name('admin.users.show');
            Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('admin.users.edit');
            Route::put('/users/{user}', [UserController::class, 'update'])->name('admin.users.update');
            Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');
        });
        
        // Monitor routes (admin and government)
        Route::get('/monitor', [DashboardController::class, 'monitor'])->name('admin.monitor');
        
        // All predictions access
        Route::get('/all-predictions', [FloodPredictionController::class, 'adminIndex'])->name('admin.predictions.index');
    });
});

// API endpoints for IoT devices
Route::prefix('api')->group(function () {
    Route::post('/sensor-data', [FloodPredictionController::class, 'apiSensorData']);
});
