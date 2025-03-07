<?php

namespace App\Http\Controllers;

use App\Models\Tool;
use App\Models\Rental;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ToolController extends Controller
{
    public function create()
    {
        return view('tools.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string',
            'condition' => 'required|string',
            'daily_rate' => 'required|numeric|min:0'
        ]);

        $tool = auth()->user()->tools()->create($validated);

        return redirect()->route('dashboard')->with('success', 'Tool added successfully!');
    }

    public function index()
    {
        // Get current user
        $user = auth()->user();
        Log::info('1. Current user:', [
            'id' => $user->id,
            'email' => $user->email
        ]);

        // Get tools
        $tools = Tool::with('user')->latest()->get();
        Log::info('2. Tools found:', ['count' => $tools->count()]);

        // Check rentals table directly using DB facade
        $dbRentals = DB::table('rentals')
            ->where('user_id', $user->id)
            ->get();
        Log::info('3. Raw rentals from DB:', [
            'count' => $dbRentals->count(),
            'rentals' => $dbRentals->toArray()
        ]);

        // Get rentals using Eloquent
        $rentals = Rental::where('user_id', $user->id)
            ->with(['tool', 'tool.user'])
            ->latest()
            ->get();
        Log::info('4. Eloquent rentals:', [
            'count' => $rentals->count(),
            'rentals' => $rentals->toArray()
        ]);

        // Get received rentals
        $receivedRentals = Rental::whereHas('tool', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })->with(['tool', 'user'])->latest()->get();

        // Debug view data
        Log::info('5. Data being passed to view:', [
            'tools_count' => $tools->count(),
            'rentals_count' => $rentals->count(),
            'received_rentals_count' => $receivedRentals->count()
        ]);

        // Check if rentals are actually in the database
        $allRentals = DB::select('SELECT * FROM rentals');
        Log::info('6. All rentals in database:', [
            'count' => count($allRentals),
            'rentals' => $allRentals
        ]);

        return view('dashboard', compact('tools', 'rentals', 'receivedRentals'));
    }

    public function show($id)
    {
        $tool = Tool::findOrFail($id);
        return response()->json([
            'id' => $tool->id,
            'name' => $tool->name,
            'description' => $tool->description,
            'category' => $tool->category,
            'daily_rate' => $tool->daily_rate,
            'condition' => $tool->condition,
            'availability_status' => $tool->availability_status,
            'user_id' => $tool->user_id
        ]);
    }

    public function update(Request $request, Tool $tool)
    {
        if ($tool->user_id !== auth()->id()) {
            return redirect()->route('dashboard')
                ->with('error', 'You are not authorized to edit this tool.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string',
            'condition' => 'required|string',
            'daily_rate' => 'required|numeric|min:0'
        ]);

        $tool->update($validated);

        return redirect()->route('dashboard')
            ->with('success', 'Tool updated successfully!');
    }

    public function destroy(Tool $tool)
    {
        // Check if the authenticated user owns the tool
        if ($tool->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Delete image if exists
        if ($tool->image_path) {
            Storage::disk('public')->delete($tool->image_path);
        }

        $tool->delete();
        return response()->json(['message' => 'Tool deleted successfully']);
    }
} 