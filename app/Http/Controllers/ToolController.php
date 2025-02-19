<?php

namespace App\Http\Controllers;

use App\Models\Tool;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class ToolController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'description' => 'required|string',
            'category' => 'required|string',
            'daily_rate' => 'required|numeric|min:0',
            'condition' => 'required|in:excellent,good,fair',
            'availability_status' => 'boolean',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:5120'
        ]);

        $tool = Tool::create([
            'user_id' => auth()->id(),
            ...$validated
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('tools', 'public');
                $tool->images()->create(['image_path' => $path]);
            }
        }

        return response()->json($tool, 201);
    }

    public function index(Request $request)
    {
        $tools = $request->user()->tools()->latest()->get();
        return response()->json($tools);
    }

    public function show(Tool $tool)
    {
        Log::info('Tool show method called', [
            'tool_id' => $tool->id,
            'image_path' => $tool->image_path,
            'user_id' => auth()->id(),
            'tool_user_id' => $tool->user_id
        ]);

        if ($tool->user_id !== auth()->id()) {
            Log::warning('Unauthorized tool access attempt');
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($tool->image_path) {
            $tool->image_url = asset('storage/' . $tool->image_path);
        }

        return response()->json($tool->load('images'));
    }

    public function update(Request $request, Tool $tool)
    {
        // Add more detailed debugging
        Log::info('Update attempt details', [
            'authenticated' => $request->user() ? 'yes' : 'no',
            'auth_user_id' => $request->user() ? $request->user()->id : null,
            'tool_user_id' => $tool->user_id,
            'tool_id' => $tool->id,
            'ids_match' => ($request->user() && $request->user()->id === $tool->user_id) ? 'yes' : 'no'
        ]);

        if ($tool->user_id !== $request->user()->id) {
            Log::info('Authorization failed', [
                'reason' => 'User ID mismatch',
                'auth_user_id' => $request->user()->id,
                'tool_user_id' => $tool->user_id
            ]);
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string',
            'description' => 'sometimes|string',
            'category' => 'sometimes|string',
            'daily_rate' => 'sometimes|numeric|min:0',
            'condition' => 'sometimes|in:excellent,good,fair',
            'availability_status' => 'sometimes|boolean'
        ]);

        $tool->update($validated);

        return response()->json($tool->fresh(), 200);
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