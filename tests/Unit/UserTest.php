<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;
    
    public function testUserHasABankroll()
    {
        $user = factory('App\User')->create([
            'bankroll' => 10000
        ]);

        $this->assertEquals($user->bankroll, 10000);
    }

    public function testBankrollCanBeUpdated()
    {
        // Create user with default 10000 bankroll.
        $user = factory('App\User')->create();

        $user->updateBankroll(50);

        $this->assertEquals($user->bankroll, 10050);

        $user->updateBankroll(-50);

        $this->assertEquals($user->fresh()->bankroll, 10000);
    }

    public function testBankrollUpdateMustBeInteger()
    {
        $this->expectException(\App\Exceptions\NonIntegerAmount::class);
      
        $user = factory('App\User')->create();

        $user->updateBankroll(50.99);
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
