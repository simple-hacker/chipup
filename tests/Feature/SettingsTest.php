<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    public function testUserMustBeLoggedInToAccessSettings()
    {
        $this->postJson(route('settings.email'), ['email' => 'different@example.com'])->assertUnauthorized();
        $this->postJson(route('settings.defaults'), [])->assertUnauthorized();
    }

    public function testUserMustCompleteSetupBeforeAccessingSettings()
    {
        $user = factory('App\User')->create();
        $this->actingAs($user);

        $this->postJson(route('settings.email'), ['email' => 'different@example.com'])->assertRedirect(route('setup.index'));
        $this->postJson(route('settings.defaults'), [])->assertRedirect(route('setup.index'));
    }

    public function testUserCanChangeTheirEmailAddress()
    {
        $user = factory('App\User')->create(['email' => 'test@example.com']);

        $this->signIn($user);

        $this->postJson('/settings/email', ['email' => 'different@example.com'])->assertOk();

        $this->assertEquals('different@example.com', $user->fresh()->email);
    }

    public function testUserCannotUpdateEmailAddressToAnExistingEmailAddress()
    {
        $user1 = factory('App\User')->create(['email' => 'user1@example.com']);

        // Create a second user with email user2@example.com and sign in.
        $user2 = factory('App\User')->create(['email' => 'user2@example.com']);
        $this->signIn($user2);

        // Try to update user2's email address to user1.
        $this->postJson('/settings/email', ['email' => 'user1@example.com'])->assertStatus(422);
    }

    public function testUserCanUpdateEmailAddressToTheSameEmailAddress()
    {
        // In case user clicks 'Change Email' without updating value, then request is still valid.
        $user = factory('App\User')->create(['email' => 'user@example.com']);
        $this->signIn($user);

        // Try to update user's email address to the same value
        $this->postJson('/settings/email', ['email' => 'user@example.com'])->assertOk();
    }

    public function testUpdatedEmailAddressMustBeValid()
    {
        // Create and sign in a user.
        $this->signIn();

        // Email is required
        $this->postJson('/settings/email', [])->assertStatus(422);

        // Email is must be an email
        $this->postJson('/settings/email', ['email' => 123])->assertStatus(422);
        $this->postJson('/settings/email', ['email' => 'NotAnEmailAddress'])->assertStatus(422);
    }

    public function testUserCanChangeTheirDefaultValues()
    {
        $defaults = [
            'default_stake_id' => 1,
            'default_limit_id' => 1,
            'default_variant_id' => 1,
            'default_table_size_id' => 1,
            'default_location' => 'CasinoMK'
        ];

        $user = factory('App\User')->create($defaults);

        $this->signIn($user);

        $updatedDefaults = [
            'default_stake_id' => 2,
            'default_limit_id' => 2,
            'default_variant_id' => 2,
            'default_table_size_id' => 2,
            'default_location' => 'Las Vegas'
        ];
        
        $this->postJson('/settings/defaults', $updatedDefaults)->assertOk();

        $this->assertDatabaseHas('users', $updatedDefaults);
        $this->assertDatabaseMissing('users', $defaults);
    }

    public function testUpdatedDefaultValuesMustBeValid()
    {
        // Create and sign in a user.
        $this->signIn();

        // stake id must be an integer
        $attributes = ['default_stake_id' => 'Not an Integer'];
        $this->postJson('/settings/defaults', $attributes)->assertStatus(422);

        // stake id must exists
        $attributes = ['default_stake_id' => 999];
        $this->postJson('/settings/defaults', $attributes)->assertStatus(422);

        // limit id must be an integer
        $attributes = ['default_limit_id' => 'Not an Integer'];
        $this->postJson('/settings/defaults', $attributes)->assertStatus(422);

        // limit id must exists
        $attributes = ['default_limit_id' => 999];
        $this->postJson('/settings/defaults', $attributes)->assertStatus(422);

        // variant id must be an integer
        $attributes = ['default_variant_id' => 'Not an Integer'];
        $this->postJson('/settings/defaults', $attributes)->assertStatus(422);

        // variant id must exists
        $attributes = ['default_variant_id' => 999];
        $this->postJson('/settings/defaults', $attributes)->assertStatus(422);

        // table_size id must be an integer
        $attributes = ['default_table_size_id' => 'Not an Integer'];
        $this->postJson('/settings/defaults', $attributes)->assertStatus(422);

        // table_size id must exists
        $attributes = ['default_table_size_id' => 999];
        $this->postJson('/settings/defaults', $attributes)->assertStatus(422);

        // location must be an string
        $attributes = ['default_location' => 100];
        $this->postJson('/settings/defaults', $attributes)->assertStatus(422);

        // location is nullable
        $attributes = ['default_location' => ''];
        $this->postJson('/settings/defaults', $attributes)->assertOk();
    }

    public function testUserCanChangeTheirPassword()
    {
        $current_password = 'password';
        $new_password = 'secret';

        $user = factory('App\User')->create([
            'password' => Hash::make($current_password)
        ]);

        $this->signIn($user);
        $this->assertTrue(Hash::check($current_password, $user->password));

        $this->postJson(route('settings.password'), [
            'current_password' => $current_password,
            'new_password' => $new_password,
            'new_password_confirmation' => $new_password,
        ])->assertOk();

        $this->assertTrue(Hash::check($new_password, $user->password));
    }

    public function testUpdatingPasswordDataMustBeValid()
    {
        $current_password = 'password';
        $new_password = 'secret';

        // Create and sign in a new user with password "password"
        $user = factory('App\User')->create([
            'password' => Hash::make($current_password)
        ]);
        $this->signIn($user);

        // Current password is required
        $this->postJson(route('settings.password'), [
            'new_password' => $new_password,
            'new_password_confirmation' => $new_password,
        ])->assertStatus(422);

        // Current password must be correct
        $this->postJson(route('settings.password'), [
            'current_password' => 'NotTheCorrectPassword',
            'new_password' => $new_password,
            'new_password_confirmation' => $new_password,
        ])->assertStatus(422);

        // New Password is required
        $this->postJson(route('settings.password'), [
            'current_password' => $current_password,
            'new_password_confirmation' => $new_password,
        ])->assertStatus(422);

        // New Password Confirmation is required
        $this->postJson(route('settings.password'), [
            'current_password' => $current_password,
            'new_password' => $new_password,
        ])->assertStatus(422);

        // New Password and New Password Confirmation must match
        $this->postJson(route('settings.password'), [
            'current_password' => $current_password,
            'new_password' => $new_password,
            'new_password_confirmation' => 'NotTheSame',
        ])->assertStatus(422);

        // New Password must contain at least 3 characters
        $this->postJson(route('settings.password'), [
            'current_password' => $current_password,
            'new_password' => '12',
            'new_password_confirmation' => '12',
        ])->assertStatus(422);
    }
}
