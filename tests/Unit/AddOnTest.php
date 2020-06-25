<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AddOnTest extends TestCase
{
    use RefreshDatabase;

    public function testAddingAddOnsUpdatesTournamentProfit()
    {
        $tournament = $this->startLiveTournament();
        
        $this->assertEquals(0, $tournament->profit);
        $tournament->addAddOn(50);

        $this->assertEquals(-50, $tournament->fresh()->profit);
        // Add another AddOn of 100.  Profit should now equal -150
        $tournament->addAddOn(100);
        $this->assertEquals(-150, $tournament->fresh()->profit);
    }

    public function testUpdatingAAddOnUpdatesTheTournamentsProfit()
    {
        $tournament = $this->startLiveTournament();
        
        $addOn = $tournament->addAddOn(500);
        $this->assertEquals(-500, $tournament->fresh()->profit);

        $addOn->update([
            'amount' => 1000
        ]);

        // Tournament Profit should equal -1000 instead of -500
        $this->assertEquals(-1000, $tournament->fresh()->profit);
    }

    public function testDeletingAAddOnUpdatesTheTournamentsProfit()
    {
        $tournament = $this->startLiveTournament();
        
        $addOn = $tournament->addAddOn(500);
        $this->assertEquals(-500, $tournament->fresh()->profit);

        $addOn->delete();

        // Tournament Profit should equal 0 instead of -500
        $this->assertEquals(0, $tournament->fresh()->profit);
    }
}
