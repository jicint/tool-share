<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rental;
use Illuminate\Http\Request;

class RentalController extends Controller
{
    public function index(Request $request)
    {
        $rentals = Rental::with(['tool', 'user'])
            ->when($request->status, function($query, $status) {
                return $query->where('status', $status);
            })
            ->when($request->user_id, function($query, $userId) {
                return $query->where('user_id', $userId);
            })
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $rentals
        ]);
    }

    public function show(Rental $rental)
    {
        return response()->json([
            'success' => true,
            'data' => $rental->load(['tool', 'user'])
        ]);
    }
}
