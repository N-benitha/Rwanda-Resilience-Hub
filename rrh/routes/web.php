<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WeatherController;
use App\Http\Controllers\FloodRiskController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\ContactController;
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

Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

// Route::post('/login', [LoginController::class, 'store']);

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    // Dashboard Routes
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard', [
            // 'canManageUsers' => auth()->user()->can('manage-users'),
            // 'canViewReports' => auth()->user()->can('view-reports'),
            // 'canAccessAdmin' => auth()->user()->is_admin,
        ]);
    })->name('dashboard');
    
    // Weather Routes
    Route::prefix('weather')->name('weather.')->group(function () {
        Route::get('/', [WeatherController::class, 'index'])->name('index');
        Route::get('/current/{location?}', [WeatherController::class, 'current'])->name('current');
        Route::get('/forecast/{location?}', [WeatherController::class, 'forecast'])->name('forecast');
        Route::post('/store', [WeatherController::class, 'store'])->name('store');
    });
    
    // Flood Risk Routes
    Route::prefix('flood-risk')->name('flood-risk.')->group(function () {
        Route::get('/', [FloodRiskController::class, 'index'])->name('index');
        Route::get('/assess/{location?}', [FloodRiskController::class, 'assess'])->name('assess');
        Route::post('/calculate', [FloodRiskController::class, 'calculate'])->name('calculate');
        Route::get('/alerts', [FloodRiskController::class, 'alerts'])->name('alerts');
    });
    
    // Report Routes
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/create', [ReportController::class, 'create'])->name('create');
        Route::post('/', [ReportController::class, 'store'])->name('store');
        Route::get('/{report}', [ReportController::class, 'show'])->name('show');
        Route::get('/{report}/download', [ReportController::class, 'download'])->name('download');
        Route::delete('/{report}', [ReportController::class, 'destroy'])->name('destroy');
    });
    
    // Data Collection Routes
    Route::prefix('data-collection')->name('data-collection.')->group(function () {
        Route::get('/', function () {
            return inertia('DataCollection/Index');
        })->name('index');
        
        Route::post('/sensor-data', [WeatherController::class, 'storeSensorData'])->name('sensor-data.store');
        Route::post('/upload-image', [WeatherController::class, 'uploadImage'])->name('upload-image');
    });
    
    // Admin Routes
    Route::middleware(['admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard');
        
        // User Management
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [UserManagementController::class, 'index'])->name('index');
            Route::post('/', [UserManagementController::class, 'store'])->name('store');
            Route::put('/{user}', [UserManagementController::class, 'update'])->name('update');
            Route::delete('/{user}', [UserManagementController::class, 'destroy'])->name('destroy');
            Route::post('/{user}/toggle-status', [UserManagementController::class, 'toggleStatus'])->name('toggle-status');
        });
        
        // System Monitoring
        Route::prefix('monitoring')->name('monitoring.')->group(function () {
            Route::get('/web-monitor', function () {
                return inertia('Admin/WebMonitor');
            })->name('web-monitor');
            
            Route::get('/permissions', function () {
                return inertia('Admin/Permissions');
            })->name('permissions');
        });
        
        // System Settings
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', function () {
                return inertia('Admin/Settings/Index');
            })->name('index');
            
            Route::post('/api-keys', function () {
                // Handle API key updates
            })->name('api-keys.update');
            
            Route::post('/notifications', function () {
                // Handle notification settings
            })->name('notifications.update');
        });
    });
    
    // Resource Routes
    Route::prefix('resources')->name('resources.')->group(function () {
        Route::get('/', function () {
            return inertia('Resources/Index');
        })->name('index');
        
        Route::get('/documentation', function () {
            return inertia('Resources/Documentation');
        })->name('documentation');
        
        Route::get('/api-docs', function () {
            return inertia('Resources/ApiDocs');
        })->name('api-docs');
        
        Route::get('/help', function () {
            return inertia('Resources/Help');
        })->name('help');
    });
});

// Public API endpoints for basic weather data (rate limited)
Route::prefix('public-api')->middleware(['throttle:60,1'])->group(function () {
    Route::get('/weather/current/{location}', [WeatherController::class, 'publicCurrent']);
    Route::get('/flood-risk/status/{location}', [FloodRiskController::class, 'publicStatus']);
});


// Health check endpoint
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now()->toISOString(),
        'services' => [
            'database' => DB::connection()->getPdo() ? 'connected' : 'disconnected',
            'cache' => Cache::store()->getStore() ? 'connected' : 'disconnected',
            'queue' => 'operational'
        ]
    ]);
})->name('health');

// Sitemap
Route::get('/sitemap.xml', function () {
    $sitemap = '<?xml version="1.0" encoding="UTF-8"?>';
    $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    
    // Add main pages
    $pages = [
        route('dashboard'),
        route('weather.index'),
        route('flood-risk.index'),
        route('reports.index')
    ];
    
    foreach ($pages as $url) {
        $sitemap .= '<url>';
        $sitemap .= '<loc>' . $url . '</loc>';
        $sitemap .= '<lastmod>' . now()->format('Y-m-d') . '</lastmod>';
        $sitemap .= '<changefreq>daily</changefreq>';
        $sitemap .= '<priority>0.8</priority>';
        $sitemap .= '</url>';
    }
    
    $sitemap .= '</urlset>';
    
    return response($sitemap)->header('Content-Type', 'application/xml');
})->name('sitemap');