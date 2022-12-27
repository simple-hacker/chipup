<?php

namespace Tests\Unit;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function testUserHasABankroll()
    {
        $user = \App\Models\User::factory()->create();

        $this->assertEquals($user->bankroll, 0);
    }

    public function testGetTheLiveCashGameForAUser()
    {
        $user = \App\Models\User::factory()->create();

        // Start a Cash Game session.
        $cashGame = $user->startCashGame();

        $this->assertEquals($user->liveCashGame()->id, $cashGame->id);

        // End the Cash Game session.
        $cashGame->end();

        // liveCashGame should now be an empty Collection.
        $this->assertEmpty($user->liveCashGame());
    }

    public function testGetTheLiveTournamentForAUser()
    {
        $user = \App\Models\User::factory()->create();

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
        $user = \App\Models\User::factory()->create();

        $user->completeSetup();

        $this->assertTrue($user->fresh()->setup_complete);
    }
}
