<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BankrollTest extends TestCase
{
    use RefreshDatabase;

    public function testAUserCanAddToTheirBankroll()
    {
        $user = factory('App\User')->create([
            'bankroll' => 100,
        ]);

        // Add £5 to user's bankroll.
        $t = $user->createBankrollTransaction(['amount' => 5]);

        // A user should have one Bankroll
        $this->assertCount(1, $user->bankrollTransactions);

        // User's bankroll is updated through Bankroll model observer
        $this->assertEquals($user->fresh()->bankroll, 105);
    }

    public function testAUserCanWithdrawFromTheirBankroll()
    {
        $user = factory('App\User')->create([
            'bankroll' => 100,
        ]);

        // Withdraw £5 to user's bankroll.
        $user->createBankrollTransaction(['amount' => -5]);
        
        // A user should have one Bankroll
        $this->assertCount(1, $user->bankrollTransactions);

        // User's bankroll is updated through Bankroll model observer
        $this->assertEquals($user->fresh()->bankroll, 95);
    }

    public function testAUsersBankrollIsCorrectedWhenUpdatingABankroll()
    {
        // Default to 10000 bankroll.
        $user = factory('App\User')->create([
            'bankroll' => 100,
        ]);

        // Add 500 (£5) to user's bankroll.
        $user->createBankrollTransaction(['amount' => 5]);
        // Assert bankroll is incremented with this addition.
        $this->assertEquals($user->fresh()->bankroll, 105);

        // Get the user's Bankroll
        $bankroll = $user->bankrollTransactions()->first();

        // Update the createBankrollTransaction amount to 10 instead of 5. i.e. greater than original
        $bankroll->update([
            'amount' => 10
        ]);

        // Assert bankroll is updated to reflect this change to Bankroll.
        // New amount should equal 110 instead, an extra 5 (New Amount - Original Amount = 1000 - 5 = 5, so 105 + 5 = 110)
        $this->assertEquals($user->fresh()->bankroll, 110);


        // Now check when new amount is less than original (which is now 1000 as above). -1500 < 1000.
        $bankroll->fresh()->update([
            'amount' => -15
        ]);

        // New bankroll should equal 8500.  (100 + 5 + (10 - 5) + (-15 - 10) = 85)
        $this->assertEquals($user->fresh()->bankroll, 85);
    }

    public function testBankrollTransactionDefaultDateToNow()
    {       
        $user = factory('App\User')->create();
        // Create a bankroll transaction without a date.
        $bankrollTransaction = $user->createBankrollTransaction(['amount' => 500]);

        // Assert you can retrieve transaction's date and it's set to now.
        $this->assertEquals($bankrollTransaction->fresh()->date, Carbon::today());
    }
}
