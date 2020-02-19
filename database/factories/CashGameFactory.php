<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\CashGame;
use Faker\Generator as Faker;

$factory->define(CashGame::class, function (Faker $faker) {
    return [
        'user_id' => factory('App\User')->create(),
    ];
});
