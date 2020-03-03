<?php

use App\Attributes\Limit;
use Illuminate\Database\Seeder;

class LimitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $limits = [
            ['limit' => 'No Limit'],
            ['limit' => 'Pot Limit'],
            ['limit' => 'Limit'],
            ['limit' => 'Mixed Limit'],
            ['limit' => 'Spread Limit'],
        ];

        Limit::insert($limits);
    }
}
