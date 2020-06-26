<?php

namespace App\Currency;

class RetrieveLatestExchangeRates {

    /**
    * Get the latest rates in base GBP
    * Rates are updated every day at 14:00 CET I think
    * Run schedule at 15:00 every day.
    *
    * @return void
    */
    public function __invoke()
    {
        // NOTE: Can't use Http Client as it is only available to Laravel 7.x
        // Currently only on Laravel 6.2
        $response = json_decode(file_get_contents('https://api.exchangeratesapi.io/latest?base=GBP'));

        // Update rates with the retrieve date or create new row in database.
        ExchangeRates::updateOrCreate(
            ['date' => $response->date],
            ['date' => $response->date, 'rates' => $response->rates]
        );

        \Log::info('Daily exchange rates imported for ' . $response->date);

        Mail::raw('Daily exchange rates imported for ' . $response->date, function ($message) {
            $message->to('michael.perks@live.co.uk');
        });
    }
}