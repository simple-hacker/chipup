<?php

namespace Tests\Unit;

use App\User;
use App\Tournament;
use Tests\TestCase;
use App\Transactions\AddOn;
use App\Transactions\BuyIn;
use App\Transactions\Rebuy;
use App\Transactions\CashOut;
use App\Transactions\Expense;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TournamentTest extends TestCase
{
    use RefreshDatabase;

    public function testATournamentBelongsToAUser()
    {
        $user = factory('App\User')->create();
        $tournament = factory('App\Tournament')->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $tournament->user);
    }

    public function testTournamentFactoryCreatesNewUser()
    {
        factory('App\Tournament')->create();
        $this->assertCount(1, User::all());
    }

    public function testTournamentFactoryAndThenSignInCreatesTwoDifferentUsers()
    {
        factory('App\Tournament')->create();

        $this->signIn();

        $this->assertCount(2, User::all());
    }

    public function testSignInAfterTournamentFactoryDoesNotOwnTournament()
    {
        $tournament = factory('App\Tournament')->create();

        $this->signIn();

        $this->assertTrue($tournament->user->isNot(auth()->user()));
    }

    public function testAUserCanStartATournament()
    {
        $user = factory('App\User')->create();

        $user->startTournament($this->getLiveTournamentAttributes());

        $this->assertCount(1, $user->tournaments);
        $this->assertInstanceOf(Tournament::class, $user->tournaments()->first());
    }

    public function testATimeCanBeSuppliedWhenStartingATournament()
    {
        $user = factory('App\User')->create();

        $time = Carbon::create(2020, 02, 15, 18, 30, 00)->toDateTimeString();

        $tournament = $user->startTournament($this->getLiveTournamentAttributes(1000, $time));

        $this->assertEquals('2020-02-15 18:30:00', $tournament->fresh()->start_time);
    }

    public function testATournamentCanBeEnded()
    {
        $tournament = $this->startLiveTournament();

        // Assert Tournament doesn't have an end time.
        $this->assertNull($tournament->end_time);

        // Set test time in future so we can end session.
        Carbon::setTestNow('tomorrow');

        $tournament->end();

        $this->assertEquals($tournament->fresh()->end_time, Carbon::getTestNow());
    }

    public function testATimeCanBeSuppliedWhenEndingATournament()
    {
        $tournament = $this->startLiveTournament();

        $time = Carbon::create('+3 hours')->toDateTimeString();

        $tournament->end($time);

        $this->assertEquals($tournament->fresh()->end_time, $time);
    }

    public function testAnEndTimeCannotBeBeforeAStartTime()
    {
        $this->expectException(\App\Exceptions\InvalidDateException::class);

        $tournament = $this->startLiveTournament();

        $tournament->end(Carbon::create('-3 hours')->toDateTimeString());
    }

    public function testATournamentCannotBeStartedIfThereIsAlreadyALiveTournamentInProgress()
    {
        $this->expectException(\App\Exceptions\SessionInProgressException::class);

        $user = factory('App\User')->create();

        $user->startTournament($this->getLiveTournamentAttributes());
        // Error should be thrown when starting another
        $user->startTournament($this->getLiveTournamentAttributes());
    }

    public function testCheckingStartingMultipleTournamentsAsLongAsPreviousOnesHaveFinished()
    {
        $user = factory('App\User')->create();

        // Start and finish a tournament.
        $tournament = $user->startTournament($this->getLiveTournamentAttributes());
        $tournament->end(Carbon::create('+1 hour')->toDateTimeString());

        Carbon::setTestNow('+ 3 hours');

        // Start a tournament.
        $tournament_2 = $user->startTournament($this->getLiveTournamentAttributes());

        // User's liveTournament should be tournament_2.
        $this->assertEquals($user->liveTournament()->id, $tournament_2->id);
    }

    public function testTournamentCanHaveABuyIn()
    {
        $tournament = $this->startLiveTournament();

        $tournament->addBuyIn(500);

        $this->assertCount(1, $tournament->buyIn()->get());
        $this->assertInstanceOf(\App\Transactions\BuyIn::class, $tournament->buyIn);
    }

    public function testTournamentCannotHaveMultipleBuyIns()
    {
        $this->expectException(\App\Exceptions\MultipleBuyInsNotAllowedException::class);

        try {
            $tournament = $this->startLiveTournament();

            $buy_in_1 = $tournament->addBuyIn(500);
            $tournament->addBuyIn(500);
        } finally {
            $this->assertCount(1, $tournament->buyIn()->get());
            $this->assertEquals($tournament->buyIn->id, $buy_in_1->id);
        }        
    }

    public function testTournamentCanHaveMultipleExpenses()
    {
        $tournament = $this->startLiveTournament();

        $tournament->addExpense(500);
        $tournament->addExpense(1000);
        $tournament->addExpense(300);

        $this->assertCount(3, $tournament->expenses);
    }

    public function testTournamentCanHaveMultipleRebuys()
    {
        $tournament = $this->startLiveTournament();

        $tournament->addRebuy(1000);
        $tournament->addRebuy(1000);
        $tournament->addRebuy(1000);

        $this->assertCount(3, $tournament->rebuys);
    }

    public function testTournamentCanHaveMultipleAddOns()
    {
        $tournament = $this->startLiveTournament();

        $tournament->addAddOn(500);
        $tournament->addAddOn(500);
        $tournament->addAddOn(500);

        $this->assertCount(3, $tournament->addOns);
    }

    public function testATournamentCanBeCashedOut()
    {
        // Cashing Out ends the tournament as well as updates the Tournament's profit.

        $tournament = $this->startLiveTournament();

        $tournament->addBuyIn(10000);
        $this->assertNull($tournament->fresh()->end_time);

        $tournament->addCashOut(30000);
        $this->assertEquals(20000, $tournament->fresh()->profit);
    }

    public function testATournamentCanOnlyBeCashOutOnce()
    {
        // This error is thrown because the tournament_id is unique in the CashOut migration
        $this->expectException(\Illuminate\Database\QueryException::class);

        try{
            $tournament = $this->startLiveTournament();

            $tournament->addCashOut(10000);
            $tournament->addCashOut(10000);
        } finally {
            $this->assertCount(1, $tournament->cashOut()->get());
            $this->assertInstanceOf(CashOut::class, $tournament->cashOut);
        }
    }

    public function testTournamentProfitFlow()
    {
        $tournament = $this->startLiveTournament();
        
        $tournament->addBuyIn(1000);
        $tournament->addExpense(50);
        $tournament->addExpense(200);
        $tournament->addRebuy(1000);
        $tournament->addRebuy(1000);
        $tournament->addAddOn(5000);
        $tournament->addCashOut(1000);

        //Tournament profit should be -1000 -50 -200 -1000 -1000 + 1000 -5000 = -7250
        $this->assertEquals(-7250, $tournament->fresh()->profit);

        $this->assertCount(1, $tournament->buyIns);
        $this->assertCount(2, $tournament->expenses);
        $this->assertCount(2, $tournament->rebuys);
        $this->assertCount(1, $tournament->addOns);
        $this->assertCount(1, $tournament->cashOut()->get());

        // Change the first Expense to 500 instead of 50
        $tournament->expenses->first()->update([
            'amount' => 500
        ]);

        // Delete the second expense (200);
        $tournament->expenses->last()->delete();

        // Change the 2nd rebuy to 2000 instead of 1000
        $tournament->rebuys->last()->update([
            'amount' => 2000
        ]);

        // Delete the only add on (5000);
        $tournament->addOns->first()->delete();

        // Update the cashOut value to 4000.
        $tournament->cashOut->update([
            'amount' => 4000
        ]);

        $tournament->refresh();

        $this->assertCount(1, $tournament->expenses);
         
        //Tournament profit should now be -7250 - (500-50) + 200 - (2000-1000) + 5000 + (4000-1000) = -500
        $this->assertEquals(-500, $tournament->profit);
    }

    public function testTheUsersBankrollIsUpdatedWhenUpdatingTheTournamentsProfit()
    {
        // There is a Observer on the abstract Game so when the Game (Tournament) profit is updated (i.e. adding buyIn, expenses etc)
        // then the User's bankroll is also updated.
        // Only testing the BuyIn of the GameTransactions as they all work the same because of Positive/NegativeGameTransactionObserver
        // which updates the Tournament's profit.

        $user = factory('App\User')->create([
            'bankroll' => 5000
        ]);
        $tournament = $user->startTournament($this->getLiveTournamentAttributes());
        $tournament->addBuyIn(1000);

        // Original bankroll is 5000, but we take off 1000 as we buy in.
        // User's bankroll should be 4000
        $this->assertEquals(4000, $user->fresh()->bankroll);

        // This should also work if we update the BuyIn.
        $buy_in = $tournament->buyIns()->first();
        $buy_in->update([
            'amount' => 500
        ]);
        // Bankroll should be 4500 (original 5000 and updated -500)
        $this->assertEquals(4500, $user->fresh()->bankroll);

        // This should also work if we update the BuyIn.
        $buy_in->delete();
        // Bankroll should be 5000 (original 5000)
        $this->assertEquals(5000, $user->fresh()->bankroll);
        
        
        // Testing Positive transaction as well.
        $tournament->addCashOut(2000);
        // We're back to the original 5000, but we cashed out for 2000.  Bankroll = 7000
        $this->assertEquals(7000, $user->fresh()->bankroll);
        
    }

    public function testTheUsersBankrollIsUpdatedWhenATournamentIsDeleted()
    {
        $user = factory('App\User')->create([
            'bankroll' => 10000
        ]);
        $tournament = $user->startTournament($this->getLiveTournamentAttributes());
        $tournament->addBuyIn(1000);
        $tournament->addExpense(50);
        $tournament->addExpense(200);
        $tournament->addRebuy(1000);
        $tournament->addAddOn(500);
        $tournament->addCashOut(1000);

        // Check that users bankroll is 7750 (10000-1000-50--1000-500+1000)
        $this->assertEquals(8250, $user->fresh()->bankroll);
        // Tournament profit is -1750 (-1000-50-1000-500+1000)
        $this->assertEquals(-1750, $tournament->fresh()->profit);

        // Now if we delete the tournament the user's bankroll should revert back to the orignal
        // 10000, calculated by the user's current bankroll (9750) minus the tournaments profit (-250)
        // If the tournament profit is negative, it adds back on, if positive it should subtract it.
        $tournament->fresh()->delete();
        $this->assertEquals(10000, $user->fresh()->bankroll);
        
        // Test again with positive profit
        $tournament2 = $user->startTournament($this->getLiveTournamentAttributes());
        $tournament2->addCashOut(10000);
        // Orignal bankroll 10000 + cashOut 10000 = 20000
        $this->assertEquals(20000, $user->fresh()->bankroll);

        $tournament2->fresh()->delete();
        $this->assertEquals(10000, $user->fresh()->bankroll);
    }

    public function testWhenATournamentIsDeletedItDeletesAllOfItsGameTransactions()
    {
        $user = factory('App\User')->create();
        $tournament = $user->startTournament($this->getLiveTournamentAttributes());
        $tournament->addBuyIn(1000);
        $tournament->addExpense(50);
        $tournament->addExpense(200);
        $tournament->addRebuy(1000);
        $tournament->addRebuy(1000);
        $tournament->addAddOn(500);
        $tournament->addAddOn(500);
        $tournament->addCashOut(1000);
        $tournament->refresh();
        // Make sure counts and bankroll are correct.
        $this->assertCount(1, $tournament->buyIns);
        $this->assertCount(2, $tournament->expenses);
        $this->assertCount(1, $tournament->cashOut()->get());
        $this->assertCount(2, $tournament->rebuys);
        $this->assertCount(2, $tournament->addOns);
        $this->assertEquals(-3250, $tournament->profit);
        $this->assertEquals(6750, $user->fresh()->bankroll);
        $this->assertCount(1, $user->tournaments);
        
        // When deleting a Tournament it shoudl delete all it's GameTransactions
        // This is handled in Game model Observer delete method.
        // Can't use cascade down migrations because of polymorphic relationship
        $tournament->delete();

        $user->refresh();
        
        $this->assertCount(0, $user->tournaments);
        $this->assertEquals(10000, $user->bankroll);
        $this->assertCount(0, BuyIn::all());
        $this->assertCount(0, Expense::all());
        $this->assertCount(0, CashOut::all());
        $this->assertCount(0, Rebuy::all());
        $this->assertCount(0, AddOn::all());
    }
}
