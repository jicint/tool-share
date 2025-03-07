<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rental;
use App\Models\Tool;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            
            // Debug log
            Log::info('User requesting dashboard', ['user_id' => $user->id]);

            $data = [
                'totalRentals' => Rental::where('user_id', $user->id)->count(),
                'activeRentals' => Rental::where('user_id', $user->id)
                    ->where('status', 'active')
                    ->count(),
                'recentRentals' => Rental::with(['tool'])
                    ->where('user_id', $user->id)
                    ->orderBy('created_at', 'desc')
                    ->take(5)
                    ->get(),
                'totalSpent' => Rental::where('user_id', $user->id)
                    ->sum('total_price')
            ];

            // Debug log
            Log::info('Dashboard data being sent', $data);

            return response()->json($data);
        } catch (\Exception $e) {
            Log::error('Dashboard error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
} 