<?php

namespace Tests\Unit;

use App\User;
use App\CashOut;
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

    public function testAddingBuyInsUpdatesCashGameProfit()
    {
        $user = factory('App\User')->create();

        $cash_game = $user->startCashGame();
        $this->assertEquals(0, $cash_game->profit);
        $cash_game->addBuyIn(500);
        $this->assertEquals(-500, $cash_game->fresh()->profit);
        // Add another buy in of 1000.  Profit should now equal -1500
        $cash_game->addBuyIn(1000);
        $this->assertEquals(-1500, $cash_game->fresh()->profit);
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

    public function testAddingExpensesUpdatesCashGameProfit()
    {
        $user = factory('App\User')->create();

        $cash_game = $user->startCashGame();
        $this->assertEquals(0, $cash_game->profit);
        $cash_game->addExpense(50);
        $this->assertEquals(-50, $cash_game->fresh()->profit);
        // Add another expense of 100.  Profit should now equal -150
        $cash_game->addExpense(100);
        $this->assertEquals(-150, $cash_game->fresh()->profit);
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
}
