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
        'user_id' => factory('App\User')->create(),
        'stake_id' => Stake::all()->random()->id,
        'limit_id' => Limit::all()->random()->id,
        'variant_id' => Variant::all()->random()->id,
        'table_size_id' => TableSize::all()->random()->id,
        'location' => $faker->randomElement(['CasinoMK', 'Las Vegas', 'Grosvenor Casino Luton', 'Grosvenor Casino Cardiff']),
        'comments' => $faker->paragraph,
        'start_time' => $start_time->toDateTimeString(),
        'end_time' => $end_time->toDateTimeString(),
    ];
});

$factory->afterCreating(CashGame::class, function ($cash_game, $faker) {

    // Add at 1, 2 or 3 Buy Ins
    $num_buy_ins = rand(1,3);
    for ($i = 1; $i <= $num_buy_ins; $i++) {
        $cash_game->addBuyIn($faker->numberBetween(20, 200));
    }

    // Add 0, 1 or 2 Expenses
    $num_expenses = rand(0,2);
    for ($i = 1; $i <= $num_expenses; $i++) {
        $cash_game->addExpense($faker->numberBetween(1, 10), $faker->sentence(2, true));
    }

    // Cash Out Between £0 and £1000
    $cash_game->addCashOut($faker->numberBetween(0, 1000));
});