<?php

namespace App\Http\Controllers;

use App\Models\Tool;
use App\Models\Rental;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RentalController extends Controller
{
    public function store(Request $request, $toolId)
    {
        // Explicitly get the tool
        $tool = Tool::findOrFail($toolId);

        Log::info('Rental attempt', [
            'tool_id' => $tool->id,
            'tool_user_id' => $tool->user_id,
            'requester_id' => $request->user()->id,
            'is_owner' => $tool->user_id === $request->user()->id,
            'tool_available' => $tool->availability_status
        ]);

        // 1. Check if user is trying to rent their own tool
        if ($request->user()->id === $tool->user_id) {
            Log::info('Self rental blocked');
            return response()->json(['error' => 'Cannot rent your own tool'], 403);
        }

        // 2. Check if tool is available
        if (!$tool->availability_status) {
            Log::info('Unavailable tool rental blocked');
            return response()->json(['error' => 'Tool is not available'], 422);
        }

        // 3. Validate the request data
        $validated = $request->validate([
            'start_date' => 'required|date|after:today',
            'end_date' => 'required|date|after:start_date',
            'total_price' => 'required|numeric|min:0'
        ]);

        // 4. Create the rental
        $rental = Rental::create([
            'tool_id' => $tool->id,
            'user_id' => $request->user()->id,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'total_price' => $validated['total_price'],
            'status' => 'pending'
        ]);

        // 5. Update tool availability
        $tool->update(['availability_status' => false]);

        return response()->json($rental, 201);
    }
} 