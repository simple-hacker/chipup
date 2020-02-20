<?php

namespace Tests\Unit;

use App\User;
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
}
