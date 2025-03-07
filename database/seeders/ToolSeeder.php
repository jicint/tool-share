<?php

namespace Database\Seeders;

use App\Models\Tool;
use App\Models\User;
use Illuminate\Database\Seeder;

class ToolSeeder extends Seeder
{
    public function run()
    {
        // Create sample tools
        $tools = [
            [
                'name' => 'Power Drill',
                'description' => 'Professional grade power drill with variable speed',
                'category' => 'Power Tools',
                'daily_rate' => 25.00,
                'condition' => 'excellent',
            ],
            [
                'name' => 'Lawn Mower',
                'description' => 'Self-propelled gas lawn mower, 21-inch cutting width',
                'category' => 'Garden Tools',
                'daily_rate' => 35.00,
                'condition' => 'good',
            ],
            [
                'name' => 'Circular Saw',
                'description' => '7-1/4 inch circular saw with laser guide',
                'category' => 'Power Tools',
                'daily_rate' => 20.00,
                'condition' => 'good',
            ],
            [
                'name' => 'Pressure Washer',
                'description' => '2000 PSI electric pressure washer',
                'category' => 'Garden Tools',
                'daily_rate' => 40.00,
                'condition' => 'excellent',
            ],
            [
                'name' => 'Ladder',
                'description' => '20ft extension ladder, aluminum',
                'category' => 'Hand Tools',
                'daily_rate' => 15.00,
                'condition' => 'good',
            ]
        ];

        // Get or create a test user
        $user = User::firstOrCreate(
            ['email' => 'test3@example.com'],
            [
                'name' => 'Test User 3',
                'password' => bcrypt('password'),
            ]
        );

        // Create tools
        foreach ($tools as $tool) {
            Tool::create([
                ...$tool,
                'user_id' => $user->id,
                'availability_status' => true
            ]);
        }
    }
} 