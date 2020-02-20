<?php

namespace Tests\Unit;

use App\User;
use App\Tournament;
use Tests\TestCase;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TournamentTest extends TestCase
{
    use RefreshDatabase;

    public function testATournamentBelongsToAUser()
    {
        $tournament = factory('App\Tournament')->create();

        $this->assertInstanceOf(User::class, $tournament->user);
    }

    public function testAUserCanStartATournament()
    {
        $user = factory('App\User')->create();

        $user->startTournament();

        $this->assertCount(1, $user->tournaments);
        $this->assertInstanceOf(Tournament::class, $user->tournaments()->first());
    }

    public function testATimeCanBeSuppliedWhenStartingATournament()
    {
        $user = factory('App\User')->create();

        $time = Carbon::create(2020, 02, 15, 18, 30, 00);

        $tournament = $user->startTournament($time);

        $this->assertEquals('2020-02-15 18:30:00', $tournament->fresh()->start_time);
    }

    public function testATournamentCanBeEnded()
    {
        $user = factory('App\User')->create();

        $tournament = $user->startTournament();

        // Assert Tournament doesn't have an end time.
        $this->assertNull($tournament->end_time);

        // Set test time in future so we can end session.
        Carbon::setTestNow('tomorrow');

        $tournament->end();

        $this->assertEquals($tournament->fresh()->end_time, Carbon::getTestNow());
    }

    public function testATimeCanBeSuppliedWhenEndingATournament()
    {
        $user = factory('App\User')->create();

        $tournament = $user->startTournament();

        $time = Carbon::create('+3 hours');

        $tournament->end($time);

        $this->assertEquals($tournament->fresh()->end_time, $time->toDateTimeString());
    }

    public function testAnEndTimeCannotBeBeforeAStartTime()
    {
        $this->expectException(\App\Exceptions\InvalidDate::class);

        $user = factory('App\User')->create();

        $tournament = $user->startTournament();

        $tournament->end(Carbon::create('-3 hours'));
    }

    public function testATournamentCannotBeStartedIfThereIsAlreadyALiveTournamentInProgress()
    {
        $this->expectException(\App\Exceptions\TournamentInProgress::class);

        $user = factory('App\User')->create();

        $user->startTournament();
        // Error should be thrown when starting another
        $user->startTournament();
    }

    public function testCheckingStartingMultipleTournamentsAsLongAsPreviousOnesHaveFinished()
    {
        $user = factory('App\User')->create();

        // Start and finish a tournament.
        $tournament = $user->startTournament();
        $tournament->end(Carbon::create('+1 hour'));

        Carbon::setTestNow('+ 3 hours');

        // Start a tournament.
        $tournament_2 = $user->startTournament();

        // User's liveTournament should be tournament_2.
        $this->assertEquals($user->liveTournament()->id, $tournament_2->id);
    }
}
