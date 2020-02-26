<?php

namespace Tests\Unit;

use App\User;
use App\Transactions\CashOut;
use App\CashGame;
use Tests\TestCase;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CashGameTest extends TestCase
{
    use RefreshDatabase;

    public function testACashGameBelongsToAUser()
    {
        $cash_game = factory('App\CashGame')->create();

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

        $time = Carbon::create(2020, 02, 15, 18, 30, 00);

        $cash_game = $user->startCashGame($time);

        $this->assertEquals('2020-02-15 18:30:00', $cash_game->fresh()->start_time);
    }
    
    public function testACashGameCanBeEnded()
    {
        $user = factory('App\User')->create();

        $cash_game = $user->startCashGame();

        // Assert CashGame doesn't have an end time.
        $this->assertNull($cash_game->end_time);

        // Set test time in future so we can end session.
        Carbon::setTestNow('tomorrow');

        $cash_game->end();

        $this->assertEquals($cash_game->fresh()->end_time, Carbon::getTestNow());
    }

    public function testATimeCanBeSuppliedWhenEndingACashGame()
    {
        $user = factory('App\User')->create();

        $cash_game = $user->startCashGame();

        $time = Carbon::create('+3 hours');

        $cash_game->end($time);

        $this->assertEquals($cash_game->fresh()->end_time, $time->toDateTimeString());
    }

    public function testAnEndTimeCannotBeBeforeAStartTime()
    {
        $this->expectException(\App\Exceptions\InvalidDate::class);

        $user = factory('App\User')->create();

        $cash_game = $user->startCashGame();

        $cash_game->end(Carbon::create('-3 hours'));
    }

    public function testACashGameCannotBeStartedIfThereIsAlreadyALiveCashGameInProgress()
    {
        $this->expectException(\App\Exceptions\CashGameInProgress::class);

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
        $cash_game->end(Carbon::create('+1 hour'));

        Carbon::setTestNow('+ 3 hours');

        // Start a cash game.
        $cash_game_2 = $user->startCashGame();

        // User's liveCashGame should be cash_game_2.
        $this->assertEquals($user->liveCashGame()->id, $cash_game_2->id);
    }

    public function testCashGameCanHaveABuyIn()
    {
        $user = factory('App\User')->create();

        $cash_game = $user->startCashGame();
        $cash_game->addBuyIn(500);

        $this->assertCount(1, $cash_game->buyIns);
    }

    public function testCashGameCanHaveMultipleBuyIns()
    {
        $user = factory('App\User')->create();

        $cash_game = $user->startCashGame();
        $cash_game->addBuyIn(500);
        $cash_game->addBuyIn(500);
        $cash_game->addBuyIn(500);

        $this->assertCount(3, $cash_game->buyIns);
    }

    public function testCashGameCanHaveMultipleExpenses()
    {
        $user = factory('App\User')->create();

        $cash_game = $user->startCashGame();
        $cash_game->addExpense(500);
        $cash_game->addExpense(1000);
        $cash_game->addExpense(300);

        $this->assertCount(3, $cash_game->expenses);
    }

    public function testACashGameCanBeCashedOut()
    {
        // Cashing Out ends the session as well as updates the CashGame's profit.

        $user = factory('App\User')->create();
        $cash_game = $user->startCashGame();
        $cash_game->addBuyIn(10000);
        $this->assertNull($cash_game->fresh()->end_time);

        $end_time = Carbon::now()->toDateTimeString();

        $cash_game->cashOut(30000);
        
        $cash_game->refresh();
        
        $this->assertEquals($end_time, $cash_game->end_time);
        $this->assertEquals(20000, $cash_game->profit);
    }

    public function testACashGameCanBeCashedOutAtASuppliedTime()
    {
        $user = factory('App\User')->create();
        $cash_game = $user->startCashGame();

        $end_time = Carbon::create('+3 hours');

        $cash_game->cashOut(30000, $end_time);
                
        $this->assertEquals($end_time->toDateTimeString(), $cash_game->fresh()->end_time);
    }

    public function testACashGameCanOnlyBeCashOutOnce()
    {
        // This error is thrown because the cash_game_id is unique in the CashOut migration
        $this->expectException(\Illuminate\Database\QueryException::class);

        try{
            $user = factory('App\User')->create();
            $cash_game = $user->startCashGame();

            $cash_game->cashOut(10000);
            $cash_game->cashOut(10000);
        } finally {
            $this->assertCount(1, $cash_game->cashOutModel()->get());
            $this->assertInstanceOf(CashOut::class, $cash_game->cashOutModel);
        }
    }

    public function testCashGameProfitFlow()
    {
        $user = factory('App\User')->create();
        $cash_game = $user->startCashGame();
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

        $this->assertEquals(6000, $user->fresh()->bankroll);

        // This should also work if we update the BuyIn.
        $buy_in = $cash_game->buyIns()->first();
        $buy_in->update([
            'amount' => 500
        ]);
        // Bankroll should be 5500 (original 5000 and updated 500)
        // $this->assertEquals(5500, $user->fresh()->bankroll);

        // This should also work if we update the BuyIn.
        $buy_in->delete();
        // Bankroll should be 5000 (original 5000)
        // $this->assertEquals(5500, $user->fresh()->bankroll);
    }

    public function testTheUsersBankrollIsUpdatedWhenACashGameIsDeleted()
    {
        
    }
}
