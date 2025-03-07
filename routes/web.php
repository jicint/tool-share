<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\ToolController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RentalController;
use App\Http\Controllers\AdminAuthController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/test-log', function () {
    try {
        Log::info('Test log entry');
        $logPath = storage_path('logs/laravel.log');
        dd([
            'Log written',
            'Log path' => $logPath,
            'File exists' => file_exists($logPath),
            'Is writable' => is_writable($logPath),
            'Log content' => file_get_contents($logPath)
        ]);
    } catch (\Exception $e) {
        dd($e->getMessage());
    }
});

Route::middleware(['web'])->group(function () {
    Route::get('/', function () {
        return view('welcome');
    });

    // Guest routes (login)
    Route::middleware('guest')->group(function () {
        Route::get('login', [AuthenticatedSessionController::class, 'create'])
            ->name('login');
        Route::post('login', [AuthenticatedSessionController::class, 'store']);
    });

    // Authenticated routes
    Route::middleware('auth')->group(function () {
        Route::get('/dashboard', [ToolController::class, 'index'])->name('dashboard');
        Route::post('/tools', [ToolController::class, 'store'])->name('tools.store');
        Route::put('/tools/{tool}', [ToolController::class, 'update'])->name('tools.update');
        Route::post('/rentals', [RentalController::class, 'store'])->name('rentals.store');
        Route::put('/rentals/{rental}', [RentalController::class, 'update'])->name('rentals.update');
        Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
        Route::get('/tools/create', [ToolController::class, 'create'])->name('tools.create');
    });

    // Admin routes
    Route::prefix('admin')->group(function () {
        Route::get('login', [AdminAuthController::class, 'create'])
            ->name('admin.login');
        
        Route::post('login', [AdminAuthController::class, 'store']);
    });
});

// Comment out this line if you have auth routes defined above
// require __DIR__.'/auth.php';
