<?php

namespace Tests\Unit;

use App\User;
use App\Transactions\CashOut;
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
        $this->assertInstanceOf(\App\Transactions\BuyIn::class, $tournament->buyIn);
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

    public function testTournamentCanHaveMultipleExpenses()
    {
        $user = factory('App\User')->create();

        $tournament = $user->startTournament();
        $tournament->addExpense(500);
        $tournament->addExpense(1000);
        $tournament->addExpense(300);

        $this->assertCount(3, $tournament->expenses);
    }

    public function testTournamentCanHaveMultipleRebuys()
    {
        $user = factory('App\User')->create();

        $tournament = $user->startTournament();
        $tournament->addRebuy(1000);
        $tournament->addRebuy(1000);
        $tournament->addRebuy(1000);

        $this->assertCount(3, $tournament->rebuys);
    }

    public function testTournamentCanHaveMultipleAddOns()
    {
        $user = factory('App\User')->create();

        $tournament = $user->startTournament();
        $tournament->addAddOn(500);
        $tournament->addAddOn(500);
        $tournament->addAddOn(500);

        $this->assertCount(3, $tournament->addOns);
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

    public function testTournamentProfitFlow()
    {
        $user = factory('App\User')->create();
        $tournament = $user->startTournament();
        $tournament->addBuyIn(1000);
        $tournament->addExpense(50);
        $tournament->addExpense(200);
        $tournament->addRebuy(1000);
        $tournament->addRebuy(1000);
        $tournament->addAddOn(5000);
        $tournament->cashOut(1000);

        //Tournament profit should be -1000 -50 -200 -1000 -1000 + 1000 -5000 = -7250
        $this->assertEquals(-7250, $tournament->fresh()->profit);

        $this->assertCount(1, $tournament->buyIns);
        $this->assertCount(2, $tournament->expenses);
        $this->assertCount(2, $tournament->rebuys);
        $this->assertCount(1, $tournament->addOns);
        $this->assertCount(1, $tournament->cashOutModel()->get());

        // Change the first Expense to 500 instead of 50
        tap($tournament->expenses->first())->update([
            'amount' => 500
        ]);

        // Delete the second expense (200);
        tap($tournament->expenses->last())->delete();

        // Change the 2nd rebuy to 2000 instead of 1000
        tap($tournament->rebuys->last())->update([
            'amount' => 2000
        ]);

        // Delete the only add on (5000);
        tap($tournament->addOns->first())->delete();

        // Update the cashOut value to 4000.
        $tournament->cashOutModel->update([
            'amount' => 4000
        ]);

        $tournament->refresh();

        $this->assertCount(1, $tournament->expenses);
         
        //Tournament profit should now be -7250 - (500-50) + 200 - (2000-1000) + 5000 + (4000-1000) = -500
        $this->assertEquals(-500, $tournament->profit);
    }
}
