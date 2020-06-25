<?php

namespace Tests\Unit;

use App\User;
use App\CashGame;
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
        $user = factory('App\User')->create();
        $cash_game = factory('App\CashGame')->create(['user_id' => $user->id]);
        
        $this->assertInstanceOf(User::class, $cash_game->user);
    }

    public function testAUserCanStartACashGameSession()
    {
        $user = factory('App\User')->create();
        $user->startCashGame();

        $this->assertCount(1, $user->cashGames);
        $this->assertInstanceOf(CashGame::class, $user->cashGames()->first());
    }

    public function testATimeCanBeSuppliedWhenStartingACashGame()
    {
        $user = factory('App\User')->create();

        $time = Carbon::now()->toDateTimeString();

        $cash_game = $user->startCashGame([
                'start_time' => $time,
            ]);

        $this->assertEquals($time, $cash_game->fresh()->start_time);
    }
    
    public function testACashGameCanBeEnded()
    {
        $cash_game = $this->startLiveCashGame();

        // Assert CashGame doesn't have an end time.
        $this->assertNull($cash_game->end_time);

        // Set test time in future so we can end session.
        Carbon::setTestNow('tomorrow');

        $cash_game->end();

        $this->assertEquals($cash_game->fresh()->end_time, Carbon::getTestNow());
    }

    public function testATimeCanBeSuppliedWhenEndingACashGame()
    {
        $cash_game = $this->startLiveCashGame();

        $time = Carbon::create('+3 hours')->toDateTimeString();

        $cash_game->end($time);

        $this->assertEquals($cash_game->fresh()->end_time, $time);
    }

    public function testAnEndTimeCannotBeBeforeAStartTime()
    {
        $this->expectException(\App\Exceptions\InvalidDateException::class);

        $cash_game = $this->startLiveCashGame();

        $cash_game->end(Carbon::create('-3 hours')->toDateTimeString());
    }

    public function testACashGameCannotBeStartedIfThereIsAlreadyALiveCashGameInProgress()
    {
        $this->expectException(\App\Exceptions\SessionInProgressException::class);

        $user = factory('App\User')->create();

        $user->startCashGame();
        // Error should be thrown when starting another
        $user->startCashGame();
    }

    public function testCheckingStartingMultipleCashGamesAsLongAsPreviousOnesHaveFinished()
    {
        $user = factory('App\User')->create();

        // Start and finish a cash game.
        $cash_game = $user->startCashGame();
        $cash_game->end(Carbon::create('+1 hour')->toDateTimeString());

        Carbon::setTestNow('+ 3 hours');

        // Start a cash game.
        $cash_game_2 = $user->startCashGame();

        // User's liveCashGame should be cash_game_2.
        $this->assertEquals($user->liveCashGame()->id, $cash_game_2->id);
    }

    public function testCashGameVariablesDefaultToUserDefaults()
    {
        $user = factory('App\User')->create([
            'default_stake_id' => 3,
            'default_limit_id' => 2,
            'default_variant_id' => 1,
            'default_table_size_id' => 2,
        ]);
        
        // Start CashGame with empty attributes
        $cash_game = $user->startCashGame([]);

        $this->assertEquals(3, $cash_game->stake_id);
        $this->assertEquals(2, $cash_game->limit_id);
        $this->assertEquals(1, $cash_game->variant_id);
        $this->assertEquals(2, $cash_game->table_size_id);
    }

    public function testCashGameCanHaveABuyIn()
    {
        $cash_game = $this->startLiveCashGame();

        $cash_game->addBuyIn(500);

        $this->assertCount(1, $cash_game->buyIns);
    }

    public function testCashGameCanHaveMultipleBuyIns()
    {
        $cash_game = $this->startLiveCashGame();

        $cash_game->addBuyIn(500);
        $cash_game->addBuyIn(500);
        $cash_game->addBuyIn(500);

        $this->assertCount(3, $cash_game->buyIns);
    }

    public function testCashGameCanHaveMultipleExpenses()
    {
        $cash_game = $this->startLiveCashGame();

        $cash_game->addExpense(500);
        $cash_game->addExpense(1000);
        $cash_game->addExpense(300);

        $this->assertCount(3, $cash_game->expenses);
    }

    public function testACashGameCanBeCashedOut()
    {
        // Cashing Out ends the session as well as updates the CashGame's profit.
        $cash_game = $this->startLiveCashGame();

        $cash_game->addBuyIn(10000);
        $this->assertNull($cash_game->fresh()->end_time);

        $cash_game->addCashOut(30000);
        
        $this->assertEquals(20000, $cash_game->fresh()->profit);
    }

    public function testACashGameCanOnlyBeCashOutOnce()
    {
        // This error is thrown because the cash_game_id is unique in the CashOut migration
        $this->expectException(\Illuminate\Database\QueryException::class);

        try{
            $cash_game = $this->startLiveCashGame();

            $cash_game->addCashOut(10000);
            $cash_game->addCashOut(10000);
        } finally {
            $this->assertCount(1, $cash_game->cashOut()->get());
            $this->assertInstanceOf(CashOut::class, $cash_game->cashOut);
        }
    }

    public function testCashGameProfitFlow()
    {
        $cash_game = $this->startLiveCashGame();

        $cash_game->addBuyIn(1000);
        $cash_game->addExpense(50);
        $cash_game->addExpense(200);
        $cash_game->addBuyIn(2000);
        $cash_game->addCashOut(1000);

        //CashGame profit should be -1000 -50 -200 -2000 + 1000 = -2250
        $this->assertEquals(-2250, $cash_game->fresh()->profit);

        $this->assertCount(2, $cash_game->buyIns);
        $this->assertCount(2, $cash_game->expenses);
        $this->assertCount(1, $cash_game->cashOut()->get());

        // Change the first Expense to 500 instead of 50
        tap($cash_game->expenses()->first())->update([
            'amount' => 500
        ]);

        // Delete the second buyIn (2000);
        tap($cash_game->buyIns->last())->delete();

        // Update the cashOut value to 4000.
        $cash_game->cashOut->update([
            'amount' => 4000
        ]);

        $cash_game->refresh();

        $this->assertCount(1, $cash_game->buyIns);

        //CashGame profit should now be -1000 -500 -200 + 4000 = 2300
        $this->assertEquals(2300, $cash_game->profit);
    }

    public function testTheUsersBankrollIsUpdatedWhenUpdatingTheCashGamesProfit()
    {
        // There is a Observer on the abstract Game so when the Game (CashGame) profit is updated (i.e. adding buyIn, expenses etc)
        // then the User's bankroll is also updated.
        // Only testing the BuyIn of the GameTransactions as they all work the same because of Positive/NegativeGameTransactionObserver
        // which updates the CashGame's profit.

        $user = factory('App\User')->create();
        $this->signIn($user);

        $cash_game = $user->startCashGame();
        $cash_game->addBuyIn(1000);

        // Original bankroll is 0, but we take off 1000 as we buy in.
        // User's bankroll should be -1000
        $this->assertEquals(-1000, $user->fresh()->bankroll);

        // This should also work if we update the BuyIn.
        $buy_in = $cash_game->buyIns()->first();
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
        $cashOut = $cash_game->addCashOut(2000);
        $this->assertEquals(2000, $user->fresh()->bankroll);

        // Delete the Cash Out and user's bankroll should revert back to 0
        $cashOut->delete();
        $this->assertEquals(0, $user->fresh()->bankroll);
    }

    public function testTheUsersBankrollIsUpdatedWhenACashGameIsDeleted()
    {
        $user = factory('App\User')->create();
        $this->signIn($user);

        $cash_game = $user->startCashGame();
        $cash_game->addBuyIn(1000);
        $cash_game->addExpense(50);
        $cash_game->addExpense(200);
        $cash_game->addBuyIn(2000);
        $cash_game->addCashOut(1000);

        // Check that users bankroll is 7750 (10000-1000-50-200-2000+1000)
        $this->assertEquals(-2250, $user->fresh()->bankroll);
        // CashGame profit is -2250 (-1000-50-200-2000+1000)
        $this->assertEquals(-2250, $cash_game->fresh()->profit);

        // Now if we delete the cash_game the user's bankroll should revert back to the orignal
        // 10000, calculated by the user's current bankroll (7750) minus the cash_games profit (-2250)
        // If the cash_game profit is negative, it adds back on, if positive it should subtract it.
        $cash_game->fresh()->delete();
        $this->assertEquals(0, $user->fresh()->bankroll);
        
        // Test again with positive profit
        $cash_game2 = $user->startCashGame();
        $cash_game2->addCashOut(10000);
        // Orignal bankroll 0 + cashOut 10000 = 10000
        $this->assertEquals(10000, $user->fresh()->bankroll);

        $cash_game2->fresh()->delete();
        $this->assertEquals(0, $user->fresh()->bankroll);
    }

    public function testWhenACashGameIsDeletedItDeletesAllOfItsGameTransactions()
    {
        $user = factory('App\User')->create();
        $this->signIn($user);
        
        $cash_game = $user->startCashGame();
        $cash_game->addBuyIn(1000);
        $cash_game->addExpense(50);
        $cash_game->addExpense(200);
        $cash_game->addBuyIn(2000);
        $cash_game->addCashOut(1000);
        $cash_game->refresh();
        // Make sure counts and bankroll are correct.
        $this->assertCount(2, $cash_game->buyIns);
        $this->assertCount(2, $cash_game->expenses);
        $this->assertCount(1, $cash_game->cashOut()->get());
        $this->assertEquals(-2250, $cash_game->profit);
        $this->assertEquals(-2250, $user->fresh()->bankroll);
        $this->assertCount(1, $user->cashGames);
        
        // When deleting a CashGame it should delete all it's GameTransactions
        // This is handled in Game model Observer delete method.
        // Can't use cascade down migrations because of polymorphic relationship
        $cash_game->delete();

        $user->refresh();
        
        $this->assertCount(0, $user->cashGames);
        $this->assertEquals(0, $user->bankroll);
        $this->assertCount(0, BuyIn::all());
        $this->assertCount(0, Expense::all());
        $this->assertCount(0, CashOut::all());
    }

    public function testCashGameCurrencyDefaultsToUsersCurrency()
    {
        $user = factory('App\User')->create(['currency' => 'USD']);
        $this->signIn($user);
        
        // Start CashGame with empty attributes
        $cash_game = $user->startCashGame([]);

        $this->assertEquals('USD', $cash_game->currency);
    }

    public function testCashGameLocaleProfitIsConvertedToUserCurrency()
    {
        // Create a User with default USD currency
        $user = factory('App\User')->create(['currency' => 'USD']);

        // Create a Cash Game with profit of £1,000 GBP
        $cash_game = $user->cashGames()->create(['currency' => 'GBP']);
        // Add a £1000 GBP Buy In
        $cash_game->addBuyIn(1000);

        // 1 GBP / 1.25 USD.  So locale_profit should equal -$1250 USD
        $this->assertEquals(-1250, $cash_game->locale_profit);
    }
}
