<?php

namespace App\Currency;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RetrieveLatestExchangeRates {

    /**
    * Get the latest rates in base GBP
    * Rates are updated every day at 16:00 CET I think
    * Run schedule at 16:00 server zulu time every day.
    *
    * @return void
    */
    public function __invoke()
    {
        // Note: exchangeratesapi have started charging and now need an API key.
        return;

        $response = json_decode(file_get_contents('https://api.exchangeratesapi.io/latest?base=GBP'));

        // Update rates with the retrieve date or create new row in database.
        ExchangeRates::updateOrCreate(
            ['date' => $response->date],
            ['date' => $response->date, 'rates' => $response->rates]
        );

        Log::info('Daily exchange rates imported for ' . $response->date);
    }
}
