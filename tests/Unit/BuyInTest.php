<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BuyInTest extends TestCase
{
    use RefreshDatabase;
    
    public function testAddingBuyInsUpdatesCashGameProfit()
    {
        $cash_game = $this->startLiveCashGame();

        $this->assertEquals(0, $cash_game->profit);
        $cash_game->addBuyIn(500);
        $this->assertEquals(-500, $cash_game->fresh()->profit);
        // Add another buy in of 1000.  Profit should now equal -1500
        $cash_game->addBuyIn(1000);
        $this->assertEquals(-1500, $cash_game->fresh()->profit);
    }

    public function testAddingBuyInUpdatesTournamentProfit()
    {
        $tournament = $this->startLiveTournament();
        
        $this->assertEquals(0, $tournament->profit);
        $tournament->addBuyIn(500);
        $this->assertEquals(-500, $tournament->fresh()->profit);
    }

    public function testUpdatingABuyInUpdatesTheGameTypesProfit()
    {
        // Only testing CashGame which is a type of Game
        $cash_game = $this->startLiveCashGame();

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
        $cash_game = $this->startLiveCashGame();

        $buy_in = $cash_game->addBuyIn(500);
        $this->assertEquals(-500, $cash_game->fresh()->profit);

        $buy_in->delete();

        // CashGame Profit should equal 0 instead of -500
        $this->assertEquals(0, $cash_game->fresh()->profit);
    }

    public function testBuyInDefaultsToSessionCurrency()
    {
        // Create a user with GBP currency default
        $user = factory('App\User')->create(['currency' => 'GBP']);

        // Create a Cash Game which has USD currency
        $cashGame = $user->startCashGame(['currency' => 'USD']);
        $buyIn = $cashGame->addBuyIn(500);

        $this->assertEquals('USD', $buyIn->currency);
    }

    public function testCashOutDefaultsToUserCurrencyIfNoSessionCurrencyIsAvailable()
    {
        // Create a user with GBP currency default
        $user = factory('App\User')->create(['currency' => 'PLN']);

        // Create a Cash Game which has default user currency
        $cashGame = $user->startCashGame();
        $buyIn = $cashGame->addBuyIn(500);

        $this->assertEquals('PLN', $buyIn->currency);
    }

    public function testBuyInCanBeInADifferentCurrency()
    {
        // Create a user with GBP currency default
        $user = factory('App\User')->create(['currency' => 'GBP']);

        // Assert Cash Game currency is user default of GBP
        $cash_game = $this->startLiveCashGame($user);
        $this->assertEquals('GBP', $cash_game->currency);
        $this->assertEquals(0, $cash_game->profit);
        
        // Cash out $300
        $cash_out = $cash_game->addBuyIn(300, 'USD');
        $this->assertEquals('USD', $cash_out->currency);

        // 1 GBP = 1.25 USD
        // Cash Game profit $300 USD = £240 GBP
        $this->assertEquals(-240, $cash_game->fresh()->profit);
    }

    public function testBuyInHasAUserLocaleAndSessionLocaleAmounts()
    {
        // Create a user with GBP currency default
        $user = factory('App\User')->create(['currency' => 'GBP']);

        // Create a Cash Game which has USD currency
        $cashGame = $user->cashGames()->create(['currency' => 'USD']);

        // Add a 1000 PLN cash out.
        $buyIn = $cashGame->addBuyIn('1000', 'PLN');

        $this->assertEquals('PLN', $buyIn->currency);
        $this->assertEquals(1000, $buyIn->amount);

        // Session is in USD
        // 4.9 PLN = 1 GBP = 1.25 USD
        // 1000 PLN = £204.08 GBP = $255.10 USD
        $this->assertEquals(255.10, $buyIn->sessionLocaleAmount);

        // Locale Amount is in GBP because that's user default.
        $this->assertEquals(204.08, $buyIn->localeAmount);
    }
}
