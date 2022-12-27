<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RebuyTest extends TestCase
{
    use RefreshDatabase;

    public function testAddingRebuysUpdatesTournamentProfit()
    {
        $tournament = $this->startLiveTournament();

        $this->assertEquals(0, $tournament->profit);
        $tournament->addRebuy(100);
        $this->assertEquals(-100, $tournament->fresh()->profit);
        // Add another rebuy of 100.  Profit should now equal -200
        $tournament->addRebuy(100);
        $this->assertEquals(-200, $tournament->fresh()->profit);
    }

    public function testUpdatingARebuyUpdatesTheTournamentsProfit()
    {
        $tournament = $this->startLiveTournament();

        $rebuy = $tournament->addRebuy(500);
        $this->assertEquals(-500, $tournament->fresh()->profit);

        $rebuy->update([
            'amount' => 1000
        ]);

        // Tournament Profit should equal -1000 instead of -500
        $this->assertEquals(-1000, $tournament->fresh()->profit);
    }

    public function testDeletingARebuyUpdatesTheTournamentsProfit()
    {
        $tournament = $this->startLiveTournament();

        $rebuy = $tournament->addRebuy(500);
        $this->assertEquals(-500, $tournament->fresh()->profit);

        $rebuy->delete();

        // Tournament Profit should equal 0 instead of -500
        $this->assertEquals(0, $tournament->fresh()->profit);
    }

    public function testRebuyDefaultsToSessionCurrency()
    {
        // Create a user with GBP currency default
        $user = \App\User::factory()->create(['currency' => 'GBP']);

        // Create a Cash Game which has USD currency
        $tournament = $user->startTournament(['currency' => 'USD']);
        $rebuy = $tournament->addRebuy(500);

        $this->assertEquals('USD', $rebuy->currency);
    }

    public function testRebuyDefaultsToUserCurrencyIfNoSessionCurrencyIsAvailable()
    {
        // Create a user with GBP currency default
        $user = \App\User::factory()->create(['currency' => 'PLN']);

        // Create a Cash Game which has default user currency
        $tournament = $user->startTournament();
        $rebuy = $tournament->addRebuy(500);

        $this->assertEquals('PLN', $rebuy->currency);
    }

    public function testRebuyCanBeInADifferentCurrency()
    {
        // Create a user with GBP currency default
        $user = \App\User::factory()->create(['currency' => 'GBP']);

        // Assert Cash Game currency is user default of GBP
        $tournament = $this->startLiveTournament($user);
        $this->assertEquals('GBP', $tournament->currency);
        $this->assertEquals(0, $tournament->profit);

        // Cash out $300
        $cash_out = $tournament->addRebuy(300, 'USD');
        $this->assertEquals('USD', $cash_out->currency);

        // 1 GBP = 1.25 USD
        // Cash Game profit $300 USD = £240 GBP
        $this->assertEquals($this->converterTest(-300, 'USD', 'GBP'), $tournament->fresh()->profit);
    }

    public function testRebuyHasAUserLocaleAndSessionLocaleAmounts()
    {
        // Create a user with GBP currency default
        $user = \App\User::factory()->create(['currency' => 'GBP']);

        // Create a Cash Game which has USD currency
        $tournament = $user->tournaments()->create(['currency' => 'USD']);

        // Add a 1000 PLN cash out.
        $rebuy = $tournament->addRebuy('1000', 'PLN');

        $this->assertEquals('PLN', $rebuy->currency);
        $this->assertEquals(1000, $rebuy->amount);

        // Session is in USD
        // 4.9 PLN = 1 GBP = 1.25 USD
        // 1000 PLN = £204.08 GBP = $255.10 USD
        $this->assertEquals($this->converterTest(1000, 'PLN', 'USD'), $rebuy->session_locale_amount);

        // Locale Amount is in GBP because that's user default.
        $this->assertEquals($this->converterTest(1000, 'PLN', 'GBP'), $rebuy->locale_amount);
    }
}
