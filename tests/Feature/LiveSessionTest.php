<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LiveSessionTest extends TestCase
{
    use RefreshDatabase;

    public function testCannotLiveStartCashGameIfLiveTournamentInProgress()
    {
        $user = $this->signIn();

        // Start Tournament
        $this->postJson(route('tournament.live.start'), $this->getLiveTournamentAttributes())->assertOk();

        // Then try to start a Cash Game.
        $this->postJson(route('cash.live.start'), $this->getLiveCashGameAttributes())->assertStatus(422);
    }

    public function testCannotLiveStartTournamentIfLiveCashGameInProgress()
    {
        $user = $this->signIn();

        // Start Cash Game
        $this->postJson(route('cash.live.start'), $this->getLiveCashGameAttributes())->assertOk();
        
        // Then try to start a Tournament.
        $this->postJson(route('tournament.live.start'), $this->getLiveTournamentAttributes())->assertStatus(422);
    }
}
