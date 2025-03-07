<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Tool;
use App\Models\Rental;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class RentalControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private $user;
    private $tool;
    private $rental;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user
        $this->user = User::factory()->create();

        // Create a tool owner
        $toolOwner = User::factory()->create();

        // Create test tool
        $this->tool = Tool::factory()->create([
            'user_id' => $toolOwner->id
        ]);

        // Create test rental
        $this->rental = Rental::factory()->create([
            'user_id' => $this->user->id,
            'tool_id' => $this->tool->id,
            'status' => 'active'
        ]);
    }

    public function test_user_can_return_their_rental()
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/rentals/{$this->rental->id}/return");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Tool returned successfully']);

        $this->assertDatabaseHas('rentals', [
            'id' => $this->rental->id,
            'status' => 'completed'
        ]);
    }

    public function test_user_cannot_return_others_rental()
    {
        $otherUser = User::factory()->create();

        $response = $this->actingAs($otherUser)
            ->postJson("/api/rentals/{$this->rental->id}/return");

        $response->assertStatus(403);

        $this->assertDatabaseHas('rentals', [
            'id' => $this->rental->id,
            'status' => 'active'
        ]);
    }

    public function test_user_can_rate_completed_rental()
    {
        $this->rental->update(['status' => 'completed']);

        $response = $this->actingAs($this->user)
            ->postJson("/api/rentals/{$this->rental->id}/rate", [
                'rating' => 5,
                'review' => 'Great tool!'
            ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Rating submitted successfully']);

        $this->assertDatabaseHas('rentals', [
            'id' => $this->rental->id,
            'rating' => 5,
            'review' => 'Great tool!'
        ]);
    }

    public function test_rating_validation()
    {
        $this->rental->update(['status' => 'completed']);

        $response = $this->actingAs($this->user)
            ->postJson("/api/rentals/{$this->rental->id}/rate", [
                'rating' => 6, // Invalid rating
                'review' => 'Great tool!'
            ]);

        $response->assertStatus(422);
    }

    public function test_get_rating_stats()
    {
        // Create multiple rentals with ratings
        Rental::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'tool_id' => $this->tool->id,
            'status' => 'completed',
            'rating' => 5
        ]);

        Rental::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'tool_id' => $this->tool->id,
            'status' => 'completed',
            'rating' => 4
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/rentals/rating-stats');

        $response->assertStatus(200)
            ->assertJson([
                'totalRatings' => 5,
                'ratingDistribution' => [
                    '5' => 3,
                    '4' => 2,
                    '3' => 0,
                    '2' => 0,
                    '1' => 0
                ]
            ]);
    }
} 