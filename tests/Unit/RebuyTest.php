<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RebuyTest extends TestCase
{
    use RefreshDatabase;

    public function testAddingRebuysUpdatesTournamentProfit()
    {
        $user = factory('App\User')->create();

        $tournament = $user->startTournament();
        $this->assertEquals(0, $tournament->profit);
        $tournament->addRebuy(100);
        $this->assertEquals(-100, $tournament->fresh()->profit);
        // Add another rebuy of 100.  Profit should now equal -200
        $tournament->addRebuy(100);
        $this->assertEquals(-200, $tournament->fresh()->profit);
    }

    public function testUpdatingARebuyUpdatesTheTournamentsProfit()
    {
        $user = factory('App\User')->create();

        $tournament = $user->startTournament();
        $rebuy = $tournament->addRebuy(500);
        $this->assertEquals(-500, $tournament->fresh()->profit);

        $rebuy->update([
            'amount' => 1000
        ]);

        // Tournament Profit should equal -1000 instead of -500
        $this->assertEquals(-1000, $tournament->fresh()->profit);
    }

    public function testDeletingARebuyUpdatesTheTournamentsProfit()
    {
        $user = factory('App\User')->create();

        $tournament = $user->startTournament();
        $rebuy = $tournament->addRebuy(500);
        $this->assertEquals(-500, $tournament->fresh()->profit);

        $rebuy->delete();

        // Tournament Profit should equal 0 instead of -500
        $this->assertEquals(0, $tournament->fresh()->profit);
    }
}
