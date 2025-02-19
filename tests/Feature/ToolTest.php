<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tool;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ToolTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /** @test */
    public function user_can_create_a_tool()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        
        $response = $this->postJson('/api/tools', [
            'name' => 'Power Drill',
            'description' => 'Professional grade power drill',
            'category' => 'Power Tools',
            'daily_rate' => 15.00,
            'condition' => 'good',
            'availability_status' => true
        ]);

        $response->assertStatus(201)
                ->assertJson([
                    'name' => 'Power Drill',
                    'category' => 'Power Tools'
                ]);

        $this->assertDatabaseHas('tools', [
            'name' => 'Power Drill',
            'daily_rate' => 15.00
        ]);
    }

    /** @test */
    public function user_can_upload_tool_images()
    {
        $user = User::factory()->create();
        $image = UploadedFile::fake()->image('tool.jpg');

        $response = $this->actingAs($user)->postJson('/api/tools', [
            'name' => 'Hammer',
            'description' => 'Heavy duty hammer',
            'category' => 'Hand Tools',
            'daily_rate' => 5.00,
            'condition' => 'excellent',
            'availability_status' => true,
            'images' => [$image]
        ]);

        $response->assertStatus(201);
        $this->assertTrue(Storage::disk('public')->exists('tools/' . $image->hashName()));
    }

    /** @test */
    public function user_can_update_tool_details()
    {
        $user = User::factory()->create();
        $tool = Tool::factory()->create(['user_id' => $user->id]);
        
        Sanctum::actingAs($user);

        // Add debugging
        Log::info('Test data', [
            'user_id' => $user->id,
            'tool_user_id' => $tool->user_id,
            'tool_id' => $tool->id
        ]);

        $response = $this->putJson("/api/tools/{$tool->id}", [
            'name' => 'Updated Tool Name',
            'daily_rate' => 25.00
        ]);

        // Add response debugging
        Log::info('Response data', [
            'status' => $response->status(),
            'content' => $response->content()
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('tools', [
            'id' => $tool->id,
            'name' => 'Updated Tool Name',
            'daily_rate' => 25.00
        ]);
    }

    /** @test */
    public function user_cannot_update_others_tools()
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $tool = Tool::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($otherUser)->putJson("/api/tools/{$tool->id}", [
            'name' => 'Hacked Tool'
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function tool_requires_valid_category()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->postJson('/api/tools', [
            'name' => 'Invalid Tool',
            'description' => 'Test description',
            'category' => 'Invalid Category',
            'daily_rate' => 10.00
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['category']);
    }

    /** @test */
    public function tool_requires_valid_daily_rate()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->postJson('/api/tools', [
            'name' => 'Expensive Tool',
            'description' => 'Test description',
            'category' => 'Hand Tools',
            'daily_rate' => -10.00
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['daily_rate']);
    }

    /** @test */
    public function user_can_delete_their_tool()
    {
        $user = User::factory()->create();
        $tool = Tool::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->deleteJson("/api/tools/{$tool->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('tools', ['id' => $tool->id]);
    }

    /** @test */
    public function user_can_filter_tools_by_category()
    {
        $user = User::factory()->create();
        $powerTool = Tool::factory()->create(['category' => 'Power Tools']);
        $handTool = Tool::factory()->create(['category' => 'Hand Tools']);

        $response = $this->actingAs($user)->getJson('/api/tools?category=Power Tools');

        $response->assertStatus(200)
                ->assertJsonCount(1)
                ->assertJsonFragment(['category' => 'Power Tools'])
                ->assertJsonMissing(['category' => 'Hand Tools']);
    }

    /** @test */
    public function user_can_search_tools()
    {
        $user = User::factory()->create();
        $drill = Tool::factory()->create(['name' => 'Power Drill']);
        $saw = Tool::factory()->create(['name' => 'Hand Saw']);

        $response = $this->actingAs($user)->getJson('/api/tools?search=drill');

        $response->assertStatus(200)
                ->assertJsonCount(1)
                ->assertJsonFragment(['name' => 'Power Drill']);
    }

    /** @test */
    public function tool_images_are_limited()
    {
        $user = User::factory()->create();
        $images = [];
        
        for ($i = 0; $i < 6; $i++) {
            $images[] = UploadedFile::fake()->image("tool{$i}.jpg");
        }

        $response = $this->actingAs($user)->postJson('/api/tools', [
            'name' => 'Multi Image Tool',
            'description' => 'Test description',
            'category' => 'Hand Tools',
            'daily_rate' => 10.00,
            'images' => $images
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['images']);
    }
} 