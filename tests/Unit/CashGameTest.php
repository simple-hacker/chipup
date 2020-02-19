<?php

namespace Tests\Unit;

use App\User;
use App\CashGame;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CashGameTest extends TestCase
{
    use RefreshDatabase;

    public function testASessionBelongsToAUser()
    {
        $session = factory('App\CashGame')->create();

        $this->assertInstanceOf(User::class, $session->user);
    }
}
