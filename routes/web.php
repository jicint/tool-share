<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;

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

Route::get('/', function () {
    return view('welcome');
});

Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
