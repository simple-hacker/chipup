<?php

namespace Tests;

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
        return $this->signIn($user)->startCashGame();
    }

    protected function createTournament($user = null)
    {
        return $this->signIn($user)->startTournament();
    }
}
