<?php

namespace App\Http\Controllers;

use App\Models\Tool;
use App\Models\Rental;
use Illuminate\Http\Request;
use App\Http\Resources\RentalHistoryResource;
use App\Http\Resources\ToolRentalHistoryResource;

class RentalHistoryController extends Controller
{
    public function userHistory(Request $request)
    {
        $rentals = Rental::with('tool')
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return RentalHistoryResource::collection($rentals);
    }

    public function toolHistory(Request $request, $toolId)
    {
        $tool = Tool::findOrFail($toolId);

        if ($request->user()->id !== $tool->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $rentals = Rental::with('user')
            ->where('tool_id', $tool->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return ToolRentalHistoryResource::collection($rentals);
    }
} 