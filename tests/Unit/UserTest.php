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

    public function testBankrollUpdateMustBeInteger()
    {
        $this->expectException(\App\Exceptions\NonIntegerAmount::class);
      
        $user = factory('App\User')->create();

        $user->updateBankroll(50.99);
    }

    public function testGetTheCurrentLiveCashGameForAUser()
    {
        $user = factory('App\User')->create();

        // Start a Cash Game session.
        $cash_game = $user->startCashGame();

        $this->assertEquals($user->currentLiveCashGame()->id, $cash_game->id);
        
        // End the Cash Game session.
        $cash_game->end();
        
        // currentLiveCashGame should now be an empty Collection.
        $this->assertEmpty($user->currentLiveCashGame());
    }
}
