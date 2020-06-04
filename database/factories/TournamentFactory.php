<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\User;
use App\Tournament;
use App\Attributes\Limit;
use App\Attributes\Variant;
use Faker\Generator as Faker;
use Illuminate\Support\Carbon;

$factory->define(Tournament::class, function (Faker $faker) {
    $start_time = Carbon::now()->subHours(rand(1, 43800));
    $end_time = $start_time->copy()->addMinutes(rand(10, 2880));

    $entries = $faker->numberBetween(50, 500);

    return [
        'user_id' => factory('App\User')->create(),
        'limit_id' => Limit::all()->random()->id,
        'variant_id' => Variant::all()->random()->id,
        'name' => $faker->sentence(3, true),
        'prize_pool' => ($faker->numberBetween(10, 500)) * 100,
        'entries' => $entries,
        'position' => $faker->numberBetween(1, $entries),
        'location' => $faker->randomElement(['CasinoMK', 'Las Vegas', 'Grosvenor Casino Luton', 'Grosvenor Casino Cardiff']),
        'comments' => $faker->paragraph,
        'start_time' => $start_time->toDateTimeString(),
        'end_time' => $end_time->toDateTimeString(),
    ];
});

$factory->afterCreating(Tournament::class, function ($tournament, $faker) {

    // Add BuyIn amount between £5 and £500
    $buy_in = $faker->numberBetween(5, 500);
    $tournament->addBuyIn($buy_in);

    // Add 0, 1 or 2 Rebuys
    $num_rebuys = rand(0,2);
    for ($i = 1; $i <= $num_rebuys; $i++) {
        $tournament->addRebuy($buy_in);
    }

    // Add 0 or 1 Add Ons
    $num_add_ons = rand(0,1);
    for ($i = 1; $i <= $num_add_ons; $i++) {
        $tournament->addAddOn(($buy_in / 2));
    }

    // Add 0, 1 or 2 Expenses
    $num_expenses = rand(0,2);
    for ($i = 1; $i <= $num_expenses; $i++) {
        $tournament->addExpense($faker->numberBetween(1, 10), $faker->sentence(2, true));
    }

    // Cash Out depending on position and entries.
    $finish_percentile = $tournament->position / $tournament->entries;

    if ($finish_percentile < 0.01) {
        $cash_out_amount = $buy_in * 30;
    }
    elseif ($finish_percentile < 0.02) {
        $cash_out_amount = $buy_in * 10;
    }
    elseif ($finish_percentile < 0.05) {
        $cash_out_amount = $buy_in * 5;
    }
    elseif ($finish_percentile < 0.10) {
        $cash_out_amount = $buy_in * 2;
    }
    elseif ($finish_percentile < 0.15) {
        $cash_out_amount = $buy_in * 1.2;
    }
    else {
        $cash_out_amount = 0;
    }

    $tournament->cashOut($cash_out_amount);
});
