<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\ExchangeRates;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CurrencyConverterTest extends TestCase
{
    use RefreshDatabase;
    
    public function testCurrencyConverterUsesRatesForSessionDate()
    {
        $user = factory('App\User')->create(['currency' => 'USD']);
        $start_time = Carbon::create(2019, 06, 20, 9, 0, 0);

        // Rates for 2019-06-20
        // 1 GBP => "USD" => 1.2682407044
        // 1 GBP => "CAD" => 1.6696764063

        $cashGame = $user->cashGames()->create([
            'user_id' => $user->id,
            'currency' => 'GBP',
            'start_time' => $start_time->toDateTimeString(),
            'end_time' => $start_time->addHours(2)->toDateTimeString()
        ]);

        // Add a 1000 CAD Buy In
        $buyIn = $cashGame->addBuyIn(1000, 'CAD');

        // Assert Game Transaction is 1000 CAD
        $this->assertEquals('CAD', $buyIn->currency);
        $this->assertEquals(1000, $buyIn->amount);

        // Assert Game Transaction Session Amount is converted to GBP
        // 1000 CAD = 598.92 GBP
        $this->assertEquals(598.92, $buyIn->sessionLocaleAmount);

        // User Currency is USD.
        // 1000 CAD = 598.92 GBP = 759.57 USD
        $this->assertEquals(759.57, $buyIn->localeAmount);
    }

    public function testIfDateIsEarlierThan2018ThenUsesRatesFromFirstJanuary2019()
    {
        // Assert no rates for $start_time
        // No rates for 2018-01-01
        $this->assertEmpty(ExchangeRates::whereDate('date', '=', '2018-01-01')->get()->first());

        // Earliest Rates are 2018-01-02
        // 1 GBP => "USD" => 1.3563342439
        // 1 GBP => "CAD" => 1.7006733893
        $earliestRates = ExchangeRates::whereDate('date', '=', '2018-01-02')->get()->first();
        $this->assertNotEmpty($earliestRates);
        $this->assertEquals(1.3563342439, $earliestRates->rates['USD']);
        $this->assertEquals(1.7006733893, $earliestRates->rates['CAD']);

        // Asssert sessionLocaleAmount and localeAmount use rates for date of the session.
        // Use the same values of Buy Ins and currencies as the test above
        // But final assertion values will be different
        $user = factory('App\User')->create(['currency' => 'USD']);
        $start_time = Carbon::create(2018, 01, 01, 9, 0, 0);

        $cashGame = $user->cashGames()->create([
            'user_id' => $user->id,
            'currency' => 'GBP',
            'start_time' => $start_time->toDateTimeString(),
            'end_time' => $start_time->addHours(2)->toDateTimeString()
        ]);

        // Add a 1000 CAD Buy In
        $buyIn = $cashGame->addBuyIn(1000, 'CAD');

        // Assert Game Transaction is 1000 CAD
        $this->assertEquals('CAD', $buyIn->currency);
        $this->assertEquals(1000, $buyIn->amount);

        // Assert Game Transaction Session Amount is converted to GBP
        // 1000 CAD / 1.7006733893 = 588.00 GBP
        $this->assertEquals(588, $buyIn->sessionLocaleAmount);

        // User Currency is USD.
        // 1000 CAD = 588.00 * 1.3563342439 = 797.53 USD
        $this->assertEquals(797.53, $buyIn->localeAmount);
    }

    public function testCurrencyConverterUsesClosestRatesToDateIfNoRatesForSpecificDate()
    {
        // Assert no rates for $start_time
        // No rates for 2020-06-20
        $this->assertEmpty(ExchangeRates::whereDate('date', '=', '2020-06-20')->get()->first());

        // If no rates are found, it finds the cloest rate which is LESS than the request date
        // Closest rates for earlier date is 2020-06-19
        // 1 GBP => "USD" => 1.2386056019
        // 1 GBP => "CAD" => 1.6804596431
        $earliestRates = ExchangeRates::whereDate('date', '=', '2020-06-19')->get()->first();
        $this->assertNotEmpty($earliestRates);
        $this->assertEquals(1.2386056019, $earliestRates->rates['USD']);
        $this->assertEquals(1.6804596431, $earliestRates->rates['CAD']);

        // Asssert sessionLocaleAmount and localeAmount use rates for date of the session.
        // Use the same values of Buy Ins and currencies as the first test and test above
        // But final assertion values will be different
        $user = factory('App\User')->create(['currency' => 'USD']);
        $start_time = Carbon::create(2020, 06, 19, 9, 0, 0);

        $cashGame = $user->cashGames()->create([
            'user_id' => $user->id,
            'currency' => 'GBP',
            'start_time' => $start_time->toDateTimeString(),
            'end_time' => $start_time->addHours(2)->toDateTimeString()
        ]);

        // Add a 1000 CAD Buy In
        $buyIn = $cashGame->addBuyIn(1000, 'CAD');

        // Assert Game Transaction is 1000 CAD
        $this->assertEquals('CAD', $buyIn->currency);
        $this->assertEquals(1000, $buyIn->amount);

        // Assert Game Transaction Session Amount is converted to GBP
        // 1000 CAD / 1.6804596431 = 595.08 GBP
        $this->assertEquals(595.08, $buyIn->sessionLocaleAmount);

        // User Currency is USD.
        // 1000 CAD = 595.075284375 * 1.2386056019 = 737.06 USD
        $this->assertEquals(737.06, $buyIn->localeAmount);
    }

    public function testIfDateIsGreaterThanLatestRatesThenUseLatestRates()
    {
        $latestRates = ExchangeRates::orderByDesc('date')->first();

        $latestDate = $latestRates->date;

        $latestUSD = $latestRates->rates['USD'];
        $latestCAD = $latestRates->rates['CAD'];

        // Create a CashGame where the start time is one day later than the latest rates.
        // Use the same Buy In amounts and currencies as the test aboves
        // But final assertion values will be different
        $user = factory('App\User')->create(['currency' => 'USD']);
        $start_time = $latestDate->addDays(1);

        $cashGame = $user->cashGames()->create([
            'user_id' => $user->id,
            'currency' => 'GBP',
            'start_time' => $start_time->toDateTimeString(),
            'end_time' => $start_time->addHours(2)->toDateTimeString()
        ]);

        // Add a 1000 CAD Buy In
        $buyIn = $cashGame->addBuyIn(1000, 'CAD');

        // Assert Game Transaction is 1000 CAD
        $this->assertEquals('CAD', $buyIn->currency);
        $this->assertEquals(1000, $buyIn->amount);

        // Assert Game Transaction Session Amount is converted to GBP
        $sessionLocaleAmount = round(1000 / $latestCAD, 2, PHP_ROUND_HALF_UP);
        $this->assertEquals($sessionLocaleAmount, $buyIn->sessionLocaleAmount);

        // User Currency is USD.
        $localeAmount = round((1000 / $latestCAD) * $latestUSD, 2, PHP_ROUND_HALF_UP);
        $this->assertEquals($localeAmount, $buyIn->localeAmount);
    }

    public function testRatesAreCached()
    {
        $rates = ExchangeRates::whereDate('date', '=', '2020-06-19')->first()->rates;
        
        Cache::shouldReceive('rememberForever')
        ->once()
        ->with('exchange_rates_2020-06-19', \Closure::class)
        ->andReturn($rates);

        $user = factory('App\User')->create(['currency' => 'USD']);
        $start_time = Carbon::create(2020, 06, 19, 9, 0, 0);

        $cashGame = $user->cashGames()->create([
            'user_id' => $user->id,
            'currency' => 'GBP',
            'start_time' => $start_time->toDateTimeString(),
            'end_time' => $start_time->addHours(2)->toDateTimeString()
        ]);

        $buyIn = $cashGame->addBuyIn(1000, 'CAD');
        // Rates are cached when calling getAttribute sessionLocaleAmount
        $this->assertEquals(595.08, $buyIn->sessionLocaleAmount);
    }

    public function testRatesAreStoredInCachedAreCorrect()
    {
        $user = factory('App\User')->create(['currency' => 'USD']);
        $start_time = Carbon::create(2020, 06, 19, 9, 0, 0);

        $cashGame = $user->cashGames()->create([
            'user_id' => $user->id,
            'currency' => 'GBP',
            'start_time' => $start_time->toDateTimeString(),
            'end_time' => $start_time->addHours(2)->toDateTimeString()
        ]);

        $buyIn = $cashGame->addBuyIn(1000, 'CAD');
        // Refresh so sessionLocaleAmount, localeAmount is "saved"
        $this->assertEquals(595.08, $buyIn->sessionLocaleAmount);

        $this->assertNotNull(Cache::get('exchange_rates_2020-06-19'));
        $this->assertEquals(Cache::get('exchange_rates_2020-06-19'), ExchangeRates::whereDate('date', '=', '2020-06-19')->first()->rates);
    }
}
