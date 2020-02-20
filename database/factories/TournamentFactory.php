<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Tournament;
use Faker\Generator as Faker;

$factory->define(Tournament::class, function (Faker $faker) {
    return [
        'user_id' => factory('App\User')->create(),
    ];
});
