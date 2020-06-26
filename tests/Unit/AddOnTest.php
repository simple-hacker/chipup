<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AddOnTest extends TestCase
{
    use RefreshDatabase;

    public function testAddingAddOnsUpdatesTournamentProfit()
    {
        $tournament = $this->startLiveTournament();
        
        $this->assertEquals(0, $tournament->profit);
        $tournament->addAddOn(50);

        $this->assertEquals(-50, $tournament->fresh()->profit);
        // Add another AddOn of 100.  Profit should now equal -150
        $tournament->addAddOn(100);
        $this->assertEquals(-150, $tournament->fresh()->profit);
    }

    public function testUpdatingAAddOnUpdatesTheTournamentsProfit()
    {
        $tournament = $this->startLiveTournament();
        
        $addOn = $tournament->addAddOn(500);
        $this->assertEquals(-500, $tournament->fresh()->profit);

        $addOn->update([
            'amount' => 1000
        ]);

        // Tournament Profit should equal -1000 instead of -500
        $this->assertEquals(-1000, $tournament->fresh()->profit);
    }

    public function testDeletingAAddOnUpdatesTheTournamentsProfit()
    {
        $tournament = $this->startLiveTournament();
        
        $addOn = $tournament->addAddOn(500);
        $this->assertEquals(-500, $tournament->fresh()->profit);

        $addOn->delete();

        // Tournament Profit should equal 0 instead of -500
        $this->assertEquals(0, $tournament->fresh()->profit);
    }

    public function testAddOnDefaultsToSessionCurrency()
    {
        // Create a user with GBP currency default
        $user = factory('App\User')->create(['currency' => 'GBP']);

        // Create a Cash Game which has USD currency
        $tournament = $user->startTournament(['currency' => 'USD']);
        $addOn = $tournament->addAddOn(500);

        $this->assertEquals('USD', $addOn->currency);
    }

    public function testAddOnDefaultsToUserCurrencyIfNoSessionCurrencyIsAvailable()
    {
        // Create a user with GBP currency default
        $user = factory('App\User')->create(['currency' => 'PLN']);

        // Create a Cash Game which has default user currency
        $tournament = $user->startTournament();
        $addOn = $tournament->addAddOn(500);

        $this->assertEquals('PLN', $addOn->currency);
    }

    public function testAddOnCanBeInADifferentCurrency()
    {
        // Create a user with GBP currency default
        $user = factory('App\User')->create(['currency' => 'GBP']);

        // Assert Cash Game currency is user default of GBP
        $tournament = $this->startLiveTournament($user);
        $this->assertEquals('GBP', $tournament->currency);
        $this->assertEquals(0, $tournament->profit);
        
        // Cash out $300
        $cash_out = $tournament->addAddOn(300, 'USD');
        $this->assertEquals('USD', $cash_out->currency);

        // 1 GBP = 1.25 USD
        // Cash Game profit $300 USD = £240 GBP
        $this->assertEquals($this->converterTest(-300, 'USD', 'GBP'), $tournament->fresh()->profit);
    }

    public function testAddOnHasAUserLocaleAndSessionLocaleAmounts()
    {
        // Create a user with GBP currency default
        $user = factory('App\User')->create(['currency' => 'GBP']);

        // Create a Cash Game which has USD currency
        $tournament = $user->tournaments()->create(['currency' => 'USD']);

        // Add a 1000 PLN cash out.
        $addOn = $tournament->addAddOn('1000', 'PLN');

        $this->assertEquals('PLN', $addOn->currency);
        $this->assertEquals(1000, $addOn->amount);

        // Session is in USD
        // 4.9 PLN = 1 GBP = 1.25 USD
        // 1000 PLN = £204.08 GBP = $255.10 USD
        $this->assertEquals($this->converterTest(1000, 'PLN', 'USD'), $addOn->sessionLocaleAmount);

        // Locale Amount is in GBP because that's user default.
        $this->assertEquals($this->converterTest(1000, 'PLN', 'GBP'), $addOn->localeAmount);
    }
}
