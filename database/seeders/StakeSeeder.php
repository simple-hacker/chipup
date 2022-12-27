<?php

namespace Database\Seeders;

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
                'small_blind' => 0.25,
                'big_blind' => 0.5,
            ],
            [
                'small_blind' => 0.5,
                'big_blind' => 1,
            ],
            [
                'small_blind' => 1,
                'big_blind' => 1,
            ],
            [
                'small_blind' => 1,
                'big_blind' => 2,
            ],
            [
                'small_blind' => 1,
                'big_blind' => 3,
            ],
            [
                'small_blind' => 2,
                'big_blind' => 2,
            ],
            [
                'small_blind' => 2,
                'big_blind' => 4,
            ],
            [
                'small_blind' => 4,
                'big_blind' => 8,
            ],
            [
                'small_blind' => 5,
                'big_blind' => 10,
            ],
            [
                'small_blind' => 10,
                'big_blind' => 20,
            ],
            [
                'small_blind' => 25,
                'big_blind' => 50,
            ],
            [
                'small_blind' => 50,
                'big_blind' => 100,
            ],
        ];

        Stake::insert($stakes);
    }
}
