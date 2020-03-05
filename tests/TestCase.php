<?php

namespace Tests;

use App\Attributes\Limit;
use App\Attributes\Stake;
use App\Attributes\Variant;
use App\Attributes\TableSize;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function signIn($user = null)
    {
        if (! $user) {
            $user = factory('App\User')->create();
        }

        $this->actingAs($user);

        return $user;
    }

    protected function createCashGame($user = null)
    {
        return $this->signIn($user)->startCashGame([
            'start_time' => date('Y-m-d H:i:s'),
        ]);
    }

    protected function createTournament($user = null)
    {
        return $this->signIn($user)->startTournament();
    }

    protected function getCashGameAttributes($amount = 1000, $start_time = null) {

        $attributes = [
            'amount' => $amount,
            'stake_id' => Stake::inRandomOrder()->first()->id,
            'variant_id' => Variant::inRandomOrder()->first()->id,
            'limit_id' => Limit::inRandomOrder()->first()->id,
            'table_size_id' => TableSize::inRandomOrder()->first()->id,
            'location' => 'Casino MK',
        ];

        // Only add start time to the request if needed.
        if ($start_time) {
            $attributes['start_time'] = $start_time;
        }
        
        return $attributes;
    }
}
