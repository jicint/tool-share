<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ToolController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::apiResource('tools', ToolController::class);
    Route::post('tools/{tool}/images', [ToolController::class, 'uploadImages']);
});

Route::get('/test-tool/{tool}', function (App\Models\Tool $tool) {
    Log::info('Test tool route', [
        'tool' => $tool->toArray(),
        'image_exists' => Storage::disk('public')->exists($tool->image_path),
        'full_url' => asset('storage/' . $tool->image_path)
    ]);
    return response()->json([
        'tool' => $tool,
        'image_exists' => Storage::disk('public')->exists($tool->image_path),
        'image_url' => asset('storage/' . $tool->image_path)
    ]);
});
