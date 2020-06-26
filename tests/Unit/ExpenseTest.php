<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExpenseTest extends TestCase
{
    use RefreshDatabase;
    
    public function testAddingExpensesUpdatesCashGameProfit()
    {
        $cashGame = $this->startLiveCashGame();

        $this->assertEquals(0, $cashGame->profit);
        $cashGame->addExpense(50);
        $this->assertEquals(-50, $cashGame->fresh()->profit);
        // Add another expense of 100.  Profit should now equal -150
        $cashGame->addExpense(100);
        $this->assertEquals(-150, $cashGame->fresh()->profit);
    }

    public function testAddingExpensesUpdatesTournamentProfit()
    {
        $tournament = $this->startLiveTournament();

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
        $cashGame = $this->startLiveCashGame();

        $expense = $cashGame->addExpense(500);
        $this->assertEquals(-500, $cashGame->fresh()->profit);

        $expense->update([
            'amount' => 1000
        ]);

        // CashGame Profit should equal -1000 instead of -500
        $this->assertEquals(-1000, $cashGame->fresh()->profit);
    }

    public function testDeletingAExpenseUpdatesTheGameTypesProfit()
    {
        // Only testing CashGame which is a type of Game
        $cashGame = $this->startLiveCashGame();
        
        $expense = $cashGame->addExpense(500);
        $this->assertEquals(-500, $cashGame->fresh()->profit);

        $expense->delete();

        // CashGame Profit should equal 0 instead of -500
        $this->assertEquals(0, $cashGame->fresh()->profit);
    }

    public function testExpenseDefaultsToSessionCurrency()
    {

        // Create a user with GBP currency default
        $user = factory('App\User')->create(['currency' => 'GBP']);

        // Create a Cash Game which has USD currency
        $cashGame = $user->startCashGame(['currency' => 'USD']);
        $expense = $cashGame->addExpense(500);

        $this->assertEquals('USD', $expense->currency);
    }

    public function testExpenseDefaultsToUserCurrencyIfNoSessionCurrencyIsAvailable()
    {
        // Create a user with GBP currency default
        $user = factory('App\User')->create(['currency' => 'PLN']);

        // Create a Cash Game which has default user currency
        $cashGame = $user->startCashGame();
        $expense = $cashGame->addExpense(500);

        $this->assertEquals('PLN', $expense->currency);
    }

    public function testExpenseCanBeInADifferentCurrency()
    {
        // Create a user with GBP currency default
        $user = factory('App\User')->create(['currency' => 'GBP']);

        // Assert Cash Game currency is user default of GBP
        $cashGame = $this->startLiveCashGame($user);
        $this->assertEquals('GBP', $cashGame->currency);
        $this->assertEquals(0, $cashGame->profit);
        
        // Cash out $300
        $cash_out = $cashGame->addExpense(300, 'USD');
        $this->assertEquals('USD', $cash_out->currency);

        // 1 GBP = 1.25 USD
        // Cash Game profit $300 USD = £240 GBP
        $this->assertEquals($this->converterTest(-300, 'USD', 'GBP'), $cashGame->fresh()->profit);
    }

    public function testExpenseHasAUserLocaleAndSessionLocaleAmounts()
    {
        // Create a user with GBP currency default
        $user = factory('App\User')->create(['currency' => 'GBP']);

        // Create a Cash Game which has USD currency
        $cashGame = $user->cashGames()->create(['currency' => 'USD']);

        // Add a 1000 PLN cash out.
        $expense = $cashGame->addExpense('1000', 'PLN');

        $this->assertEquals('PLN', $expense->currency);
        $this->assertEquals(1000, $expense->amount);

        // Session is in USD
        // 4.9 PLN = 1 GBP = 1.25 USD
        // 1000 PLN = £204.08 GBP = $255.10 USD
        $this->assertEquals($this->converterTest(1000, 'PLN', 'USD'), $expense->sessionLocaleAmount);

        // Locale Amount is in GBP because that's user default.
        $this->assertEquals($this->converterTest(1000, 'PLN', 'GBP'), $expense->localeAmount);
    }
}
