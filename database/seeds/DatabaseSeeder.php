<?php

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
    }
}
