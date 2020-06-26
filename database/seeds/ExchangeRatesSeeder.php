<?php

use App\Currency\ExchangeRates;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class ExchangeRatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $json = json_decode(Storage::get('rates.json'), true);
        $allRates = $json['rates'];
        ksort($allRates);

        $insert = [];

        foreach ($allRates as $date => $rates) {
            $insert[] = ['date' => $date, 'rates' => json_encode($rates)];
        }

        ExchangeRates::insert($insert);
    }
}
