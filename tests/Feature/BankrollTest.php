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

        $this->postJson(route('bankroll.create'), [
                'amount' => 30000
            ])
            ->assertUnauthorized();

        // Create a BankrollTransaction to update
        $bankrollTransaction = Bankroll::create([
            'user_id' => $user->id,
            'date' => '2020-01-01',
            'amount' => 1000,
        ]);
        
        $this->patchJson(route('bankroll.update', ['bankrollTransaction', $bankrollTransaction]), [
                'amount' => 30000
            ])
            ->assertUnauthorized();

        $this->deleteJson(route('bankroll.delete', ['bankrollTransaction', $bankrollTransaction]))
            ->assertUnauthorized();
    }
    
    public function testAUserCanAddToTheirBankroll()
    {
        $user = factory('App\User')->create([
            'bankroll' => 0,
        ]);
        $user->completeSetup();

        $this->actingAs($user);

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
        $user = factory('App\User')->create([
            'bankroll' => 50000,
        ]);
        $user->completeSetup();

        $this->actingAs($user);

        // Supply negative number for withdrawals
        $this->postJson(route('bankroll.create'), [
                'amount' => -10000
            ])
            ->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        $user->refresh();

        $this->assertEquals(40000, $user->bankroll);
        $this->assertCount(1, $user->bankrollTransactions);
    }

    public function testAUserCanUpdateTheirBankrollTransaction()
    {
        $user = factory('App\User')->create([
            'bankroll' => 0,
        ]);
        $user->completeSetup();

        $this->actingAs($user);

        $this->postJson(route('bankroll.create'), [
            'amount' => 30000
        ]);

        $bankrollTransaction = $user->bankrollTransactions->first();

        $this->patchJson(route('bankroll.update', ['bankrollTransaction' => $bankrollTransaction]), [
            'amount' => 20000
        ]);

        $this->assertEquals(20000, $user->fresh()->bankroll);
    }

    public function testAUserCanDeleteTheirBankrollTransaction()
    {
        $user = factory('App\User')->create([
            'bankroll' => 50000,
        ]);
        $user->completeSetup();

        $this->actingAs($user);

        // Withdraw 10000
        $this->postJson(route('bankroll.create'), [
            'amount' => -10000
        ]);

        $user->refresh();
        $this->assertEquals(40000, $user->bankroll); // 50000 - 10000 = 40000
        $this->assertCount(1, $user->bankrollTransactions);
        
        // Get the user's Withdraw Transaction and delete it.
        $bankrollTransaction = $user->bankrollTransactions->first();
        $this->deleteJson(route('bankroll.delete', ['bankrollTransaction' => $bankrollTransaction]));

        // The user shouldn't have any Transactions and their bankroll should be the original 50000
        $user->refresh();
        $this->assertCount(0, $user->bankrollTransactions);
        $this->assertEquals(50000, $user->bankroll);
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

        $this->patchJson(route('bankroll.update', ['bankrollTransaction' => $bankrollTransaction]), [
                    'amount' => 20000
                ])
                ->assertForbidden();
        
        // Should be Forbidden to delete user1's transaction too.
        $this->deleteJson(route('bankroll.delete', ['bankrollTransaction' => $bankrollTransaction]))
                ->assertForbidden();
    }

    public function testAUserCannotGiveAnInvalidAdditionOrWithdrawalAmount()
    {
        // When creating a Bankroll Transaction for addition, withdrawal, or update
        // the amount given must be a positive integer.
        // NOTE: Update 17/04/20  You can now supply a negative integer for withdrawing
        // Instead of having separate additional, withdrawing functions, refactor to a single add transaction function that accepts postive amounts for adding, and negative amounts for withdrawals.

        $user = factory('App\User')->create([
            'bankroll' => 10000  //This doesn't create a BankrollTransaction
        ]);
        $user->completeSetup();
        $this->actingAs($user);

        // Check negative numbers for withdrawals - will result in status 200.
        $this->postJson(route('bankroll.create'), [
                'amount' => -1000
            ])
            ->assertStatus(200);

        // Check float numbers
        $this->postJson(route('bankroll.create'), [
                'amount' => 50.82
            ])
            ->assertStatus(422);


        // Create a BankrollTransaction and get it from user.
        $this->postJson(route('bankroll.create'), [
                'amount' => 500
            ]);
        $bankrollTransaction = $user->bankrollTransactions->first();

        // Check negative numbers for updates, will result in a 200
        $this->patchJson(route('bankroll.update', ['bankrollTransaction' => $bankrollTransaction]), [
                'amount' => -1000
            ])
            ->assertStatus(200);

        // Check float numbers for updates
        $this->patchJson(route('bankroll.update', ['bankrollTransaction' => $bankrollTransaction]), [
                'amount' => 50.82
            ])
            ->assertStatus(422);
    }

    public function testDateIsSetToNowWhenCreatingBankrollTransaction()
    {
        $this->withoutExceptionHandling();

        // We don't provide a date when creating a bankroll transaction, so it defaults to now()
        $user = $this->signIn();
        $this->postJson(route('bankroll.create'), [
            'amount' => 30000
        ]);

        $bankrollTransaction = $user->bankrollTransactions->first();

        $this->assertEquals($bankrollTransaction->date, Carbon::today());
    }

    public function testBankrollTransactionDateCanBeUpdated()
    {
        $user = $this->signIn();
        $this->postJson(route('bankroll.create'), [
            'amount' => 30000
        ]);

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
        $this->postJson(route('bankroll.create'), [
            'amount' => 30000
        ]);

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
