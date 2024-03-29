<?php

namespace Tests\Feature;

use App\Models\User;
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
        $this->post('/register', [
            'name' => 'Michael',
            'email' => 'test@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ])
        ->assertRedirect('/');

        $user = User::first();

        $this->assertFalse($user->setup_complete);
    }

    public function testAUserIsRedirectedToCompleteSetupAfterRegistering()
    {
        $this->post('/register', [
            'email' => 'test@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ])
        ->assertRedirect('/');
    }

    public function testAUserCanCompleteTheirSetup()
    {
        $attributes = [
            'default_stake_id' => null,
            'default_limit_id' => null,
            'default_variant_id' => null,
            'default_table_size_id' => null,
            'default_location' => null
        ];

        $new_attributes = [
            'locale' => 'en-US',
            'currency' => 'USD',
            'bankroll' => 1000,
            'default_stake_id' => 1,
            'default_limit_id' => 1,
            'default_variant_id' => 1,
            'default_table_size_id' => 1,
            'default_location' => 'Casino MK'
        ];

        $find_new_attributes = $new_attributes;
        unset($find_new_attributes['bankroll']);

        // Create a new user with empty default attributes
        // Send in blank $attributes because UserFactory generates a user which has already chosen them.
        // So now User is as if they've just registered and have not yet completed setup.
        $user = \App\Models\User::factory()->create($attributes);
        $this->actingAs($user);

        // Assert original attributes are in table and new_attributes have not been saved yet.
        $this->assertDatabaseHas('users', $attributes);
        $this->assertDatabaseMissing('users', $find_new_attributes);

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
        $this->assertDatabaseHas('users', $find_new_attributes);

        $user->refresh();
        $this->assertEquals('en-US', $user->locale);
        $this->assertEquals('USD', $user->currency);
        $this->assertEquals(1000, $user->bankroll);
        $this->assertTrue($user->setup_complete);
    }

    public function testUserCanChangeTheirLocale()
    {
        $new_attributes = [
            'locale' => 'de-DE',
        ];
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        // Make POST request to complete user's setup with new default values.
        $this->postJson(route('setup.complete'), $new_attributes)
                ->assertOk()
                ->assertJsonStructure(['success', 'redirect'])
                ->assertJson([
                    'success' => true,
                    'redirect' => route('dashboard')
                ]);

        $user->refresh();
        $this->assertEquals('de-DE', $user->locale);
    }

    public function testLocaleMustBeAValidLocale()
    {
        $new_attributes = [
            'locale' => 'en-XX',  // Not a valid ISO 639-2
        ];
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        // Make POST request to complete user's setup with new default values.
        $this->postJson(route('setup.complete'), $new_attributes)->assertStatus(422);
    }

    public function testUserCanChangeTheirCurrency()
    {
        $new_attributes = [
            'currency' => 'USD',
        ];
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        // Make POST request to complete user's setup with new default values.
        $this->postJson(route('setup.complete'), $new_attributes)
                ->assertOk()
                ->assertJsonStructure(['success', 'redirect'])
                ->assertJson([
                    'success' => true,
                    'redirect' => route('dashboard')
                ]);

        $user->refresh();
        $this->assertEquals('USD', $user->currency);
    }

    public function testCurrencyMustBeAValidCurrency()
    {
        $new_attributes = [
            'currency' => 'ZZZ',  // Not a valid ISO 4217
        ];
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        // Make POST request to complete user's setup with new default values.
        $this->postJson(route('setup.complete'), $new_attributes)->assertStatus(422);
    }

    public function testAssertCompleteSetupDataValid()
    {
        $user = \App\Models\User::factory()->create();  // Default setup_complete is false
        $this->actingAs($user);

        // Bankroll must be postive
        $this->postJson(route('setup.complete'), ['bankroll' => -10.99])->assertStatus(422);
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

        // // Bankroll must be an integer.  Moved test from top to end because this completes setup for the rest of the above data.
        // // NOTE: 2020-04-29 Float numbers are now valid.
        $this->postJson(route('setup.complete'), ['bankroll' => 1.99])->assertOk();
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
            'default_stake_id' => 1,
            'default_limit_id' => 1,
            'default_variant_id' => 1,
            'default_table_size_id' => 1,
            'default_location' => 'Casino MK'
        ];

        $new_attributes = [
            'default_stake_id' => 2,
            'default_limit_id' => 2,
            'default_variant_id' => 2,
            'default_table_size_id' => 2,
            'default_location' => 'Las Vegas'
        ];


        // Create user and send old_attributes when completing setup.
        $user = \App\Models\User::factory()->create();
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
        $user = \App\Models\User::factory()->create(); //setup_complete default is false

        $this->actingAs($user);
        $this->get(route('dashboard'))->assertRedirect(route('setup.index'));
    }

    public function testAUserCanVisitDashboardIfSetupHasBeenCompleted()
    {
        // Create a user where setup_complete is set to false
        $user = \App\Models\User::factory()->create(); // setup_complete default is false
        // Complete Setup
        $user->completeSetup();

        $this->actingAs($user);
        $this->get(route('dashboard'))->assertOk();
    }

    public function testAUserCannotHitAnyAPIRouteIfSetupHasNotBeenCompleted()
    {
        $user = \App\Models\User::factory()->create(); // setup_complete default is false
        $this->actingAs($user);

        // Test a couple of API routes as all API routes are under the same middleware group.
        $this->postJson(route('bankroll.create'), ['amount' => 5000])->assertRedirect(route('setup.index'));
        $this->postJson(route('cash.live.start'), $this->getLiveCashGameAttributes())->assertRedirect(route('setup.index'));
        $this->postJson(route('tournament.live.start'), $this->getTournamentAttributes())->assertRedirect(route('setup.index'));
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
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);
        $this->postJson(route('setup.complete'), $attributes)->assertOk();

        $user->refresh();

        // Assert BankrollTransaction was created and user's bankroll is updated.
        //This is updated BankrollTransactionObserver created method.
        $this->assertEquals(1000, $user->bankroll);
        $this->assertCount(1, $user->bankrollTransactions);
    }
}
