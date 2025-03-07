<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tool;
use App\Models\User;
use App\Models\Rental;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RentalHistoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    /** @test */
    public function user_can_view_their_rental_history()
    {
        $user = User::factory()->create();
        $tool1 = Tool::factory()->create();
        $tool2 = Tool::factory()->create();

        // Create completed rental
        Rental::create([
            'tool_id' => $tool1->id,
            'user_id' => $user->id,
            'start_date' => now()->subDays(5),
            'end_date' => now()->subDays(2),
            'total_price' => 150.00,
            'status' => 'completed'
        ]);

        // Create active rental
        Rental::create([
            'tool_id' => $tool2->id,
            'user_id' => $user->id,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDays(2),
            'total_price' => 100.00,
            'status' => 'active'
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/rentals/history');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure([
                'data' => [[
                    'id',
                    'tool' => [
                        'id',
                        'name',
                        'category'
                    ],
                    'start_date',
                    'end_date',
                    'total_price',
                    'status'
                ]]
            ]);
    }

    /** @test */
    public function owner_can_view_their_tool_rental_history()
    {
        $owner = User::factory()->create();
        $renter = User::factory()->create();
        $tool = Tool::factory()->create(['user_id' => $owner->id]);

        // Create some rentals for the tool
        Rental::create([
            'tool_id' => $tool->id,
            'user_id' => $renter->id,
            'start_date' => now()->subDays(5),
            'end_date' => now()->subDays(2),
            'total_price' => 150.00,
            'status' => 'completed'
        ]);

        Sanctum::actingAs($owner);

        $response = $this->getJson("/api/tools/{$tool->id}/history");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [[
                    'id',
                    'renter' => [
                        'id',
                        'name'
                    ],
                    'start_date',
                    'end_date',
                    'total_price',
                    'status'
                ]]
            ]);
    }

    /** @test */
    public function non_owner_cannot_view_tool_rental_history()
    {
        $owner = User::factory()->create();
        $nonOwner = User::factory()->create();
        $tool = Tool::factory()->create(['user_id' => $owner->id]);

        Sanctum::actingAs($nonOwner);

        $response = $this->getJson("/api/tools/{$tool->id}/history");

        $response->assertStatus(403);
    }
} 