<?php

namespace Tests\Unit;

use App\User;
use App\CashOut;
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

    public function testTournamentCanHaveABuyIn()
    {
        $user = factory('App\User')->create();

        $tournament = $user->startTournament();
        $tournament->addBuyIn(500);

        $this->assertCount(1, $tournament->buyIn()->get());
        $this->assertInstanceOf(\App\BuyIn::class, $tournament->buyIn);
    }

    public function testTournamentCannotHaveMultipleBuyIns()
    {
        $this->expectException(\App\Exceptions\MultipleBuyInsNotAllowed::class);
        try {
            $user = factory('App\User')->create();

            $tournament = $user->startTournament();
            $buy_in_1 = $tournament->addBuyIn(500);
            $tournament->addBuyIn(500);
        } finally {
            $this->assertCount(1, $tournament->buyIn()->get());
            $this->assertEquals($tournament->buyIn->id, $buy_in_1->id);
        }        
    }

    public function testAddingBuyInUpdatesTournamentProfit()
    {
        $user = factory('App\User')->create();

        $tournament = $user->startTournament();
        $this->assertEquals(0, $tournament->profit);
        $tournament->addBuyIn(500);
        $this->assertEquals(-500, $tournament->fresh()->profit);
    }

    public function testTournamentCanHaveMultipleExpenses()
    {
        $user = factory('App\User')->create();

        $tournament = $user->startTournament();
        $tournament->addExpense(500);
        $tournament->addExpense(1000);
        $tournament->addExpense(300);

        $this->assertCount(3, $tournament->expenses);
    }

    public function testAddingExpensesUpdatesTournamentProfit()
    {
        $user = factory('App\User')->create();

        $tournament = $user->startTournament();
        $this->assertEquals(0, $tournament->profit);
        $tournament->addExpense(50);
        $this->assertEquals(-50, $tournament->fresh()->profit);
        // Add another expense of 100.  Profit should now equal -150
        $tournament->addExpense(100);
        $this->assertEquals(-150, $tournament->fresh()->profit);
    }

    public function testATournamentCanBeCashedOut()
    {
        // Cashing Out ends the tournament as well as updates the Tournament's profit.

        $user = factory('App\User')->create();
        $tournament = $user->startTournament();
        $tournament->addBuyIn(10000);
        $this->assertNull($tournament->fresh()->end_time);

        $end_time = Carbon::now()->toDateTimeString();

        $tournament->cashOut(30000);
        
        $tournament->refresh();
        
        $this->assertEquals($end_time, $tournament->end_time);
        $this->assertEquals(20000, $tournament->profit);
    }

    public function testATournamentCanBeCashedOutAtASuppliedTime()
    {
        $user = factory('App\User')->create();
        $tournament = $user->startTournament();

        $end_time = Carbon::create('+3 hours');

        $tournament->cashOut(30000, $end_time);
                
        $this->assertEquals($end_time->toDateTimeString(), $tournament->fresh()->end_time);
    }

    public function testATournamentCanOnlyBeCashOutOnce()
    {
        // This error is thrown because the tournament_id is unique in the CashOut migration
        $this->expectException(\Illuminate\Database\QueryException::class);

        try{
            $user = factory('App\User')->create();
            $tournament = $user->startTournament();

            $tournament->cashOut(10000);
            $tournament->cashOut(10000);
        } finally {
            $this->assertCount(1, $tournament->cashOutModel()->get());
            $this->assertInstanceOf(CashOut::class, $tournament->cashOutModel);
        }
    }
}
