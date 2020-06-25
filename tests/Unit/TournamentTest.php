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

    public function testTournamentVariablesDefaultToUserDefaults()
    {
        $user = factory('App\User')->create([
            'default_limit_id' => 2,
            'default_variant_id' => 3,
        ]);
        
        // Start Tournament with empty attributes
        $tournament = $user->startTournament([]);

        $this->assertEquals(2, $tournament->limit_id);
        $this->assertEquals(3, $tournament->variant_id);
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

        $user = factory('App\User')->create();
        $this->signIn($user);

        $tournament = $user->startTournament($this->getLiveTournamentAttributes());
        $tournament->addBuyIn(1000);

        // Original bankroll is 0, but we take off 1000 as we buy in.
        $this->assertEquals(-1000, $user->fresh()->bankroll);

        // This should also work if we update the BuyIn.
        $buy_in = $tournament->buyIns()->first();
        $buy_in->update([
            'amount' => 500
        ]);
        // Bankroll should be -500 (original 0 and updated -500)
        $this->assertEquals(-500, $user->fresh()->bankroll);

        // This should also work if we update the BuyIn.
        $buy_in->delete();
        // Bankroll should revert back to 0
        $this->assertEquals(0, $user->fresh()->bankroll);
        
        
        // Testing Positive transaction as well.
        $cashOut = $tournament->addCashOut(2000);
        $this->assertEquals(2000, $user->fresh()->bankroll);

        // Delete the Cash Out and user's bankroll should revert back to 0
        $cashOut->delete();
        $this->assertEquals(0, $user->fresh()->bankroll);
        
    }

    public function testTheUsersBankrollIsUpdatedWhenATournamentIsDeleted()
    {
        // Start with zero bankroll
        $user = factory('App\User')->create();
        $this->signIn($user);

        // Create a tournament with various buy ins and cash out.
        $tournament = $user->startTournament($this->getLiveTournamentAttributes());
        $tournament->addBuyIn(1000);
        $tournament->addExpense(50);
        $tournament->addExpense(200);
        $tournament->addRebuy(1000);
        $tournament->addAddOn(500);
        $tournament->addCashOut(1000);
        // Total Buy Ins = 1000 + 50 + 200 + 1000 + 500 = 2750
        // Total Cash Out = 1000
        // Tournament Profit  = -2750 + 1000 = -1750

        $this->assertEquals(-1750, $tournament->fresh()->profit);
        $this->assertEquals(-1750, $user->fresh()->bankroll);

        // Now if we delete the tournament the user's bankroll should revert back to the orignal bankroll of 0
        $tournament->fresh()->delete();
        $this->assertEquals(0, $user->fresh()->bankroll);
        
        // Test again with positive profit
        $tournament2 = $user->startTournament($this->getLiveTournamentAttributes());
        $tournament2->addCashOut(10000);
        // Orignal bankroll 0 + cashOut 10000
        $this->assertEquals(10000, $user->fresh()->bankroll);

        // Deleting the Cash Out will revert user's bank roll to original amount, which is 0
        $tournament2->fresh()->delete();
        $this->assertEquals(0, $user->fresh()->bankroll);
    }

    public function testWhenATournamentIsDeletedItDeletesAllOfItsGameTransactions()
    {
        $user = factory('App\User')->create();
        $this->signIn($user);
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
        $this->assertEquals(-3250, $user->fresh()->bankroll);
        $this->assertCount(1, $user->tournaments);
        
        // When deleting a Tournament it shoudl delete all it's GameTransactions
        // This is handled in Game model Observer delete method.
        // Can't use cascade down migrations because of polymorphic relationship
        $tournament->delete();

        $user->refresh();
        
        $this->assertCount(0, $user->tournaments);
        $this->assertEquals(0, $user->bankroll);
        $this->assertCount(0, BuyIn::all());
        $this->assertCount(0, Expense::all());
        $this->assertCount(0, CashOut::all());
        $this->assertCount(0, Rebuy::all());
        $this->assertCount(0, AddOn::all());
    }

    public function testTournamentCurrencyDefaultsToUsersCurrency()
    {
        $user = factory('App\User')->create(['currency' => 'AUD']);
        $this->signIn($user);
        
        // Start Tournament with empty attributes
        $tournament = $user->startTournament([]);

        $this->assertEquals('AUD', $tournament->currency);
    }

    public function testTournamentLocaleProfitIsConvertedToUserCurrency()
    {
        // Create a User with default USD currency
        $user = factory('App\User')->create(['currency' => 'USD']);

        // Create a Cash Game with currency of PLN
        $tournament = $user->tournaments()->create(['currency' => 'PLN']);
        // Add a 1000 PLN Buy In
        $tournament->addBuyIn(1000);

        // 4.9 PLN / 1 GBP / 1.25 USD
        // 1000 PLN = £204.08 GBP = $255.10 USD
        $this->assertEquals(-255.10, $tournament->localeProfit);
    }

    public function testTournamentProfit()
    {
        // Big test cover lots of transactions and different currencies.
        // NOTE: Getting small rounding errors because TestExchangeRates are only 2dp so amounts are easier to calculate for test comments

        // 1 GBP = 1.68 CAD = 1.10 EUR = 1.25 USD = 1.79 AUD = 4.9 PLN

        // Create a User with default USD currency
        $user = factory('App\User')->create(['currency' => 'USD']);

        // Create a Cash Game with currency of PLN
        $tournament = $user->tournaments()->create(['currency' => 'PLN']);
        // Add a 1000 PLN Buy In
        $tournament->addBuyIn(1000, 'PLN'); // 1000 PLN = 204.08 GBP = 999.99 PLN = 255.10  USD
        // Add 30 CAD expense
        $tournament->addExpense(30, 'CAD'); // 30 CAD = 17.86 GBP = 87.51 PLN = 22.33 USD
        // Add 100 USD Rebuy
        $tournament->addRebuy(100, 'USD'); // 100 USD = 80 GBP = 392 PLN = 100 USD
        // Add 250 AUD add on
        $tournament->addAddOn(250, 'AUD'); // 250 AUD = 139.67 GBP = 684.36 PLN = 174.58 USD
        // Cash out for 1000 GBP
        $tournament->addCashOut(1000, 'GBP'); // 1000 GBP = 4900 PLN = 1250 USD

        // Tournament currency is PLN
        $this->assertEquals('PLN', $tournament->currency);

        // Tournament Profit is in PLN
        // -999.99 - 87.51 - 392 - 684.36 + 4900 = 2736.14 PLN
        $this->assertEquals(2736.14, $tournament->profit);

        // Tournament Locale Profit is in USD (User currency)
        // -255.10 - 22.33 - 100 - 174.59 + 1250 = 697.99 USD
        $this->assertEquals(697.99, $tournament->localeProfit);

        // User Bankroll is in USD
        $this->assertEquals(697.99, $user->bankroll);
    }
}
