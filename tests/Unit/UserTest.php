<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;
    
    public function testUserHasABankroll()
    {
        $user = factory('App\User')->create([
            'bankroll' => 10000
        ]);

        $this->assertEquals($user->bankroll, 10000);
    }

    public function testBankrollCanBeUpdated()
    {
        // Create user with default 10000 bankroll.
        $user = factory('App\User')->create();

        $user->updateBankroll(50);

        $this->assertEquals($user->bankroll, 10050);

        $user->updateBankroll(-50);

        $this->assertEquals($user->fresh()->bankroll, 10000);
    }

    public function testBankrollCanBeIncremented()
    {
        // Create user with default 10000 bankroll.
        $user = factory('App\User')->create();

        $user->incrementBankroll(50);

        $this->assertEquals($user->bankroll, 10050);
    }

    public function testBankrollCanBeDecremented()
    {
        // Create user with default 10000 bankroll.
        $user = factory('App\User')->create();

        $user->decrementBankroll(50);

        $this->assertEquals($user->bankroll, 9950);
    }

    public function testBankrollUpdateMustBeInteger()
    {
        $this->expectException(\App\Exceptions\NonIntegerAmount::class);
      
        $user = factory('App\User')->create();

        $user->updateBankroll(50.99);

    }
}
