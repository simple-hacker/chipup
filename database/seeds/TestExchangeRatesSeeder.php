<?php

use App\ExchangeRates;
use Illuminate\Database\Seeder;

class TestExchangeRatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $contents = '{"rates":{"CAD":1.68,"HKD":9.67,"ISK":173.01,"PHP":62.54,"DKK":8.22,"HUF":386.66,"CZK":29.36,"GBP":1.0,"RON":5.344,"SEK":11.60,"IDR":17683.20,"INR":94.27,"BRL":6.51,"RUB":85.78,"HRK":8.36,"JPY":133.56,"THB":38.61,"CHF":1.18,"EUR":1.10,"MYR":5.33,"BGN":2.15,"TRY":8.56,"CNY":8.82,"NOK":11.84,"NZD":1.91,"ZAR":21.48,"USD":1.25,"MXN":27.86,"SGD":1.73,"AUD":1.79,"ILS":4.28,"KRW":1507.78,"PLN":4.9},"base":"GBP","date":"2020-06-23"}';
        $contents = json_decode($contents);


        $rates = [
            'base_rate' => $contents->base,
            'date' => $contents->date,
            'rates' => json_encode($contents->rates),
        ];

        ExchangeRates::insert($rates);
    }
}
