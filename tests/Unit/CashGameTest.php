<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\CashGame;
use Tests\TestCase;
use App\Transactions\BuyIn;
use App\Transactions\CashOut;
use App\Transactions\Expense;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CashGameTest extends TestCase
{
    use RefreshDatabase;

    public function testACashGameBelongsToAUser()
    {
        $user = \App\Models\User::factory()->create();
        $cashGame = \App\Models\CashGame::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $cashGame->user);
    }

    public function testAUserCanStartACashGameSession()
    {
        $user = \App\Models\User::factory()->create();
        $user->startCashGame();

        $this->assertCount(1, $user->cashGames);
        $this->assertInstanceOf(CashGame::class, $user->cashGames()->first());
    }

    public function testATimeCanBeSuppliedWhenStartingACashGame()
    {
        $user = \App\Models\User::factory()->create();

        $time = Carbon::now()->toDateTimeString();

        $cashGame = $user->startCashGame([
                'start_time' => $time,
            ]);

        $this->assertEquals($time, $cashGame->fresh()->start_time);
    }

    public function testACashGameCanBeEnded()
    {
        $cashGame = $this->startLiveCashGame();

        // Assert CashGame doesn't have an end time.
        $this->assertNull($cashGame->end_time);

        // Set test time in future so we can end session.
        Carbon::setTestNow('tomorrow');

        $cashGame->end();

        $this->assertEquals($cashGame->fresh()->end_time, Carbon::getTestNow());
    }

    public function testATimeCanBeSuppliedWhenEndingACashGame()
    {
        $cashGame = $this->startLiveCashGame();

        $time = Carbon::create('+3 hours')->toDateTimeString();

        $cashGame->end($time);

        $this->assertEquals($cashGame->fresh()->end_time, $time);
    }

    public function testAnEndTimeCannotBeBeforeAStartTime()
    {
        $this->expectException(\App\Exceptions\InvalidDateException::class);

        $cashGame = $this->startLiveCashGame();

        $cashGame->end(Carbon::create('-3 hours')->toDateTimeString());
    }

    public function testACashGameCannotBeStartedIfThereIsAlreadyALiveCashGameInProgress()
    {
        $this->expectException(\App\Exceptions\SessionInProgressException::class);

        $user = \App\Models\User::factory()->create();

        $user->startCashGame();
        // Error should be thrown when starting another
        $user->startCashGame();
    }

    public function testCheckingStartingMultipleCashGamesAsLongAsPreviousOnesHaveFinished()
    {
        $user = \App\Models\User::factory()->create();

        // Start and finish a cash game.
        $cashGame = $user->startCashGame();
        $cashGame->end(Carbon::create('+1 hour')->toDateTimeString());

        Carbon::setTestNow('+ 3 hours');

        // Start a cash game.
        $cashGame_2 = $user->startCashGame();

        // User's liveCashGame should be cash_game_2.
        $this->assertEquals($user->liveCashGame()->id, $cashGame_2->id);
    }

    public function testCashGameVariablesDefaultToUserDefaults()
    {
        $user = \App\Models\User::factory()->create([
            'default_stake_id' => 3,
            'default_limit_id' => 2,
            'default_variant_id' => 1,
            'default_table_size_id' => 2,
        ]);

        // Start CashGame with empty attributes
        $cashGame = $user->startCashGame([]);

        $this->assertEquals(3, $cashGame->stake_id);
        $this->assertEquals(2, $cashGame->limit_id);
        $this->assertEquals(1, $cashGame->variant_id);
        $this->assertEquals(2, $cashGame->table_size_id);
    }

    public function testCashGameCanHaveABuyIn()
    {
        $cashGame = $this->startLiveCashGame();

        $cashGame->addBuyIn(500);

        $this->assertCount(1, $cashGame->buyIns);
    }

    public function testCashGameCanHaveMultipleBuyIns()
    {
        $cashGame = $this->startLiveCashGame();

        $cashGame->addBuyIn(500);
        $cashGame->addBuyIn(500);
        $cashGame->addBuyIn(500);

        $this->assertCount(3, $cashGame->buyIns);
    }

    public function testCashGameCanHaveMultipleExpenses()
    {
        $cashGame = $this->startLiveCashGame();

        $cashGame->addExpense(500);
        $cashGame->addExpense(1000);
        $cashGame->addExpense(300);

        $this->assertCount(3, $cashGame->expenses);
    }

    public function testACashGameCanBeCashedOut()
    {
        // Cashing Out ends the session as well as updates the CashGame's profit.
        $cashGame = $this->startLiveCashGame();

        $cashGame->addBuyIn(10000);
        $this->assertNull($cashGame->fresh()->end_time);

        $cashGame->addCashOut(30000);

        $this->assertEquals(20000, $cashGame->fresh()->profit);
    }

    public function testACashGameCanOnlyBeCashOutOnce()
    {
        // This error is thrown because the cash_game_id is unique in the CashOut migration
        $this->expectException(\Illuminate\Database\QueryException::class);

        try{
            $cashGame = $this->startLiveCashGame();

            $cashGame->addCashOut(10000);
            $cashGame->addCashOut(10000);
        } finally {
            $this->assertCount(1, $cashGame->cashOut()->get());
            $this->assertInstanceOf(CashOut::class, $cashGame->cashOut);
        }
    }

    public function testCashGameProfitFlow()
    {
        $cashGame = $this->startLiveCashGame();

        $cashGame->addBuyIn(1000);
        $cashGame->addExpense(50);
        $cashGame->addExpense(200);
        $cashGame->addBuyIn(2000);
        $cashGame->addCashOut(1000);

        //CashGame profit should be -1000 -50 -200 -2000 + 1000 = -2250
        $this->assertEquals(-2250, $cashGame->fresh()->profit);

        $this->assertCount(2, $cashGame->buyIns);
        $this->assertCount(2, $cashGame->expenses);
        $this->assertCount(1, $cashGame->cashOut()->get());

        // Change the first Expense to 500 instead of 50
        tap($cashGame->expenses()->first())->update([
            'amount' => 500
        ]);

        // Delete the second buyIn (2000);
        tap($cashGame->buyIns->last())->delete();

        // Update the cashOut value to 4000.
        $cashGame->cashOut->update([
            'amount' => 4000
        ]);

        $cashGame->refresh();

        $this->assertCount(1, $cashGame->buyIns);

        //CashGame profit should now be -1000 -500 -200 + 4000 = 2300
        $this->assertEquals(2300, $cashGame->profit);
    }

    public function testTheUsersBankrollIsUpdatedWhenUpdatingTheCashGamesProfit()
    {
        // There is a Observer on the abstract Game so when the Game (CashGame) profit is updated (i.e. adding buyIn, expenses etc)
        // then the User's bankroll is also updated.
        // Only testing the BuyIn of the GameTransactions as they all work the same because of Positive/NegativeGameTransactionObserver
        // which updates the CashGame's profit.

        $user = \App\Models\User::factory()->create();
        $this->signIn($user);

        $cashGame = $user->startCashGame();
        $cashGame->addBuyIn(1000);

        // Original bankroll is 0, but we take off 1000 as we buy in.
        // User's bankroll should be -1000
        $this->assertEquals(-1000, $user->fresh()->bankroll);

        // This should also work if we update the BuyIn.
        $buy_in = $cashGame->buyIns()->first();
        $buy_in->update([
            'amount' => 500
        ]);
        // Bankroll should be -500 (original -1000 and updated -500)
        $this->assertEquals(-500, $user->fresh()->bankroll);

        // This should also work if we update the BuyIn.
        $buy_in->delete();
        // Bankroll should be 5000 (original 5000)
        $this->assertEquals(0, $user->fresh()->bankroll);


        // Testing Positive transaction as well.
        $cashOut = $cashGame->addCashOut(2000);
        $this->assertEquals(2000, $user->fresh()->bankroll);

        // Delete the Cash Out and user's bankroll should revert back to 0
        $cashOut->delete();
        $this->assertEquals(0, $user->fresh()->bankroll);
    }

    public function testTheUsersBankrollIsUpdatedWhenACashGameIsDeleted()
    {
        $user = \App\Models\User::factory()->create();
        $this->signIn($user);

        $cashGame = $user->startCashGame();
        $cashGame->addBuyIn(1000);
        $cashGame->addExpense(50);
        $cashGame->addExpense(200);
        $cashGame->addBuyIn(2000);
        $cashGame->addCashOut(1000);

        // Check that users bankroll is 7750 (10000-1000-50-200-2000+1000)
        $this->assertEquals(-2250, $user->fresh()->bankroll);
        // CashGame profit is -2250 (-1000-50-200-2000+1000)
        $this->assertEquals(-2250, $cashGame->fresh()->profit);

        // Now if we delete the cash_game the user's bankroll should revert back to the orignal
        // 10000, calculated by the user's current bankroll (7750) minus the cash_games profit (-2250)
        // If the cash_game profit is negative, it adds back on, if positive it should subtract it.
        $cashGame->fresh()->delete();
        $this->assertEquals(0, $user->fresh()->bankroll);

        // Test again with positive profit
        $cashGame2 = $user->startCashGame();
        $cashGame2->addCashOut(10000);
        // Orignal bankroll 0 + cashOut 10000 = 10000
        $this->assertEquals(10000, $user->fresh()->bankroll);

        $cashGame2->fresh()->delete();
        $this->assertEquals(0, $user->fresh()->bankroll);
    }

    public function testWhenACashGameIsDeletedItDeletesAllOfItsGameTransactions()
    {
        $user = \App\Models\User::factory()->create();
        $this->signIn($user);

        $cashGame = $user->startCashGame();
        $cashGame->addBuyIn(1000);
        $cashGame->addExpense(50);
        $cashGame->addExpense(200);
        $cashGame->addBuyIn(2000);
        $cashGame->addCashOut(1000);
        $cashGame->refresh();
        // Make sure counts and bankroll are correct.
        $this->assertCount(2, $cashGame->buyIns);
        $this->assertCount(2, $cashGame->expenses);
        $this->assertCount(1, $cashGame->cashOut()->get());
        $this->assertEquals(-2250, $cashGame->profit);
        $this->assertEquals(-2250, $user->fresh()->bankroll);
        $this->assertCount(1, $user->cashGames);

        // When deleting a CashGame it should delete all it's GameTransactions
        // This is handled in Game model Observer delete method.
        // Can't use cascade down migrations because of polymorphic relationship
        $cashGame->delete();

        $user->refresh();

        $this->assertCount(0, $user->cashGames);
        $this->assertEquals(0, $user->bankroll);
        $this->assertCount(0, BuyIn::all());
        $this->assertCount(0, Expense::all());
        $this->assertCount(0, CashOut::all());
    }

    public function testCashGameCurrencyDefaultsToUsersCurrency()
    {
        $user = \App\Models\User::factory()->create(['currency' => 'USD']);
        $this->signIn($user);

        // Start CashGame with empty attributes
        $cashGame = $user->startCashGame([]);

        $this->assertEquals('USD', $cashGame->currency);
    }

    public function testCashGameLocaleProfitIsConvertedToUserCurrency()
    {
        // Create a User with default USD currency
        $user = \App\Models\User::factory()->create(['currency' => 'USD']);

        // Create a Cash Game with currency GBP
        $cashGame = $user->cashGames()->create(['currency' => 'GBP']);
        // Add a £1000 GBP Buy In
        $cashGame->addBuyIn(1000);

        $cashGame->refresh();

        // 1 GBP / ~1.25 USD.  So localeProfit should equal ~-$1250 USD
        $this->assertEquals($this->converterTest(-1000, 'GBP', 'USD'), $cashGame->localeProfit);

        // Cash Game profit is -1000 and currency is GBP
        $this->assertEquals(-1000, $cashGame->profit);
        $this->assertEquals('GBP', $cashGame->currency);
    }

    public function testCashGameProfit()
    {
        // Big test cover lots of transactions and different currencies.
        // 1 GBP = ~1.68 CAD = ~1.10 EUR = ~1.25 USD = ~1.79 AUD = ~4.9 PLN

        // Create a User with default USD currency
        $user = \App\Models\User::factory()->create(['currency' => 'USD']);

        // Create a Cash Game with currency of PLN
        $cashGame = $user->cashGames()->create(['currency' => 'PLN']);
        // Add a 1000 PLN Buy In
        $cashGame->addBuyIn(1000, 'PLN'); // 1000 PLN = ~204.08 GBP = ~255.10  USD
        // Add 30 CAD expense
        $cashGame->addExpense(30, 'CAD'); // 30 CAD = ~17.86 GBP = ~87.51 PLN = ~22.32 USD
        // Cash out for 1000 GBP
        $cashGame->addCashOut(1000, 'GBP'); // 1000 GBP = ~4900 PLN = ~1250 USD

        $cashGame->refresh();

        // cashGame currency is PLN
        $this->assertEquals('PLN', $cashGame->currency);

        // cashGame Profit is in PLN
        // -1000 - 87.51 + 4900 = 3812.5 PLN
        $profit = $this->converterTest(-1000, 'PLN', 'PLN') + $this->converterTest(-30, 'CAD', 'PLN') + $this->converterTest(1000, 'GBP', 'PLN');
        $this->assertEquals($profit, $cashGame->profit);

        // cashGame Locale Profit is in USD (User currency)
        // -255.10 - 22.32 + 1250 = ~972.58 USD
        $localeProfit = $this->converterTest(-1000, 'PLN', 'USD') + $this->converterTest(-30, 'CAD', 'USD') + $this->converterTest(1000, 'GBP', 'USD');
        $this->assertEquals($localeProfit, $cashGame->localeProfit);

        // User Bankroll is in USD
        $this->assertEquals($localeProfit, $user->bankroll);
    }
}
