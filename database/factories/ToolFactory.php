<?php

namespace Database\Factories;

use App\Models\Tool;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ToolFactory extends Factory
{
    protected $model = Tool::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'name' => $this->faker->word,
            'description' => $this->faker->sentence,
            'category' => $this->faker->randomElement([
                'Power Tools',
                'Hand Tools',
                'Garden Tools'
            ]),
            'daily_rate' => $this->faker->randomFloat(2, 5, 100),
            'condition' => $this->faker->randomElement([
                'excellent',
                'good',
                'fair'
            ]),
            'availability_status' => true
        ];
    }
} 