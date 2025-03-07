<?php

namespace Database\Factories;

use App\Models\Rental;
use App\Models\Tool;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class RentalFactory extends Factory
{
    protected $model = Rental::class;

    public function definition()
    {
        return [
            'tool_id' => Tool::factory(),
            'user_id' => User::factory(),
            'start_date' => $this->faker->dateTimeBetween('now', '+1 week'),
            'end_date' => $this->faker->dateTimeBetween('+1 week', '+2 weeks'),
            'total_price' => $this->faker->randomFloat(2, 10, 1000),
            'status' => $this->faker->randomElement(['active', 'completed', 'cancelled']),
            'rating' => null,
            'review' => null,
        ];
    }
} 