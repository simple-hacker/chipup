<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BankrollTest extends TestCase
{
    use RefreshDatabase;
    
    public function testAUserMustBeLoggedInToUpdateBankroll()
    {
        $user = factory('App\User')->create();

        $this->post(route('bankroll.add'), [
            'amount' => 30000
        ])
        ->assertRedirect('login');
    }
    
    public function testAUserCanAddToTheirBankroll()
    {
        $user = factory('App\User')->create([
            'bankroll' => 0,
        ]);

        $this->actingAs($user);

        $response = $this->post(route('bankroll.add'), [
            'amount' => 30000
        ]);

        $response->assertStatus(200)
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

        $this->actingAs($user);

        $response = $this->post(route('bankroll.withdraw'), [
            'amount' => 10000
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                    'success' => true
                ]);

        $user->refresh();

        $this->assertEquals(40000, $user->bankroll);
        $this->assertCount(1, $user->bankrollTransactions);
    }

    public function testAUserCanUpdateTheirBankrollTransaction()
    {
        $this->withoutExceptionHandling();

        $user = factory('App\User')->create([
            'bankroll' => 0,
        ]);

        $this->actingAs($user);

        $this->post(route('bankroll.add'), [
            'amount' => 30000
        ]);

        $bankrollTransaction = $user->bankrollTransactions->first();

        $this->patch(route('bankroll.update', ['bankrollTransaction' => $bankrollTransaction]), [
            'amount' => 20000
        ]);

        $this->assertEquals(20000, $user->fresh()->bankroll);
    }

    public function testAUserCanDeleteTheirBankrollTransaction()
    {
        $this->withoutExceptionHandling();

        $user = factory('App\User')->create([
            'bankroll' => 50000,
        ]);

        $this->actingAs($user);

        // Withdraw 10000
        $this->post(route('bankroll.withdraw'), [
            'amount' => 10000
        ]);

        $user->refresh();
        $this->assertEquals(40000, $user->bankroll); // 50000 - 10000 = 40000
        $this->assertCount(1, $user->bankrollTransactions);
        
        // Get the user's Withdraw Transaction and delete it.
        $bankrollTransaction = $user->bankrollTransactions->first();
        $this->delete(route('bankroll.delete', ['bankrollTransaction' => $bankrollTransaction]));

        // The user shouldn't have any Transactions and their bankroll should be the original 50000
        $user->refresh();
        $this->assertCount(0, $user->bankrollTransactions);
        $this->assertEquals(50000, $user->bankroll);
    }

    public function testOnlyTheOwnerCanManageTheirBankrollTransactions()
    {
        $user1 = factory('App\User')->create();
        $user2 = factory('App\User')->create();

        $this->actingAs($user1);
        // Add 30000 to user1's bankroll.
        $this->post(route('bankroll.add'), [
            'amount' => 30000
        ]);

        // Get the bankrollTransaction for user1
        $bankrollTransaction = $user1->bankrollTransactions->first();

        // Now acting as user2, should be Unauthorized to update user1's transaction.
        $this->actingAs($user2);
        $this->patch(route('bankroll.update', ['bankrollTransaction' => $bankrollTransaction]), [
                    'amount' => 20000
                ])
                ->assertForbidden();
        
        // Should be Unauthorized to delete user1's transaction too.
        $this->delete(route('bankroll.delete', ['bankrollTransaction' => $bankrollTransaction]))
                ->assertForbidden();
    }

    public function testAUserCannotGiveAnInvalidAdditionOrWithdrawalAmount()
    {
        // When creating a Bankroll Transaction for addition, withdrawal, or update
        // the amount given must be a positive integer.

        $user = factory('App\User')->create([
            'bankroll' => 10000  //This doesn't create a BankrollTransaction
        ]);
        $this->actingAs($user);

        // Check negative numbers for addition
        $this->post(route('bankroll.add'), [
                'amount' => -1000
            ])
            ->assertSessionHasErrors(['amount']);

        // Check float numbers for addition
        $this->post(route('bankroll.add'), [
                'amount' => 50.82
            ])
            ->assertSessionHasErrors(['amount']);

        // Check negative numbers for withdrawals
        $this->post(route('bankroll.withdraw'), [
                'amount' => -1000
            ])
            ->assertSessionHasErrors(['amount']);

        // Check float numbers for withdrawals
        $this->post(route('bankroll.withdraw'), [
                'amount' => 50.82
            ])
            ->assertSessionHasErrors(['amount']);

        // Create a BankrollTransaction and get it from user.
        $this->post(route('bankroll.add'), [
                'amount' => 500
            ]);
        $bankrollTransaction = $user->bankrollTransactions->first();

        // Check negative numbers for updates.
        $this->patch(route('bankroll.update', ['bankrollTransaction' => $bankrollTransaction]), [
                'amount' => -1000
            ])
            ->assertSessionHasErrors(['amount']);

        // Check float numbers for updates
        $this->patch(route('bankroll.update', ['bankrollTransaction' => $bankrollTransaction]), [
                'amount' => 50.82
            ])
            ->assertSessionHasErrors(['amount']);
    }
}
