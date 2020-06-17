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

    public function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    protected function signIn($user = null)
    {
        if (! $user) {
            $user = factory('App\User')->create();
        }

        $user->completeSetup();

        $this->actingAs($user);

        return $user;
    }

    protected function startLiveCashGame($user = null)
    {
        // In User.php startCashGame has default values
        return $this->signIn($user)->startCashGame([
            'start_time' => date('Y-m-d H:i:s'),
        ]);
    }

    protected function getLiveCashGameAttributes($amount = 1000, $start_time = null) {

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

    protected function getCashGameAttributes($amount = 1000, $time = null)
    {
        $start_time = $time ? Carbon::create($time) : Carbon::create('-4 hours');

        $attributes = [
            'start_time' => $start_time->toDateTimeString(),
            'stake_id' => Stake::inRandomOrder()->first()->id,
            'limit_id' => Limit::inRandomOrder()->first()->id,
            'variant_id' => Variant::inRandomOrder()->first()->id,
            'table_size_id' => TableSize::inRandomOrder()->first()->id,
            'location' => 'CasinoMK',
            'end_time' => $start_time->copy()->addMinutes(30)->toDateTimeString(),
            'buy_ins' => [
                ['amount' => $amount],
            ],
            'expenses' => [
                ['amount' => 400],
                ['amount' => 750, 'comments' => 'Tips'],
            ],
            'cash_out' => [
                'amount' => 1000,
            ]
        ];
        
        return $attributes;
    }

    protected function startLiveTournament($user = null)
    {
        // In User.php startTournament does not have default values
        // So need to add them here.
        return $this->signIn($user)->startTournament($this->getLiveTournamentAttributes());
    }

    protected function getLiveTournamentAttributes($amount = 1000, $start_time = null)
    {
        $attributes = [
            'amount' => $amount,
            'name' => 'FU Flip',
            'variant_id' => Variant::inRandomOrder()->first()->id,
            'limit_id' => Limit::inRandomOrder()->first()->id,
            'location' => 'Casino MK',
        ];

        // Only add start time to the request if needed.
        if ($start_time) {
            $attributes['start_time'] = $start_time;
        }
        
        return $attributes;
    }

    protected function getTournamentAttributes($amount = 1000, $time = null)
    {
        $start_time = $time ? Carbon::create($time) : Carbon::create('-4 hours');

        $attributes = [
            'start_time' => $start_time->toDateTimeString(),
            'name' => 'FU Flip',
            'limit_id' => 1,
            'variant_id' => 1,
            'prize_pool' => 1000,
            'position' => 5,
            'entries' => 110,
            'location' => 'CasinoMK',
            'end_time' => $start_time->copy()->addMinutes(30)->toDateTimeString(),
            'buy_in' => ['amount' => $amount],
            'rebuys' => [
                ['amount' => ($amount/2)]
            ],
            'add_ons' => [
                ['amount' => ($amount/3)]
            ],
            'expenses' => [
                ['amount' => 400],
                ['amount' => 750, 'comments' => 'Tips'],
            ],
            'cash_out' => [
                'amount' => 1000,
            ]
        ];
        
        return $attributes;
    }
}
