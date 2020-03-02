<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExpenseTest extends TestCase
{
    use RefreshDatabase;
    
    public function testAddingExpensesUpdatesCashGameProfit()
    {
        $cash_game = $this->createCashGame();

        $this->assertEquals(0, $cash_game->profit);
        $cash_game->addExpense(50);
        $this->assertEquals(-50, $cash_game->fresh()->profit);
        // Add another expense of 100.  Profit should now equal -150
        $cash_game->addExpense(100);
        $this->assertEquals(-150, $cash_game->fresh()->profit);
    }

    public function testAddingExpensesUpdatesTournamentProfit()
    {
        $tournament = $this->createTournament();

        $this->assertEquals(0, $tournament->profit);
        $tournament->addExpense(50);
        $this->assertEquals(-50, $tournament->fresh()->profit);
        // Add another expense of 100.  Profit should now equal -150
        $tournament->addExpense(100);
        $this->assertEquals(-150, $tournament->fresh()->profit);
    }

    public function testUpdatingAExpenseUpdatesTheGameTypesProfit()
    {
        // Only testing CashGame which is a type of Game
        $cash_game = $this->createCashGame();

        $expense = $cash_game->addExpense(500);
        $this->assertEquals(-500, $cash_game->fresh()->profit);

        $expense->update([
            'amount' => 1000
        ]);

        // CashGame Profit should equal -1000 instead of -500
        $this->assertEquals(-1000, $cash_game->fresh()->profit);
    }

    public function testDeletingAExpenseUpdatesTheGameTypesProfit()
    {
        // Only testing CashGame which is a type of Game
        $cash_game = $this->createCashGame();
        
        $expense = $cash_game->addExpense(500);
        $this->assertEquals(-500, $cash_game->fresh()->profit);

        $expense->delete();

        // CashGame Profit should equal 0 instead of -500
        $this->assertEquals(0, $cash_game->fresh()->profit);
    }
}
