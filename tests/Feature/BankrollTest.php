<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Transactions\Bankroll;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BankrollTest extends TestCase
{
    use RefreshDatabase;
    
    public function testAUserMustBeLoggedInToUpdateBankroll()
    {
        $user = factory('App\User')->create();

        $this->postJson(route('bankroll.create'), ['amount' => 30000])->assertUnauthorized();

        // Create a BankrollTransaction to update
        $bankrollTransaction = Bankroll::create([
            'user_id' => $user->id,
            'date' => '2020-01-01',
            'currency' => $user->currency,
            'amount' => 1000,
        ]);
        
        $this->patchJson(route('bankroll.update', ['bankrollTransaction', $bankrollTransaction]), ['amount' => 30000])->assertUnauthorized();

        $this->deleteJson(route('bankroll.delete', ['bankrollTransaction', $bankrollTransaction]))->assertUnauthorized();
    }
    
    public function testAUserCanAddToTheirBankroll()
    {
        $user = $this->signIn();

        $this->postJson(route('bankroll.create'), [
                'amount' => 30000
            ])
            ->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        $user->refresh();

        $this->assertEquals(30000, $user->bankroll);
        $this->assertCount(1, $user->bankrollTransactions);
    }

    public function testAUserCanWithdrawFromTheirBankroll()
    {
        $user = $this->signIn();

        // Supply negative number for withdrawals
        $this->postJson(route('bankroll.create'), [
                'amount' => -1000
            ])
            ->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        $user->refresh();

        $this->assertEquals(-1000, $user->bankroll);
        $this->assertCount(1, $user->bankrollTransactions);
    }

    public function testCurrencyDefaultsToUsersCurrencyIfNotSupplied()
    {
        $user = $this->signIn();

        $this->postJson(route('bankroll.create'), ['amount' => 1000]);

        $user->refresh();

        $this->assertEquals($user->currency, $user->bankrolLTransactions->first()->currency);
    }

    public function testBankrollTransactionCurrencyCanBeSupplied()
    {
        $user = factory('App\User')->create(['currency' => 'GBP']);
        $this->signIn($user);

        $this->postJson(route('bankroll.create'), ['amount' => 1000, 'currency' => 'PLN']);
        $this->assertEquals('PLN', $user->bankrollTransactions->first()->currency);
    }

    public function testBankrollTransactionCurrencyMustBeValid()
    {
        $user = factory('App\User')->create(['currency' => 'GBP']);
        $this->signIn($user);

        // Not a string
        $this->postJson(route('bankroll.create'), ['amount' => 1000, 'currency' => 999])->assertStatus(422);
        // Not a valid ISO 4217
        $this->postJson(route('bankroll.create'), ['amount' => 1000, 'currency' => 'AAA'])->assertStatus(422);
    }

    public function testAUserCanUpdateTheirBankrollTransaction()
    {
        $user = $this->signIn();

        $this->postJson(route('bankroll.create'), ['currency' => 'GBP', 'amount' => 30000]);

        $bankrollTransaction = $user->bankrollTransactions->first();
        $this->assertEquals('GBP', $bankrollTransaction->currency);
        $this->assertEquals(30000, $bankrollTransaction->amount);

        $this->patchJson(route('bankroll.update', ['bankrollTransaction' => $bankrollTransaction]), ['currency' => 'USD', 'amount' => 20000]);

        $bankrollTransaction->refresh();
        $this->assertEquals('USD', $bankrollTransaction->currency);
        $this->assertEquals(20000, $bankrollTransaction->amount);
    }

    public function testAUserCanDeleteTheirBankrollTransaction()
    {
        $user = $this->signIn();

        // Withdraw 10000
        $this->postJson(route('bankroll.create'), ['amount' => -10000]);

        $user->refresh();
        $this->assertEquals(-10000, $user->bankroll); // 50000 - 10000 = 40000
        $this->assertCount(1, $user->bankrollTransactions);
        
        // Get the user's Withdraw Transaction and delete it.
        $bankrollTransaction = $user->bankrollTransactions->first();
        $this->deleteJson(route('bankroll.delete', ['bankrollTransaction' => $bankrollTransaction]));

        // The user shouldn't have any Transactions and their bankroll should be the original 0
        $user->refresh();
        $this->assertCount(0, $user->bankrollTransactions);
        $this->assertEquals(0, $user->bankroll);
    }

    public function testOnlyTheOwnerCanManageTheirBankrollTransactions()
    {
        $user1 = $this->signIn();

        // Add 30000 to user1's bankroll.
        $this->postJson(route('bankroll.create'), [
            'amount' => 30000
        ]);

        // Get the bankrollTransaction for user1
        $bankrollTransaction = $user1->bankrollTransactions->first();

        // Now acting as user2, should be Forbidden to update user1's transaction.
        $user2 = $this->signIn();

        $this->patchJson(route('bankroll.update', ['bankrollTransaction' => $bankrollTransaction]), ['amount' => 20000])->assertForbidden();
        
        // Should be Forbidden to delete user1's transaction too.
        $this->deleteJson(route('bankroll.delete', ['bankrollTransaction' => $bankrollTransaction]))->assertForbidden();
    }

    public function testAmountMustBeValidForBankrollTransactions()
    {
        // All numbers are valid.

        $user = factory('App\User')->create();
        $user->completeSetup();
        $this->actingAs($user);

        // Check negative numbers for withdrawals - valid.
        $this->postJson(route('bankroll.create'), ['amount' => -1000])->assertOk();
        // Check float numbers - valid.
        $this->postJson(route('bankroll.create'), ['amount' => 50.82])->assertOk();

        $bankrollTransaction = $user->bankrollTransactions->first();

        // Check negative numbers for updates, will result in a 200
        $this->patchJson(route('bankroll.update', ['bankrollTransaction' => $bankrollTransaction]), ['amount' => -1000])->assertOk();
        $this->assertEquals(-1000, $bankrollTransaction->fresh()->amount);
        
        // Check float numbers for updates
        $this->patchJson(route('bankroll.update', ['bankrollTransaction' => $bankrollTransaction]), ['amount' => 50.82])->assertOk();
        $this->assertEquals(50.82, $bankrollTransaction->fresh()->amount);
    }

    public function testAmountCannotBeInvalidForBankrollTransactions()
    {
        $user = factory('App\User')->create();
        $user->completeSetup();
        $this->actingAs($user);

        $this->postJson(route('bankroll.create'), ['amount' => 'Not a number'])->assertStatus(422);
    }

    public function testDateIsSetToNowWhenCreatingBankrollTransaction()
    {
        // We don't provide a date when creating a bankroll transaction, so it defaults to now()
        $user = $this->signIn();
        $this->postJson(route('bankroll.create'), ['amount' => 30000]);

        $bankrollTransaction = $user->bankrollTransactions->first();

        $this->assertEquals($bankrollTransaction->date, Carbon::today());
    }

    public function testBankrollTransactionDateCanBeUpdated()
    {
        $user = $this->signIn();
        $this->postJson(route('bankroll.create'), ['amount' => 30000]);

        $bankrollTransaction = $user->bankrollTransactions->first();
 
        // Amount must be supplied when updating a bankroll transaction
        // because the whole purpose of a transaction is the amount, without it we might as well delete the transaction
        $this->patchJson(route('bankroll.update', ['bankrollTransaction' => $bankrollTransaction]), [
            'date' => '2019-12-25',
            'amount' => 999,
        ]);

        $this->assertEquals(Carbon::create(2019, 12, 25, 0, 0, 0), $bankrollTransaction->fresh()->date);
    }

    public function testUpdatedDateCannotBeInTheFuture()
    {
        $user = $this->signIn();
        $this->postJson(route('bankroll.create'), ['amount' => 30000]);

        $bankrollTransaction = $user->bankrollTransactions->first();

        // Assert okay if changing date to today.
        $this->patchJson(route('bankroll.update', ['bankrollTransaction' => $bankrollTransaction]), [
            'date' => Carbon::create('today')->toDateString(),
            'amount' => 30000,
        ])
        ->assertOk();

        // Assert status 422 and date should fail validation for future date (tomorrow)
        $this->patchJson(route('bankroll.update', ['bankrollTransaction' => $bankrollTransaction]), [
            'date' => Carbon::create('tomorrow')->toDateString(),
            'amount' => 30000,
        ])
        ->assertStatus(422);
    }

    public function testBankrollTransactionCommentsCanBeAddedAndUpdated()
    {
        $user = $this->signIn();
        $this->postJson(route('bankroll.create'), [
            'amount' => 30000,
            'comments' => 'This is a comment'
        ]);

        $bankrollTransaction = $user->bankrollTransactions->first();
        // Assert comment was adding when creating a bankroll transaction.
        $this->assertEquals($bankrollTransaction->comments, 'This is a comment');

        // Update the transaction's comments.
        $this->patchJson(route('bankroll.update', ['bankrollTransaction' => $bankrollTransaction]), [
            'amount' => 30000,
            'comments' => 'Updated comments'
        ]);

        // Assert fresh copy of transaction has updated comments.
        $this->assertEquals($bankrollTransaction->fresh()->comments, 'Updated comments');

    }
}
