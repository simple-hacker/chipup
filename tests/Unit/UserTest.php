<?php

namespace Tests\Unit;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;
    
    public function testUserHasABankroll()
    {
        $user = factory('App\User')->create();

        $this->assertEquals($user->bankroll, 0);
    }

    public function testGetTheLiveCashGameForAUser()
    {
        $user = factory('App\User')->create();

        // Start a Cash Game session.
        $cash_game = $user->startCashGame();

        $this->assertEquals($user->liveCashGame()->id, $cash_game->id);
        
        // End the Cash Game session.
        $cash_game->end();
        
        // liveCashGame should now be an empty Collection.
        $this->assertEmpty($user->liveCashGame());
    }

    public function testGetTheLiveTournamentForAUser()
    {
        $user = factory('App\User')->create();

        // Start a Cash Game session.
        $tournament = $user->startTournament($this->getTournamentAttributes());

        $this->assertEquals($user->liveTournament()->id, $tournament->id);
        
        // End the Cash Game session.
        $tournament->end();
        
        // liveCashGame should now be an empty Collection.
        $this->assertEmpty($user->liveCashGame());
    }

    public function testAUserCanCompleteTheSetup()
    {
        $user = factory('App\User')->create();

        $user->completeSetup();

        $this->assertTrue($user->fresh()->setup_complete);
    }
}
