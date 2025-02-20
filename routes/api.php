<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ToolController;
use App\Http\Controllers\RentalController;
use App\Models\Tool;
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
    Route::get('tools/{tool}/check', function(Tool $tool) {
        return response()->json([
            'tool_exists' => true,
            'tool_id' => $tool->id
        ]);
    });
    Route::apiResource('tools', ToolController::class);
    Route::post('tools/{tool}/images', [ToolController::class, 'uploadImages']);
    Route::post('/tools/{toolId}/rent', [RentalController::class, 'store'])
        ->name('tools.rent');
    Route::get('/debug/tool/{tool}', function(Tool $tool) {
        return response()->json(['tool' => $tool]);
    });
});

Route::get('/test-tool/{tool}', function (Tool $tool) {
    try {
        $imageExists = false;
        $imageUrl = null;
        
        if ($tool->image_path) {
            $imageExists = Storage::disk('public')->exists($tool->image_path);
            $imageUrl = asset('storage/' . $tool->image_path);
        }

        Log::info('Test tool route', [
            'tool' => $tool->toArray(),
            'exists' => $tool->exists,
            'id' => $tool->id,
            'image_path' => $tool->image_path
        ]);

        return response()->json([
            'tool' => $tool,
            'exists' => $tool->exists,
            'image_exists' => $imageExists,
            'image_url' => $imageUrl
        ]);
    } catch (\Exception $e) {
        Log::error('Test tool error', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

// Add this test route outside middleware to check model binding
Route::get('/test-rental/{tool}', function (Tool $tool) {
    Log::info('Tool binding test', [
        'tool_id' => $tool->id,
        'user_id' => $tool->user_id,
        'exists' => $tool->exists
    ]);
    return response()->json($tool);
});
