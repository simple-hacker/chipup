<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\User;
use App\CashGame;
use App\Attributes\Limit;
use App\Attributes\Stake;
use App\Attributes\Variant;
use App\Attributes\TableSize;
use Faker\Generator as Faker;
use Illuminate\Support\Carbon;

$factory->define(CashGame::class, function (Faker $faker) {

    $start_time = Carbon::now()->subHours(rand(1, 43800));
    $end_time = $start_time->copy()->addMinutes(rand(10, 2880));

    return [
        'user_id' => User::all()->random()->id,
        'stake_id' => Stake::all()->random()->id,
        'limit_id' => Limit::all()->random()->id,
        'variant_id' => Variant::all()->random()->id,
        'table_size_id' => TableSize::all()->random()->id,
        'location' => $faker->randomElement(['CasinoMK', 'Las Vegas', 'Grosvenor Casino Luton', 'Grosvenor Casino Cardiff']),
        'comments' => $faker->paragraph,
        'start_time' => $start_time,
        'end_time' => $end_time,
    ];
});

$factory->afterCreating(CashGame::class, function ($cash_game, $faker) {

    $num_buy_ins = rand(1,3);
    for ($i = 1; $i <= $num_buy_ins; $i++) {
        $cash_game->addBuyIn($faker->numberBetween(20, 200));
    }
    $cash_game->cashOut($faker->numberBetween(5, 1000));
    $cash_game->addExpense($faker->numberBetween(1, 10), $faker->sentence(2, true));
});