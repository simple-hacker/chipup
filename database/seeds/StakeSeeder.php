<?php

use App\Attributes\Stake;
use Illuminate\Database\Seeder;

class StakeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $stakes = [
            [
                'stake' => '1/1',
                'small_blind' => 1,
                'big_blind' => 1,
            ],
            [
                'stake' => '1/2',
                'small_blind' => 1,
                'big_blind' => 2,
            ],
            [
                'stake' => '1/3',
                'small_blind' => 1,
                'big_blind' => 3,
            ],
            [
                'stake' => '2/4',
                'small_blind' => 2,
                'big_blind' => 4,
            ],
        ];

        Stake::insert($stakes);
    }
}
