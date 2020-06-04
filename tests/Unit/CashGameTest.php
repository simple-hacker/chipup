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

        $cash_game->cashOut(30000);
        
        $this->assertEquals(20000, $cash_game->fresh()->profit);
    }

    public function testACashGameCanOnlyBeCashOutOnce()
    {
        // This error is thrown because the cash_game_id is unique in the CashOut migration
        $this->expectException(\Illuminate\Database\QueryException::class);

        try{
            $cash_game = $this->startLiveCashGame();

            $cash_game->cashOut(10000);
            $cash_game->cashOut(10000);
        } finally {
            $this->assertCount(1, $cash_game->cashOutModel()->get());
            $this->assertInstanceOf(CashOut::class, $cash_game->cashOutModel);
        }
    }

    public function testCashGameProfitFlow()
    {
        $cash_game = $this->startLiveCashGame();

        $cash_game->addBuyIn(1000);
        $cash_game->addExpense(50);
        $cash_game->addExpense(200);
        $cash_game->addBuyIn(2000);
        $cash_game->cashOut(1000);

        //CashGame profit should be -1000 -50 -200 -2000 + 1000 = -2250
        $this->assertEquals(-2250, $cash_game->fresh()->profit);

        $this->assertCount(2, $cash_game->buyIns);
        $this->assertCount(2, $cash_game->expenses);
        $this->assertCount(1, $cash_game->cashOutModel()->get());

        // Change the first Expense to 500 instead of 50
        tap($cash_game->expenses()->first())->update([
            'amount' => 500
        ]);

        // Delete the second buyIn (2000);
        tap($cash_game->buyIns->last())->delete();

        // Update the cashOut value to 4000.
        $cash_game->cashOutModel->update([
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

        $user = factory('App\User')->create([
            'bankroll' => 5000
        ]);
        $cash_game = $user->startCashGame();
        $cash_game->addBuyIn(1000);

        // Original bankroll is 5000, but we take off 1000 as we buy in.
        // User's bankroll should be 4000
        $this->assertEquals(4000, $user->fresh()->bankroll);

        // This should also work if we update the BuyIn.
        $buy_in = $cash_game->buyIns()->first();
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
        $cash_game->cashOut(2000);
        // We're back to the original 5000, but we cashed out for 2000.  Bankroll = 7000
        $this->assertEquals(7000, $user->fresh()->bankroll);
        
    }

    public function testTheUsersBankrollIsUpdatedWhenACashGameIsDeleted()
    {
        $user = factory('App\User')->create([
            'bankroll' => 10000
        ]);
        $cash_game = $user->startCashGame();
        $cash_game->addBuyIn(1000);
        $cash_game->addExpense(50);
        $cash_game->addExpense(200);
        $cash_game->addBuyIn(2000);
        $cash_game->cashOut(1000);

        // Check that users bankroll is 7750 (10000-1000-50-200-2000+1000)
        $this->assertEquals(7750, $user->fresh()->bankroll);
        // CashGame profit is -2250 (-1000-50-200-2000+1000)
        $this->assertEquals(-2250, $cash_game->fresh()->profit);

        // Now if we delete the cash_game the user's bankroll should revert back to the orignal
        // 10000, calculated by the user's current bankroll (7750) minus the cash_games profit (-2250)
        // If the cash_game profit is negative, it adds back on, if positive it should subtract it.
        $cash_game->fresh()->delete();
        $this->assertEquals(10000, $user->fresh()->bankroll);
        
        // Test again with positive profit
        $cash_game2 = $user->startCashGame();
        $cash_game2->cashOut(10000);
        // Orignal bankroll 10000 + cashOut 10000 = 20000
        $this->assertEquals(20000, $user->fresh()->bankroll);

        $cash_game2->fresh()->delete();
        $this->assertEquals(10000, $user->fresh()->bankroll);
    }

    public function testWhenACashGameIsDeletedItDeletesAllOfItsGameTransactions()
    {
        $user = factory('App\User')->create();
        $cash_game = $user->startCashGame();
        $cash_game->addBuyIn(1000);
        $cash_game->addExpense(50);
        $cash_game->addExpense(200);
        $cash_game->addBuyIn(2000);
        $cash_game->cashOut(1000);
        $cash_game->refresh();
        // Make sure counts and bankroll are correct.
        $this->assertCount(2, $cash_game->buyIns);
        $this->assertCount(2, $cash_game->expenses);
        $this->assertCount(1, $cash_game->cashOutModel()->get());
        $this->assertEquals(-2250, $cash_game->profit);
        $this->assertEquals(7750, $user->fresh()->bankroll);
        $this->assertCount(1, $user->cashGames);
        
        // When deleting a CashGame it shoudl delete all it's GameTransactions
        // This is handled in Game model Observer delete method.
        // Can't use cascade down migrations because of polymorphic relationship
        $cash_game->delete();

        $user->refresh();
        
        $this->assertCount(0, $user->cashGames);
        $this->assertEquals(10000, $user->bankroll);
        $this->assertCount(0, BuyIn::all());
        $this->assertCount(0, Expense::all());
        $this->assertCount(0, CashOut::all());
    }
}
