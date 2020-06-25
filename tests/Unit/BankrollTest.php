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
        $user = factory('App\User')->create(['currency' => 'GBP']);

        // Add £5 to user's bankroll.
        $user->createBankrollTransaction(['currency' => 'GBP', 'amount' => 5]);

        // A user should have one Bankroll
        $this->assertCount(1, $user->bankrollTransactions);

        // User's bankroll is updated through Bankroll model observer
        $this->assertEquals($user->fresh()->bankroll, 5);
    }

    public function testAUserCanWithdrawFromTheirBankroll()
    {
        $user = factory('App\User')->create(['currency' => 'GBP']);

        // Withdraw £5 from user's bankroll.
        $user->createBankrollTransaction(['currency' => 'GBP', 'amount' => -5]);
        
        // A user should have one Bankroll Transaction
        $this->assertCount(1, $user->bankrollTransactions);

        // User's bankroll is updated through Bankroll model observer
        $this->assertEquals($user->fresh()->bankroll, -5);
    }

    public function testAUsersBankrollIsCorrectedWhenUpdatingABankroll()
    {
        $user = factory('App\User')->create(['currency' => 'GBP']);

        // Add £5 to user's bankroll.
        $user->createBankrollTransaction(['currency' => 'GBP', 'amount' => 5]);
        // Assert bankroll is incremented with this addition.
        $this->assertEquals($user->fresh()->bankroll, 5);

        // Get the user's Bankroll
        $bankroll = $user->bankrollTransactions()->first();

        // Update the createBankrollTransaction amount to 10 instead of 5. i.e. greater than original
        $bankroll->update([
            'amount' => 10
        ]);

        // Assert bankroll is updated to reflect this change to Bankroll.
        // New amount should equal 110 instead, an extra 5 (New Amount - Original Amount = 1000 - 5 = 5, so 105 + 5 = 110)
        $this->assertEquals($user->fresh()->bankroll, 10);


        $bankroll->fresh()->update([
            'amount' => -15
        ]);

        $this->assertEquals($user->fresh()->bankroll, -15);
    }

    public function testBankrollTransactionDefaultDateToNow()
    {       
        $user = factory('App\User')->create();
        // Create a bankroll transaction without a date.
        $bankrollTransaction = $user->createBankrollTransaction(['currency' => 'GBP', 'amount' => 500]);
        $this->assertEquals($bankrollTransaction->date, Carbon::today());
    }

    public function testBankrollTransactionDefaultsToUsersCurrencyIfNoCurrencyProvided()
    {
        $user = factory('App\User')->create(['currency' => 'USD']);
        $bankrollTransaction = $user->createBankrollTransaction(['amount' => 500]);
        $this->assertEquals($bankrollTransaction->currency, $user->currency);
    }

    public function testLocaleAmountIsSameAsAmountIfSameCurrencyAsUserDefault()
    {
        $user = factory('App\User')->create(['currency' => 'GBP']);
        $bankrollTransaction = $user->createBankrollTransaction(['amount' => 500]);
        $this->assertEquals($bankrollTransaction->locale_amount, $bankrollTransaction->amount);
    }

    public function testDifferentCurrencyIsConvertedToUserCurrencyForLocaleAmount()
    {
        // Set User Default Currency to GBP and Start with a Bankroll of 1000
        $user = factory('App\User')->create(['currency' => 'GBP']);

        $bankrollTransaction = $user->createBankrollTransaction(['currency' => 'USD', 'amount' => 5]);

        // $1 GBP = $1.25 USD in TestExchangeRates
        // $5 USD is £4 GBP.
        // Assert BankrollTransaction has currency USD and amount 5, but locale_amount is 4.
        $this->assertEquals('USD', $bankrollTransaction->currency);
        $this->assertEquals(5, $bankrollTransaction->amount);
        $this->assertEquals(4, $bankrollTransaction->locale_amount);
    }

    public function testDifferentCurrencyIsConvertedToUserCurrencyForBankrollDeposits()
    {
        // Set User Default Currency to GBP
        $user = factory('App\User')->create(['currency' => 'GBP']);

        // 1 GBP / 4.9 PLN
        // Deposit a bankroll transaction of 50 PLN = £10.2040
        $user->createBankrollTransaction(['currency' => 'PLN', 'amount' => 50]);

        $this->assertEquals(10.20, $user->fresh()->bankroll);
    }

    public function testDifferentCurrencyIsConvertedToUserCurrencyForBankrollWithdrawals()
    {
        // Set User Default Currency to GBP
        $user = factory('App\User')->create(['currency' => 'GBP']);

        // 1 GBP / 1.69 CAD
        // Withdraw a bankroll transaction of $100 CAD = £59.5238 GBP
        $user->createBankrollTransaction(['currency' => 'CAD', 'amount' => -100]);

        $this->assertEquals(-59.52, $user->fresh()->bankroll);
    }

    public function testConvertedAmountIsUsedWhenDeletingABankrollTransactionInADifferentCurrency()
    {
        // Set User Default Currency to GBP
        $user = factory('App\User')->create(['currency' => 'GBP']);

        // 1 GBP / 1.25 USD
        // Withdraw a bankroll transaction of $100 USD = £80 GBP
        $bankrollTransaction = $user->createBankrollTransaction(['currency' => 'USD', 'amount' => -100]);
        $this->assertEquals(-80, $user->fresh()->bankroll);

        // Delete the USD Transaction, make sure £80 is added back on the bankroll and not £100
        $bankrollTransaction->delete();
        $this->assertEquals(0, $user->fresh()->bankroll);
    }

    public function testCorrectConvertedAmountIsUsedWhenUpdatingABankrollTransactionToTheSameCurrency()
    {
        // Set User Default Currency to GBP
        $user = factory('App\User')->create(['currency' => 'GBP']);

        // 1 GBP / 1.25 USD
        // Deposit a bankroll transaction of $100 USD = £80 GBP
        $bankrollTransaction = $user->createBankrollTransaction(['currency' => 'USD', 'amount' => 100]);

        //Bankroll is now £1,080
        $this->assertEquals(80, $user->fresh()->bankroll);

        // Change bankroll transaction from $100 USD to $500 (Same currency)
        $bankrollTransaction->update(['amount' => 500]);
        
        // $500 USD = £400 GBP.  Assert user's bankroll is now £400 and not 500
        $this->assertEquals(400, $user->fresh()->bankroll);
    }

    public function testUpdatingTheCurrencyOfTheBankrollTransactionUpdatesUsersBankrollWithTheCorrectAmount()
    {
        // Set User Default Currency to GBP
        $user = factory('App\User')->create(['currency' => 'GBP']);

        // 1 GBP / 1.25 USD
        // Deposit a bankroll transaction of $500 USD = £400 GBP
        $bankrollTransaction = $user->createBankrollTransaction(['currency' => 'USD', 'amount' => 500]);

        // Bankroll is now £400 ($500)

        // Change from USD to PLN
        // $500 USD = £400 GBP
        // 500 PLN = £102.04 GBP
        // Therefore bankroll should now be £102.04 instead of £400

        $bankrollTransaction->update(['currency' => 'PLN']);
        $this->assertEquals(102.04, $user->fresh()->bankroll);
    }

    public function testUpdatingTheCurrencyOfTheBankrollTransactionUpdatesUsersBankrollWithTheCorrectAmountForWithdrawalsToo()
    {
        // Set User Default Currency to GBP
        $user = factory('App\User')->create(['currency' => 'GBP']);

        // 1 GBP / 1.25 USD
        // Withdraw a bankroll transaction of $500 USD = £400 GBP
        $bankrollTransaction = $user->createBankrollTransaction(['currency' => 'USD', 'amount' => -500]);

        // Bankroll is now £-400
        $this->assertEquals(-400, $user->fresh()->bankroll);

        // Change from USD to PLN
        // $500 USD = £400 GBP
        // 500 PLN = £102.04 GBP
        $bankrollTransaction->update(['currency' => 'PLN']);

        // Therefore bankroll should now be -£102.04
        $this->assertEquals(-102.04, $user->fresh()->bankroll);
    }

    public function testUpdatingBothCurrencyAndAmountOfbankrollTransactionUpdatedUsersBankrollWithCorrectConvertedAmount()
    {
        // Set User Default Currency to GBP
        $user = factory('App\User')->create(['currency' => 'GBP']);

        // 1 GBP / 1.25 USD
        // Deposit a bankroll transaction of $500 USD = £400 GBP
        $bankrollTransaction = $user->createBankrollTransaction(['currency' => 'USD', 'amount' => 500]);

        // Bankroll is now £400

        // Change from $500 USD to 350 PLN
        // $500 USD = £400 GBP
        // 350 PLN = £71.4285 GBP
        $bankrollTransaction->update(['currency' => 'PLN', 'amount' => 350]);

        // Therefore bankroll should now be £71.4285 = £71.43
        $this->assertEquals(71.43, $user->fresh()->bankroll);
    }
}
