<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tool;
use App\Models\User;
use App\Models\Rental;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;

class ToolAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    /** @test */
    public function tool_becomes_unavailable_after_rental()
    {
        $owner = User::factory()->create();
        $renter = User::factory()->create();
        
        $tool = Tool::factory()->create([
            'user_id' => $owner->id,
            'availability_status' => true
        ]);

        Sanctum::actingAs($renter);

        $response = $this->postJson("/api/tools/{$tool->id}/rent", [
            'start_date' => now()->addDay()->format('Y-m-d'),
            'end_date' => now()->addDays(3)->format('Y-m-d'),
            'total_price' => 150.00
        ]);

        $response->assertStatus(201);
        
        $this->assertFalse(Tool::find($tool->id)->availability_status);
    }

    /** @test */
    public function tool_becomes_available_after_rental_ends()
    {
        $owner = User::factory()->create();
        $renter = User::factory()->create();
        
        $tool = Tool::factory()->create([
            'user_id' => $owner->id,
            'availability_status' => true
        ]);

        Sanctum::actingAs($renter);

        // Create a rental that ends today
        $rental = Rental::create([
            'tool_id' => $tool->id,
            'user_id' => $renter->id,
            'start_date' => now()->subDays(3)->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
            'total_price' => 150.00,
            'status' => 'active'
        ]);

        // Mark tool as unavailable
        $tool->update(['availability_status' => false]);

        // Call endpoint to check and update rental status
        $response = $this->getJson("/api/tools/{$tool->id}/check-availability");
        
        $response->assertOk()
            ->assertJsonStructure([
                'is_available',
                'next_available_date'
            ]);

        $this->assertTrue(Tool::find($tool->id)->availability_status);
    }

    /** @test */
    public function cannot_rent_tool_with_overlapping_rental_period()
    {
        $owner = User::factory()->create();
        $renter1 = User::factory()->create();
        $renter2 = User::factory()->create();
        
        $tool = Tool::factory()->create([
            'user_id' => $owner->id,
            'availability_status' => true
        ]);

        // Create first rental
        Sanctum::actingAs($renter1);
        $this->postJson("/api/tools/{$tool->id}/rent", [
            'start_date' => now()->addDays(1)->format('Y-m-d'),
            'end_date' => now()->addDays(5)->format('Y-m-d'),
            'total_price' => 150.00
        ]);

        // Try to create overlapping rental
        Sanctum::actingAs($renter2);
        $response = $this->postJson("/api/tools/{$tool->id}/rent", [
            'start_date' => now()->addDays(3)->format('Y-m-d'),
            'end_date' => now()->addDays(7)->format('Y-m-d'),
            'total_price' => 150.00
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('error', 'Tool is not available for the selected dates');
    }
} 