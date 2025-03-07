<?php

namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;
use App\Models\User;
use App\Models\Tool;
use App\Models\Rental;
use Illuminate\Support\Facades\Hash;

class RentalCest
{
    public function _before(AcceptanceTester $I)
    {
        // Create test data
        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')  // Use Hash facade
        ]);

        $this->toolOwner = User::factory()->create();
        
        $this->tool = Tool::factory()->create([
            'user_id' => $this->toolOwner->id
        ]);

        $this->rental = Rental::factory()->create([
            'user_id' => $this->user->id,
            'tool_id' => $this->tool->id,
            'status' => 'active'
        ]);
    }

    // ... rest of the test methods remain the same ...
} 