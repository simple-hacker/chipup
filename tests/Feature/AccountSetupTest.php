<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AccountSetupTest extends TestCase
{
    use RefreshDatabase;

    public function testAGuestCannotViewSetup()
    {
        $this->get(route('setup.index'))->assertRedirect('login');
        $this->postJson(route('setup.complete'))->assertUnauthorized();
    }

    public function testWhenANewUserRegistersSetUpCompleteIsFalse()
    {
        $this->postJson('/register', [
            'name' => 'Michael',
            'email' => 'test@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ])
        ->assertRedirect(route('setup.index'));

        $user = User::first();

        $this->assertFalse($user->setup_complete);
    }

    public function testAUserIsRedirectedToCompleteSetupAfterRegistering()
    {
        $this->postJson('/register', [
            'email' => 'test@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ])
        ->assertRedirect(route('setup.index'));
    }

    public function testAUserCanCompleteTheirSetup()
    {
        // TODO:  Choose default currency

        $this->withoutExceptionHandling();

        $attributes = [
            'bankroll' => 0,
            'default_stake_id' => null,
            'default_limit_id' => null,
            'default_variant_id' => null,
            'default_table_size_id' => null,
            'default_location' => null
        ];

        $new_attributes = [
            'bankroll' => 1000,
            'default_stake_id' => 1,
            'default_limit_id' => 1,
            'default_variant_id' => 1,
            'default_table_size_id' => 1,
            'default_location' => 'Casino MK'
        ];

        // Create a new user with empty default attributes
        // Send in blank $attributes because UserFactory generates a user which has already chosen them.
        // So now User is as if they've just registered and have not yet completed setup.
        $user = factory('App\User')->create($attributes);
        $this->actingAs($user);

        // Assert original attributes are in table and new_attributes have not been saved yet.
        $this->assertDatabaseHas('users', $attributes);
        $this->assertDatabaseMissing('users', $new_attributes);

        // Make POST request to complete user's setup with new default values.
        $this->postJson(route('setup.complete'), $new_attributes)
                ->assertOk()
                ->assertJsonStructure(['success', 'redirect'])
                ->assertJson([
                    'success' => true,
                    'redirect' => route('dashboard')
                ]);

        // Assert original attributes are missing from table and have been replaced by new attributes
        $this->assertDatabaseMissing('users', $attributes);
        $this->assertDatabaseHas('users', $new_attributes);
        
        $user->refresh();
        $this->assertEquals(1000, $user->bankroll);
        $this->assertTrue($user->setup_complete);
    }

    public function testAssertCompleteSetupDataValid()
    {
        $user = factory('App\User')->create();  // Default setup_complete is false
        $this->actingAs($user);

        // Bankroll must be an integer
        $this->postJson(route('setup.complete'), ['bankroll' => 1.99])->assertStatus(422);
        // Bankroll must be postive
        $this->postJson(route('setup.complete'), ['bankroll' => -1000])->assertStatus(422);
        // Stake Id must be an integer
        $this->postJson(route('setup.complete'), ['default_stake_id' => 'Not an integer'])->assertStatus(422);
        // Stake Id must exist in the stakes table
        $this->postJson(route('setup.complete'), ['default_stake_id' => 99])->assertStatus(422);
        // Variant Id must be an integer
        $this->postJson(route('setup.complete'), ['default_variant_id' => 'Not an integer'])->assertStatus(422);
        // Variant Id must exist in the variants table
        $this->postJson(route('setup.complete'), ['default_variant_id' => 99])->assertStatus(422);
        // Limit Id must be an integer
        $this->postJson(route('setup.complete'), ['default_limit_id' => 'Not an integer'])->assertStatus(422);
        // Limit Id must exist in the limits table
        $this->postJson(route('setup.complete'), ['default_limit_id' => 99])->assertStatus(422);
        // Table Size Id must be an integer
        $this->postJson(route('setup.complete'), ['default_table_size_id' => 'Not an integer'])->assertStatus(422);
        // Table Size Id must exist in the table_sizes table
        $this->postJson(route('setup.complete'), ['default_table_size_id' => 99])->assertStatus(422);
        // Location must be a string
        $this->postJson(route('setup.complete'), ['default_location' => 99])->assertStatus(422);
    }

    public function testAUserCannotVisitPageToCompleteSetupIfAlreadyCompleted()
    {
        $user = $this->signIn();
        $user->completeSetup();

        $this->get(route('setup.index'))->assertRedirect(route('dashboard'));
    }

    public function testAUserCannotPOSTToCompleteSetupIfSetupHasAlreadyBeenCompleted()
    {
        // A user gets redirected to dashboard both when POSTing to setup.complete and with middleware setup.incomplete
        // So we can't just rely on assertRedirect('dashboard') to assert middleware is working.

        // Need to complete setup with values, then try again with new values and assert database wasn't updated with new values

        $old_attributes = [
            'bankroll' => 1000,
            'default_stake_id' => 1,
            'default_limit_id' => 1,
            'default_variant_id' => 1,
            'default_table_size_id' => 1,
            'default_location' => 'Casino MK'
        ];

        $new_attributes = [
            'bankroll' => 9999,
            'default_stake_id' => 2,
            'default_limit_id' => 2,
            'default_variant_id' => 2,
            'default_table_size_id' => 2,
            'default_location' => 'Las Vegas'
        ];

        // Create user and send old_attributes when completing setup.
        $user = factory('App\User')->create(['bankroll' => 0]);
        $this->actingAs($user);
        $this->postJson(route('setup.complete'), $old_attributes)
                ->assertOk()
                ->assertJsonStructure(['success', 'redirect'])
                ->assertJson([
                    'success' => true,
                    'redirect' => route('dashboard')
                ]);

        // Assert old_attributes are in table and new_attributes have not been saved yet.
        $this->assertDatabaseHas('users', $old_attributes);
        $this->assertDatabaseMissing('users', $new_attributes);

        // Make POST request to complete user's setup with new_attributes
        // and assertRedirect to dashboard (which should be because of the middleware and not the controller)
        $this->postJson(route('setup.complete'), $new_attributes)->assertRedirect(route('dashboard'));

        // If middleware worked correctly then database should not have been changed
        // and so old_attributes will still be in the database, and assert new_attributes are not and weren't saved
        $this->assertDatabaseHas('users', $old_attributes);
        $this->assertDatabaseMissing('users', $new_attributes);
    }

    public function testAUserIsRedirectedToCompleteSetupWhenVisitingDashboardIfNotCompleted()
    {
        // Create a user where setup_complete is set to false
        $user = factory('App\User')->create(); //setup_complete default is false
        
        $this->actingAs($user);
        $this->get(route('dashboard'))->assertRedirect(route('setup.index'));
    }

    public function testAUserCanVisitDashboardIfSetupHasBeenCompleted()
    {
        // Create a user where setup_complete is set to false
        $user = factory('App\User')->create(); // setup_complete default is false
        // Complete Setup
        $user->completeSetup();
        
        $this->actingAs($user);
        $this->get(route('dashboard'))->assertOk();
    }

    public function testAUserCannotHitAnyAPIRouteIfSetupHasNotBeenCompleted()
    {
        $user = factory('App\User')->create(); // setup_complete default is false
        $this->actingAs($user);

        // Test a couple of API routes as all API routes are under the same middleware group.
        $this->postJson(route('bankroll.create'), ['amount' => 5000])->assertRedirect(route('setup.index'));
        $this->postJson(route('cash.start'), $this->getCashGameAttributes())->assertRedirect(route('setup.index'));
        $this->postJson(route('tournament.start'), $this->getTournamentAttributes())->assertRedirect(route('setup.index'));
    }

    public function testABankrollTransactionIsAddedIfUserGivesBankrollAmountInSetup()
    {
        $attributes = [
            'bankroll' => 1000,
            'default_stake_id' => 1,
            'default_limit_id' => 1,
            'default_variant_id' => 1,
            'default_table_size_id' => 1,
            'default_location' => 'Casino MK'
        ];

        // Create user and send attributes when completing setup.
        $user = factory('App\User')->create(['bankroll' => 0]);
        $this->actingAs($user);
        $this->postJson(route('setup.complete'), $attributes)->assertOk();

        $user->refresh();

        // Assert BankrollTransaction was created and user's bankroll is updated.
        //This is updated BankrollTransactionObserver created method.
        $this->assertEquals(1000, $user->bankroll);
        $this->assertCount(1, $user->bankrollTransactions);
    }
}
