<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BankrollTest extends TestCase
{
    use RefreshDatabase;

    public function testAUserCanAddToTheirBankroll()
    {
        $user = factory('App\User')->create([
            'bankroll' => 10000,
        ]);

        // Add 500 (£5) to user's bankroll.
        $user->createBankrollTransaction(500);

        // A user should have one Bankroll
        $this->assertCount(1, $user->bankrollTransactions);

        // User's bankroll is updated through Bankroll model observer
        $this->assertEquals($user->fresh()->bankroll, 10500);
    }

    public function testAUserCanWithdrawFromTheirBankroll()
    {
        $user = factory('App\User')->create([
            'bankroll' => 10000,
        ]);

        // Withdraw 500 (£5) to user's bankroll.
        $user->createBankrollTransaction(-500);
        
        // A user should have one Bankroll
        $this->assertCount(1, $user->bankrollTransactions);

        // User's bankroll is updated through Bankroll model observer
        $this->assertEquals($user->fresh()->bankroll, 9500);
    }

    public function testAUsersBankrollIsCorrectedWhenUpdatingABankroll()
    {
        // Default to 10000 bankroll.
        $user = factory('App\User')->create();

        // Add 500 (£5) to user's bankroll.
        $user->createBankrollTransaction(500);
        // Assert bankroll is incremented with this addition.
        $this->assertEquals($user->fresh()->bankroll, 10500);

        // Get the user's Bankroll
        $bankroll = $user->bankrollTransactions()->first();

        // Update the createBankrollTransaction amount to 1000 instead of 500. i.e. greater than original
        $bankroll->update([
            'amount' => 1000
        ]);

        // Assert bankroll is updated to reflect this change to Bankroll.
        // New amount should equal 11000 instead, an extra 500 (New Amount - Original Amount = 1000 - 500 = 500, so 10500 + 500 = 11000)
        $this->assertEquals($user->fresh()->bankroll, 11000);


        // Now check when new amount is less than original (which is now 1000 as above). -1500 < 1000.
        $bankroll->fresh()->update([
            'amount' => -1500
        ]);

        // New bankroll should equal 8500.  (10000 + 500 + (1000 - 500) + (-1500 - 1000) = 8500)
        $this->assertEquals($user->fresh()->bankroll, 8500);
    }
}
