<?php

namespace Database\Seeders;

use Database\Seeders\LimitSeeder;
use Database\Seeders\VariantSeeder;
use Database\Seeders\TableSizeSeeder;
use Database\Seeders\ExchangeRatesSeeder;
use Database\Seeders\StakeSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(StakeSeeder::class);
        $this->call(VariantSeeder::class);
        $this->call(LimitSeeder::class);
        $this->call(TableSizeSeeder::class);
        $this->call(ExchangeRatesSeeder::class);
    }
}
