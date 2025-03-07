<?php

namespace App\Http\Controllers;

use App\Models\Tool;
use App\Models\Rental;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RentalController extends Controller
{
    public function store(Request $request)
    {
        // Start transaction
        DB::beginTransaction();

        try {
            // Log the request
            Log::info('1. Rental request received:', [
                'user_id' => auth()->id(),
                'request_data' => $request->all()
            ]);

            // Validate
            $validated = $request->validate([
                'tool_id' => 'required|exists:tools,id',
                'start_date' => 'required|date|after_or_equal:today',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);

            Log::info('2. Validation passed:', $validated);

            // Find tool
            $tool = Tool::findOrFail($validated['tool_id']);
            Log::info('3. Tool found:', $tool->toArray());

            // Calculate rental details
            $start = Carbon::parse($validated['start_date']);
            $end = Carbon::parse($validated['end_date']);
            $days = $end->diffInDays($start) + 1;
            $total_cost = $tool->daily_rate * $days;

            Log::info('4. Calculated rental details:', [
                'days' => $days,
                'total_cost' => $total_cost
            ]);

            // Create rental using DB facade
            $rentalId = DB::table('rentals')->insertGetId([
                'tool_id' => $tool->id,
                'user_id' => auth()->id(),
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'total_cost' => $total_cost,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            Log::info('5. Rental created with ID:', ['rental_id' => $rentalId]);

            // Verify rental exists
            $rental = DB::table('rentals')->find($rentalId);
            Log::info('6. Rental verification:', [
                'found' => $rental ? true : false,
                'rental' => $rental
            ]);

            if (!$rental) {
                throw new \Exception('Failed to create rental record');
            }

            // Commit transaction
            DB::commit();

            // Debug: Check all rentals
            $allRentals = DB::table('rentals')->get();
            Log::info('7. All rentals in database:', [
                'count' => $allRentals->count(),
                'rentals' => $allRentals->toArray()
            ]);

            // Redirect with success message and rental ID
            return redirect()->route('dashboard')
                ->with('success', "Rental request #{$rentalId} submitted successfully! Check My Rentals tab.");

        } catch (\Exception $e) {
            // Rollback transaction
            DB::rollBack();

            Log::error('ERROR creating rental:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('dashboard')
                ->with('error', 'Error creating rental: ' . $e->getMessage());
        }
    }

    public function update(Request $request, Rental $rental)
    {
        // Debug: Log the update request
        \Log::info('Rental update request:', [
            'rental_id' => $rental->id,
            'status' => $request->status
        ]);

        if ($rental->tool->user_id !== auth()->id()) {
            return redirect()->route('dashboard')
                ->with('error', 'You are not authorized to update this rental.');
        }

        $validated = $request->validate([
            'status' => 'required|in:approved,rejected'
        ]);

        $rental->status = $validated['status'];
        $rental->save();

        // Debug: Log the updated rental
        \Log::info('Rental updated:', $rental->toArray());

        return redirect()->route('dashboard')
            ->with('success', 'Rental request ' . $validated['status'] . ' successfully.');
    }

    public function checkAvailability($toolId)
    {
        $tool = Tool::findOrFail($toolId);
        $today = now()->startOfDay();
        
        // Find active rentals that have ended (including today)
        $endedRentals = Rental::where('tool_id', $tool->id)
            ->where('status', 'active')
            ->whereDate('end_date', '<=', $today)
            ->get();

        Log::info('Checking availability', [
            'tool_id' => $tool->id,
            'ended_rentals' => $endedRentals->count(),
            'current_status' => $tool->availability_status,
            'current_date' => $today->format('Y-m-d')
        ]);

        // Update rental status and tool availability
        if ($endedRentals->isNotEmpty()) {
            foreach ($endedRentals as $rental) {
                $rental->update(['status' => 'completed']);
            }

            // Check if there are any remaining active rentals
            $hasActiveRentals = Rental::where('tool_id', $tool->id)
                ->where('status', 'active')
                ->whereDate('start_date', '>', $today)
                ->exists();

            // If no active future rentals, make tool available
            if (!$hasActiveRentals) {
                $tool->update(['availability_status' => true]);
                $tool->refresh();
            }
        }

        return response()->json([
            'is_available' => $tool->availability_status,
            'next_available_date' => $this->getNextAvailableDate($tool)
        ]);
    }

    private function getNextAvailableDate(Tool $tool)
    {
        $latestRental = Rental::where('tool_id', $tool->id)
            ->where('status', 'active')
            ->orderBy('end_date', 'desc')
            ->first();

        return $latestRental ? $latestRental->end_date : now()->format('Y-m-d');
    }

    public function index(Request $request)
    {
        $rentals = Rental::with(['tool.user'])
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($rentals);
    }

    public function return($id, Request $request)
    {
        try {
            $rental = Rental::findOrFail($id);
            
            if ($rental->user_id !== $request->user()->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            if ($rental->status !== 'active') {
                return response()->json(['error' => 'Rental is not active'], 400);
            }

            $rental->update([
                'status' => 'completed',
                'returned_at' => now()
            ]);

            return response()->json(['message' => 'Tool returned successfully']);
        } catch (\Exception $e) {
            Log::error('Error returning tool', [
                'error' => $e->getMessage(),
                'rental_id' => $id
            ]);
            return response()->json(['error' => 'Failed to return tool'], 500);
        }
    }

    public function rate($id, Request $request)
    {
        try {
            $rental = Rental::findOrFail($id);
            
            if ($rental->user_id !== $request->user()->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            if ($rental->status !== 'completed') {
                return response()->json(['error' => 'Can only rate completed rentals'], 400);
            }

            $validated = $request->validate([
                'rating' => 'required|integer|min:1|max:5',
                'review' => 'nullable|string|max:500'
            ]);

            $rental->update([
                'rating' => $validated['rating'],
                'review' => $validated['review']
            ]);

            return response()->json(['message' => 'Rating submitted successfully']);
        } catch (\Exception $e) {
            Log::error('Error rating rental', [
                'error' => $e->getMessage(),
                'rental_id' => $id
            ]);
            return response()->json(['error' => 'Failed to submit rating'], 500);
        }
    }

    public function getRatingStats(Request $request)
    {
        try {
            $user = $request->user();
            
            $rentals = Rental::where('user_id', $user->id)
                ->whereNotNull('rating')
                ->get();
            
            $totalRatings = $rentals->count();
            $averageRating = $totalRatings > 0 ? round($rentals->avg('rating'), 2) : 0;
            
            $distribution = [
                5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0
            ];
            
            foreach ($rentals as $rental) {
                $distribution[$rental->rating]++;
            }
            
            return response()->json([
                'averageRating' => $averageRating,
                'totalRatings' => $totalRatings,
                'ratingDistribution' => $distribution
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting rating stats', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()->id
            ]);
            return response()->json(['error' => 'Failed to get rating statistics'], 500);
        }
    }
} 