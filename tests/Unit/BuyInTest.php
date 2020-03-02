<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BuyInTest extends TestCase
{
    use RefreshDatabase;
    
    public function testAddingBuyInsUpdatesCashGameProfit()
    {
        $cash_game = $this->createCashGame();

        $this->assertEquals(0, $cash_game->profit);
        $cash_game->addBuyIn(500);
        $this->assertEquals(-500, $cash_game->fresh()->profit);
        // Add another buy in of 1000.  Profit should now equal -1500
        $cash_game->addBuyIn(1000);
        $this->assertEquals(-1500, $cash_game->fresh()->profit);
    }

    public function testAddingBuyInUpdatesTournamentProfit()
    {
        $tournament = $this->createTournament();
        
        $this->assertEquals(0, $tournament->profit);
        $tournament->addBuyIn(500);
        $this->assertEquals(-500, $tournament->fresh()->profit);
    }

    public function testUpdatingABuyInUpdatesTheGameTypesProfit()
    {
        // Only testing CashGame which is a type of Game
        $cash_game = $this->createCashGame();

        $buy_in = $cash_game->addBuyIn(500);
        $this->assertEquals(-500, $cash_game->fresh()->profit);

        $buy_in->update([
            'amount' => 1000
        ]);

        // CashGame Profit should equal -1000 instead of -500
        $this->assertEquals(-1000, $cash_game->fresh()->profit);
    }

    public function testDeletingABuyInUpdatesTheGameTypesProfit()
    {
        // Only testing CashGame which is a type of Game
        $cash_game = $this->createCashGame();

        $buy_in = $cash_game->addBuyIn(500);
        $this->assertEquals(-500, $cash_game->fresh()->profit);

        $buy_in->delete();

        // CashGame Profit should equal 0 instead of -500
        $this->assertEquals(0, $cash_game->fresh()->profit);
    }
}
