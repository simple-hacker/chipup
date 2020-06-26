<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CashOutTest extends TestCase
{
    use RefreshDatabase;
    
    public function testCashingOutUpdatesTheCashGameProfit()
    {
        $cashGame = $this->startLiveCashGame();
        $cashGame->addBuyIn(10000);
        $cashGame->addCashOut(30000);
        $this->assertEquals(20000, $cashGame->fresh()->profit);
    }

    public function testCashingOutUpdatesTheTournamentProfit()
    {
        $tournament = $this->startLiveTournament();
        $tournament->addBuyIn(10000);
        $tournament->addCashOut(30000);
        $this->assertEquals(20000, $tournament->fresh()->profit);
    }

    public function testUpdatingACashOutUpdatesTheGameTypesProfit()
    {
        // Only testing CashGame which is a type of Game
        $cashGame = $this->startLiveCashGame();

        $cashGame->addBuyIn(10000);
        $cash_out = $cashGame->addCashOut(30000);
        // -10,000 + 30,000 = 20,000
        $this->assertEquals(20000, $cashGame->fresh()->profit);

        $cash_out->update([
            'amount' => 50000
        ]);

        // CashGame Profit should equal 40,000 (-10,000 + 50,000) instead of 20,000
        $this->assertEquals(40000, $cashGame->fresh()->profit);
    }

    public function testDeletingACashOutUpdatesTheGameTypesProfit()
    {
        // Only testing CashGame which is a type of Game
        $cashGame = $this->startLiveCashGame();
        
        $cashGame->addBuyIn(10000);
        $cash_out = $cashGame->addCashOut(30000);
        // -10,000 + 30,000 = 20,000
        $this->assertEquals(20000, $cashGame->fresh()->profit);

        $cash_out->delete();

        // CashGame Profit should equal -10,000 (buyIn) instead of 20,000
        $this->assertEquals(-10000, $cashGame->fresh()->profit);
    }

    public function testCashOutDefaultsToSessionCurrency()
    {
        // Create a user with GBP currency default
        $user = factory('App\User')->create(['currency' => 'GBP']);

        // Create a Cash Game which has USD currency
        $cashGame = $user->startCashGame(['currency' => 'USD']);
        $cashOut = $cashGame->addCashOut(500);

        $this->assertEquals('USD', $cashOut->currency);
    }

    public function testCashOutDefaultsToUserCurrencyIfNoSessionCurrencyIsAvailable()
    {
        // Create a user with GBP currency default
        $user = factory('App\User')->create(['currency' => 'PLN']);

        // Create a Cash Game which has default user currency
        $cashGame = $user->startCashGame();
        $cashOut = $cashGame->addCashOut(500);

        $this->assertEquals('PLN', $cashOut->currency);
    }

    public function testCashOutCanBeInADifferentCurrency()
    {
        // Create a user with GBP currency default
        $user = factory('App\User')->create(['currency' => 'GBP']);

        // Assert Cash Game currency is user default of GBP
        $cashGame = $this->startLiveCashGame($user);
        $this->assertEquals('GBP', $cashGame->currency);
        $this->assertEquals(0, $cashGame->profit);
        
        // Cash out $300
        $cash_out = $cashGame->addCashOut(300, 'USD');
        $this->assertEquals('USD', $cash_out->currency);

        // 1 GBP = 1.25 USD
        // Cash Game profit $300 USD = ~£240 GBP
        $this->assertEquals($this->converterTest(300, 'USD', 'GBP'), $cashGame->fresh()->profit);
    }

    public function testCashOutHasAUserLocaleAndSessionLocaleAmounts()
    {
        // Create a user with GBP currency default
        $user = factory('App\User')->create(['currency' => 'GBP']);

        // Create a Cash Game which has USD currency
        $cashGame = $user->cashGames()->create(['currency' => 'USD']);

        // Add a 1000 PLN cash out.
        $cashOut = $cashGame->addCashOut('1000', 'PLN');

        $this->assertEquals('PLN', $cashOut->currency);
        $this->assertEquals(1000, $cashOut->amount);

        // Session is in USD
        // 4.9 PLN = 1 GBP = 1.25 USD
        // 1000 PLN = £204.08 GBP = $255.10 USD
        $this->assertEquals($this->converterTest(1000, 'PLN', 'USD'), $cashOut->sessionLocaleAmount);

        // Locale Amount is in GBP because that's user default.
        $this->assertEquals($this->converterTest(1000, 'PLN', 'GBP'), $cashOut->localeAmount);
    }
}
