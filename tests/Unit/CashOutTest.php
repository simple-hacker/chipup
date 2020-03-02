<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CashOutTest extends TestCase
{
    use RefreshDatabase;
    
    public function testCashingOutUpdatesTheCashGameProfit()
    {
        $cash_game = $this->createCashGame();
        $cash_game->addBuyIn(10000);
        $cash_game->cashOut(30000);
        $this->assertEquals(20000, $cash_game->fresh()->profit);
    }

    public function testCashingOutUpdatesTheTournamentProfit()
    {
        $tournament = $this->createTournament();
        $tournament->addBuyIn(10000);
        $tournament->cashOut(30000);
        $this->assertEquals(20000, $tournament->fresh()->profit);
    }

    public function testUpdatingACashOutUpdatesTheGameTypesProfit()
    {
        // Only testing CashGame which is a type of Game
        $cash_game = $this->createCashGame();

        $cash_game->addBuyIn(10000);
        $cash_out = $cash_game->cashOut(30000);
        // -10,000 + 30,000 = 20,000
        $this->assertEquals(20000, $cash_game->fresh()->profit);

        $cash_out->update([
            'amount' => 50000
        ]);

        // CashGame Profit should equal 40,000 (-10,000 + 50,000) instead of 20,000
        $this->assertEquals(40000, $cash_game->fresh()->profit);
    }

    public function testDeletingACashOutUpdatesTheGameTypesProfit()
    {
        // Only testing CashGame which is a type of Game
        $cash_game = $this->createCashGame();
        
        $cash_game->addBuyIn(10000);
        $cash_out = $cash_game->cashOut(30000);
        // -10,000 + 30,000 = 20,000
        $this->assertEquals(20000, $cash_game->fresh()->profit);

        $cash_out->delete();

        // CashGame Profit should equal -10,000 (buyIn) instead of 20,000
        $this->assertEquals(-10000, $cash_game->fresh()->profit);
    }
}
