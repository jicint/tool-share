<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tool;
use App\Models\User;
use App\Models\Rental;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;

class ToolRentalTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_rent_an_available_tool()
    {
        $owner = User::factory()->create();
        $renter = User::factory()->create();
        
        $tool = Tool::factory()->create([
            'user_id' => $owner->id,
            'availability_status' => true,
            'daily_rate' => 50.00
        ]);

        Log::info('Test setup', [
            'tool_exists' => Tool::find($tool->id) ? true : false,
            'tool_id' => $tool->id,
            'owner_id' => $owner->id,
            'renter_id' => $renter->id,
            'url' => "/api/tools/{$tool->id}/rent"
        ]);

        Sanctum::actingAs($renter);

        $response = $this->postJson("/api/tools/{$tool->id}/rent", [
            'start_date' => now()->addDay()->format('Y-m-d'),
            'end_date' => now()->addDays(3)->format('Y-m-d'),
            'total_price' => $tool->daily_rate * 3
        ]);

        if ($response->status() !== 201) {
            Log::error('Test failed', [
                'status' => $response->status(),
                'content' => $response->content(),
                'headers' => $response->headers->all()
            ]);
        }

        $response->assertStatus(201);
        
        $this->assertDatabaseHas('rentals', [
            'tool_id' => $tool->id,
            'user_id' => $renter->id,
        ]);
    }

    /** @test */
    public function user_cannot_rent_their_own_tool()
    {
        $owner = User::factory()->create();
        
        $tool = Tool::factory()->create([
            'user_id' => $owner->id,
            'availability_status' => true
        ]);

        Sanctum::actingAs($owner); // Try to rent own tool

        $response = $this->postJson("/api/tools/{$tool->id}/rent", [
            'start_date' => now()->addDay()->format('Y-m-d'),
            'end_date' => now()->addDays(3)->format('Y-m-d')
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function user_cannot_rent_unavailable_tool()
    {
        $owner = User::factory()->create();
        $renter = User::factory()->create();
        
        $tool = Tool::factory()->create([
            'user_id' => $owner->id,
            'availability_status' => false // Tool is not available
        ]);

        Sanctum::actingAs($renter);

        $response = $this->postJson("/api/tools/{$tool->id}/rent", [
            'start_date' => now()->addDay()->format('Y-m-d'),
            'end_date' => now()->addDays(3)->format('Y-m-d')
        ]);

        $response->assertStatus(422);
    }
} 