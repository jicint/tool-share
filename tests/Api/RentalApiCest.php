<?php

namespace Tests\Api;

use Tests\Support\ApiTester;
use App\Models\User;
use App\Models\Tool;
use App\Models\Rental;
use Illuminate\Support\Facades\DB;

class RentalApiCest
{
    public function _before(ApiTester $I)
    {
        // Clear database
        DB::statement('PRAGMA foreign_keys = OFF');
        foreach(DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name!='sqlite_sequence'") as $table) {
            DB::table($table->name)->truncate();
        }
        DB::statement('PRAGMA foreign_keys = ON');

        $this->user = User::factory()->create();
        $this->toolOwner = User::factory()->create();
        $this->tool = Tool::factory()->create(['user_id' => $this->toolOwner->id]);
        $this->rental = Rental::factory()->create([
            'user_id' => $this->user->id,
            'tool_id' => $this->tool->id,
            'status' => 'active'
        ]);
    }

    public function testReturnToolApi(ApiTester $I)
    {
        $I->amBearerAuthenticated($this->user->createToken('test')->plainTextToken);
        
        $I->sendPOST("/api/rentals/{$this->rental->id}/return");
        
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['message' => 'Tool returned successfully']);
        
        // Use Laravel's query builder to check database
        $rental = Rental::find($this->rental->id);
        expect($rental->status)->equals('completed');
    }

    public function testRateRentalApi(ApiTester $I)
    {
        $this->rental->update(['status' => 'completed']);
        
        $I->amBearerAuthenticated($this->user->createToken('test')->plainTextToken);
        $I->sendPOST("/api/rentals/{$this->rental->id}/rate", [
            'rating' => 5,
            'review' => 'Excellent tool!'
        ]);
        
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['message' => 'Rating submitted successfully']);
        
        $rental = Rental::find($this->rental->id);
        expect($rental->rating)->equals(5);
        expect($rental->review)->equals('Excellent tool!');
    }

    public function testGetRatingStats(ApiTester $I)
    {
        // Create some rated rentals
        Rental::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'tool_id' => $this->tool->id,
            'status' => 'completed',
            'rating' => 5
        ]);

        $I->amBearerAuthenticated($this->user->createToken('test')->plainTextToken);
        $I->sendGET('/api/rentals/rating-stats');
        
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'averageRating' => 5,
            'totalRatings' => 3
        ]);
    }
} 