<?php

namespace Tests\Unit;

use App\Currency\ExchangeRates;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BuyInTest extends TestCase
{
    use RefreshDatabase;

    public function testAddingBuyInsUpdatesCashGameProfit()
    {
        $cashGame = $this->startLiveCashGame();

        $this->assertEquals(0, $cashGame->profit);
        $cashGame->addBuyIn(500);
        $this->assertEquals(-500, $cashGame->fresh()->profit);
        // Add another buy in of 1000.  Profit should now equal -1500
        $cashGame->addBuyIn(1000);
        $this->assertEquals(-1500, $cashGame->fresh()->profit);
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
        $cashGame = $this->startLiveCashGame();

        $buy_in = $cashGame->addBuyIn(500);
        $this->assertEquals(-500, $cashGame->fresh()->profit);

        $buy_in->update([
            'amount' => 1000
        ]);

        // CashGame Profit should equal -1000 instead of -500
        $this->assertEquals(-1000, $cashGame->fresh()->profit);
    }

    public function testDeletingABuyInUpdatesTheGameTypesProfit()
    {
        // Only testing CashGame which is a type of Game
        $cashGame = $this->startLiveCashGame();

        $buy_in = $cashGame->addBuyIn(500);
        $this->assertEquals(-500, $cashGame->fresh()->profit);

        $buy_in->delete();

        // CashGame Profit should equal 0 instead of -500
        $this->assertEquals(0, $cashGame->fresh()->profit);
    }

    public function testBuyInDefaultsToSessionCurrency()
    {
        // Create a user with GBP currency default
        $user = \App\Models\User::factory()->create(['currency' => 'GBP']);

        // Create a Cash Game which has USD currency
        $cashGame = $user->startCashGame(['currency' => 'USD']);
        $buyIn = $cashGame->addBuyIn(500);

        $this->assertEquals('USD', $buyIn->currency);
    }

    public function testCashOutDefaultsToUserCurrencyIfNoSessionCurrencyIsAvailable()
    {
        // Create a user with GBP currency default
        $user = \App\Models\User::factory()->create(['currency' => 'PLN']);

        // Create a Cash Game which has default user currency
        $cashGame = $user->startCashGame();
        $buyIn = $cashGame->addBuyIn(500);

        $this->assertEquals('PLN', $buyIn->currency);
    }

    public function testBuyInCanBeInADifferentCurrency()
    {
        // Create a user with GBP currency default
        $user = \App\Models\User::factory()->create(['currency' => 'GBP']);

        // Assert Cash Game currency is user default of GBP
        $cashGame = $this->startLiveCashGame($user);
        $this->assertEquals('GBP', $cashGame->currency);
        $this->assertEquals(0, $cashGame->profit);

        // Cash out $300
        $cash_out = $cashGame->addBuyIn(300, 'USD');
        $this->assertEquals('USD', $cash_out->currency);

        // 1 GBP = 1.25 USD
        // Cash Game profit $300 USD = £240 GBP
        $this->assertEquals($this->converterTest(-300, 'USD', 'GBP'), $cashGame->fresh()->profit);
    }

    public function testBuyInHasAUserLocaleAndSessionLocaleAmounts()
    {
        // Create a user with GBP currency default
        $user = \App\Models\User::factory()->create(['currency' => 'GBP']);

        // Create a Cash Game which has USD currency
        $cashGame = $user->cashGames()->create(['currency' => 'USD']);

        // Add a 1000 PLN cash out.
        $buyIn = $cashGame->addBuyIn('1000', 'PLN');

        $this->assertEquals('PLN', $buyIn->currency);
        $this->assertEquals(1000, $buyIn->amount);

        // Session is in USD
        // 4.9 PLN = 1 GBP = 1.25 USD
        // 1000 PLN = £204.08 GBP = $255.10 USD
        $this->assertEquals($this->converterTest(1000, 'PLN', 'USD'), $buyIn->session_locale_amount);

        // Locale Amount is in GBP because that's user default.
        $this->assertEquals($this->converterTest(1000, 'PLN', 'GBP'), $buyIn->locale_amount);
    }
}
