<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Attributes\Stake;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StakeTest extends TestCase
{
    use RefreshDatabase;

    public function testUserMustBeLoggedInToAccessStakeRoutes()
    {
        $stake = Stake::create([
            'small_blind' => 1,
            'big_blind' => 2,
        ]);

        $this->postJson(route('stake.create'))->assertUnauthorized();
        $this->patchJson(route('stake.update', ['stake' => $stake]))->assertUnauthorized();
        $this->deleteJson(route('stake.delete', ['stake' => $stake]))->assertUnauthorized();
    }

    public function testUserCanAddACustomStake()
    {
        $this->signIn();

        $attributes = [
            'small_blind' => 11,
            'big_blind' => 22,
        ];

        $this->postJson(route('stake.create'), $attributes)->assertOk();

        $this->assertDatabaseHas('stakes', $attributes);
    }

    public function testStakesAreReturnedWhenCreatingCustomStake()
    {
        $stakes_count = Stake::all()->count();

        $this->signIn();

        $attributes = [
            'small_blind' => 11,
            'big_blind' => 22,
        ];

        $response = $this->postJson(route('stake.create'), $attributes);

        $stakes = auth()->user()->stakes->toArray();

        // Assert the JSON response contains all the default stakes and the custom user stake.
        $response->assertJsonCount($stakes_count + 1, 'stakes');

        $response->assertJson(['stakes' => $stakes]);
    }

    public function testUserCanGetTheirCustomStakesAsWellAsTheDefault()
    {
        $defaultStakes = Stake::all();

        $this->signIn();

        $attributes = [
            'small_blind' => 11,
            'big_blind' => 22,
        ];

        // Create one custom stake.
        $this->postJson(route('stake.create'), $attributes)->assertOk();

        // Assert the number of stakes is now the count of default stakes + 1.
        $this->assertEquals($defaultStakes->count() + 1, auth()->user()->stakes->count());

        // Sign in as a new different user.
        $this->signIn();
        // Assert the number of stakes is now the count of default stakes only
        $this->assertEquals($defaultStakes->count(), auth()->user()->stakes->count());
    }

    public function testCustomStakeDataMustBeValidWhenCreating()
    {
        $validAttributes = [
            'small_blind' => 10,
            'big_blind' => 20,
            'straddle_1' => 30,
            'straddle_2' => 40,
            'straddle_3' => 50,
        ];

        $this->signIn();

        // Small and big blinds are required
        $attributes = $validAttributes;
        unset($attributes['small_blind']);
        $this->postJson(route('stake.create'), $attributes)->assertStatus(422);
        $attributes = $validAttributes;
        unset($attributes['big_blind']);
        $this->postJson(route('stake.create'), $attributes)->assertStatus(422);

        // Must be a number
        $attributes = $validAttributes;
        $attributes['small_blind'] = 'not a number';
        $this->postJson(route('stake.create'), $attributes)->assertStatus(422);
        $attributes = $validAttributes;
        $attributes['big_blind'] = 'not a number';
        $this->postJson(route('stake.create'), $attributes)->assertStatus(422);

        // Cannot be negative
        $attributes = $validAttributes;
        $attributes['small_blind'] = -1;
        $this->postJson(route('stake.create'), $attributes)->assertStatus(422);
        $attributes = $validAttributes;
        $attributes['big_blind'] = -1;
        $this->postJson(route('stake.create'), $attributes)->assertStatus(422);

        // Cannot be null
        $attributes = $validAttributes;
        $attributes['small_blind'] = null;
        $this->postJson(route('stake.create'), $attributes)->assertStatus(422);
        $attributes = $validAttributes;
        $attributes['big_blind'] = null;
        $this->postJson(route('stake.create'), $attributes)->assertStatus(422);

        // Cannot be zero
        $attributes = $validAttributes;
        $attributes['small_blind'] = 0;
        $this->postJson(route('stake.create'), $attributes)->assertStatus(422);
        $attributes = $validAttributes;
        $attributes['big_blind'] = 0;
        $this->postJson(route('stake.create'), $attributes)->assertStatus(422);
    }
    
    public function testBigBlindMustBeGreaterThanOrEqualToSmallBlind()
    {
        // Small and Big blind can be equal
        $validAttributes = [
            'small_blind' => 10,
            'big_blind' => 20,
            'straddle_1' => 30,
            'straddle_2' => 40,
            'straddle_3' => 50,
        ];

        $this->signIn();

        // Big Blind cannot be smaller than small blind
        $attributes = $validAttributes;
        $attributes['small_blind'] = 5;
        $attributes['big_blind'] = 4.99;
        $this->postJson(route('stake.create'), $attributes)->assertStatus(422);

        // Big Blind can be equal to small blind
        $attributes = $validAttributes;
        $attributes['small_blind'] = 5;
        $attributes['big_blind'] = 5;
        $this->postJson(route('stake.create'), $attributes)->assertOk();

    }

    public function testStraddlesMustBeGreaterThanOrEqualToBigBlind()
    {
        $validAttributes = [
            'small_blind' => 10,
            'big_blind' => 20,
            'straddle_1' => 30,
            'straddle_2' => 40,
            'straddle_3' => 50,
        ];

        $this->signIn();

        // Straddle 1 is required if Straddle_2 or Straddle_3 are present
        $attributes = $validAttributes;
        unset($attributes['straddle_1'], $attributes['straddle_2']);
        $this->postJson(route('stake.create'), $attributes)->assertStatus(422);

        // Straddle 2 is required if Straddle_3 are present
        $attributes = $validAttributes;
        unset($attributes['straddle_2']);
        $this->postJson(route('stake.create'), $attributes)->assertStatus(422);

        // Straddle_1 must be greater than or equal to Big Blind
        $attributes = [
            'small_blind' => 10,
            'big_blind' => 20,
            'straddle_1' => 19.99,
        ];
        $this->postJson(route('stake.create'), $attributes)->assertStatus(422);

        // Straddle_2 must be greater than or equal to Straddle_1
        $attributes = [
            'small_blind' => 10,
            'big_blind' => 20,
            'straddle_1' => 25,
            'straddle_2' => 24.99,
        ];
        $this->postJson(route('stake.create'), $attributes)->assertStatus(422);

        // Straddle_3 must be greater than or equal to Straddle_2
        $attributes = [
            'small_blind' => 10,
            'big_blind' => 20,
            'straddle_1' => 25,
            'straddle_2' => 30,
            'straddle_3' => 29.99,
        ];
        $this->postJson(route('stake.create'), $attributes)->assertStatus(422);

        // All blinds and straddles equal is valid
        $attributes = [
            'small_blind' => 10,
            'big_blind' => 10,
            'straddle_1' => 10,
            'straddle_2' => 10,
            'straddle_3' => 10,
        ];
        $this->postJson(route('stake.create'), $attributes)->assertOk();
    }

    public function testUserCanUpdateTheirCustomStake()
    {
        $user = $this->signIn();
        $stake = Stake::create(['user_id' => $user->id, 'small_blind' => 10, 'big_blind' => 20, 'straddle_1' => 45]);

        $newAttributes = ['small_blind' => 100, 'big_blind' => 200];

        $this->patchJson(route('stake.update', ['stake' => $stake->id]), $newAttributes)->assertOk();
        $this->assertDatabaseHas('stakes', array_merge(['user_id' => $user->id], $newAttributes));
    }

    public function testStakesAreReturnedWhenUpdatingCustomStake()
    {
        $user = $this->signIn();
        $stake = Stake::create(['user_id' => $user->id, 'small_blind' => 10, 'big_blind' => 20]);

        $newAttributes = ['small_blind' => 100, 'big_blind' => 200];

        $this->patchJson(route('stake.update', ['stake' => $stake->id]), $newAttributes)
            ->assertJson(['stakes' => auth()->user()->stakes->toArray()]);
    }

    public function testDataMustBeValidWhenUpdating()
    {
        $validAttributes = [
            'small_blind' => 1,
            'big_blind' => 2,
            'straddle_1' => 4,
            'straddle_2' => 8,
            'straddle_3' => 16,
        ];

        $user = $this->signIn();
        $stake = Stake::create(['user_id' => $user->id, 'small_blind' => 10, 'big_blind' => 20]);

        // Small and big blinds are required
        $attributes = $validAttributes;
        unset($attributes['small_blind']);
        $this->patchJson(route('stake.update', ['stake' => $stake->id]), $attributes)->assertStatus(422);
        $attributes = $validAttributes;
        unset($attributes['big_blind']);
        $this->patchJson(route('stake.update', ['stake' => $stake->id]), $attributes)->assertStatus(422);

        // Must be a number
        $attributes = $validAttributes;
        $attributes['small_blind'] = 'not a number';
        $this->patchJson(route('stake.update', ['stake' => $stake->id]), $attributes)->assertStatus(422);
        $attributes = $validAttributes;
        $attributes['big_blind'] = 'not a number';
        $this->patchJson(route('stake.update', ['stake' => $stake->id]), $attributes)->assertStatus(422);

        // Cannot be negative
        $attributes = $validAttributes;
        $attributes['small_blind'] = -1;
        $this->patchJson(route('stake.update', ['stake' => $stake->id]), $attributes)->assertStatus(422);
        $attributes = $validAttributes;
        $attributes['big_blind'] = -1;
        $this->patchJson(route('stake.update', ['stake' => $stake->id]), $attributes)->assertStatus(422);

        // Cannot be null
        $attributes = $validAttributes;
        $attributes['small_blind'] = null;
        $this->patchJson(route('stake.update', ['stake' => $stake->id]), $attributes)->assertStatus(422);
        $attributes = $validAttributes;
        $attributes['big_blind'] = null;
        $this->patchJson(route('stake.update', ['stake' => $stake->id]), $attributes)->assertStatus(422);

        // Cannot be zero
        $attributes = $validAttributes;
        $attributes['small_blind'] = 0;
        $this->patchJson(route('stake.update', ['stake' => $stake->id]), $attributes)->assertStatus(422);
        $attributes = $validAttributes;
        $attributes['big_blind'] = 0;
        $this->patchJson(route('stake.update', ['stake' => $stake->id]), $attributes)->assertStatus(422);
    }

    public function testBigBlindMustBeGreaterThanOrEqualToSmallBlindWhenUpdating()
    {
        $validAttributes = [
            'small_blind' => 10,
            'big_blind' => 20,
            'straddle_1' => 30,
            'straddle_2' => 40,
            'straddle_3' => 50,
        ];

        $user = $this->signIn();
        $stake = Stake::create(['user_id' => $user->id, 'small_blind' => 10, 'big_blind' => 20]);

        // Straddle 1 is required if Straddle_2 or Straddle_3 are present
        $attributes = $validAttributes;
        unset($attributes['straddle_1'], $attributes['straddle_2']);
        $this->patchJson(route('stake.update', ['stake' => $stake->id]), $attributes)->assertStatus(422);

        // Straddle 2 is required if Straddle_3 are present
        $attributes = $validAttributes;
        unset($attributes['straddle_2']);
        $this->patchJson(route('stake.update', ['stake' => $stake->id]), $attributes)->assertStatus(422);

        // Straddle_1 must be greater than or equal to Big Blind
        $attributes = [
            'small_blind' => 10,
            'big_blind' => 20,
            'straddle_1' => 19.99,
        ];
        $this->patchJson(route('stake.update', ['stake' => $stake->id]), $attributes)->assertStatus(422);

        // Straddle_2 must be greater than or equal to Straddle_1
        $attributes = [
            'small_blind' => 10,
            'big_blind' => 20,
            'straddle_1' => 25,
            'straddle_2' => 24.99,
        ];
        $this->patchJson(route('stake.update', ['stake' => $stake->id]), $attributes)->assertStatus(422);

        // Straddle_3 must be greater than or equal to Straddle_2
        $attributes = [
            'small_blind' => 10,
            'big_blind' => 20,
            'straddle_1' => 25,
            'straddle_2' => 30,
            'straddle_3' => 29.99,
        ];
        $this->patchJson(route('stake.update', ['stake' => $stake->id]), $attributes)->assertStatus(422);

        // All blinds and straddles equal is valid
        $attributes = [
            'small_blind' => 10,
            'big_blind' => 10,
            'straddle_1' => 10,
            'straddle_2' => 10,
            'straddle_3' => 10,
        ];
        $this->patchJson(route('stake.update', ['stake' => $stake->id]), $attributes)->assertOk();
    }

    public function testStraddlesMustBeGreaterThanOrEqualToBigBlindWhenUpdating()
    {
        $validAttributes = [
            'small_blind' => 10,
            'big_blind' => 20,
            'straddle_1' => 30,
            'straddle_2' => 40,
            'straddle_3' => 50,
        ];

        $user = $this->signIn();
        $stake = Stake::create(['user_id' => $user->id, 'small_blind' => 2, 'big_blind' => 4]);

        // Straddle 1 is required if Straddle_2 or Straddle_3 are present
        $attributes = $validAttributes;
        unset($attributes['straddle_1'], $attributes['straddle_2']);
        $this->patchJson(route('stake.update', ['stake' => $stake->id]), $attributes)->assertStatus(422);

        // Straddle 2 is required if Straddle_3 are present
        $attributes = $validAttributes;
        unset($attributes['straddle_2']);
        $this->patchJson(route('stake.update', ['stake' => $stake->id]), $attributes)->assertStatus(422);

        // Straddle_1 must be greater than or equal to Big Blind
        $attributes = [
            'small_blind' => 10,
            'big_blind' => 20,
            'straddle_1' => 19.99,
        ];
        $this->patchJson(route('stake.update', ['stake' => $stake->id]), $attributes)->assertStatus(422);

        // Straddle_2 must be greater than or equal to Straddle_1
        $attributes = [
            'small_blind' => 10,
            'big_blind' => 20,
            'straddle_1' => 25,
            'straddle_2' => 24.99,
        ];
        $this->patchJson(route('stake.update', ['stake' => $stake->id]), $attributes)->assertStatus(422);

        // Straddle_3 must be greater than or equal to Straddle_2
        $attributes = [
            'small_blind' => 10,
            'big_blind' => 20,
            'straddle_1' => 25,
            'straddle_2' => 30,
            'straddle_3' => 29.99,
        ];
        $this->patchJson(route('stake.update', ['stake' => $stake->id]), $attributes)->assertStatus(422);

        // All blinds and straddles equal is valid
        $attributes = [
            'small_blind' => 10,
            'big_blind' => 10,
            'straddle_1' => 10,
            'straddle_2' => 10,
            'straddle_3' => 10,
        ];
        $this->patchJson(route('stake.update', ['stake' => $stake->id]), $attributes)->assertOk();
    }

    // public function testStraddlesAreResetIfExistedButUpdateRequestDoesNotIncludeThem()
    // {
    //     // This is to prevent updating a Stake and making the new small and big blinds greater than the old straddles
    //     // Because you can update a stake with only small and big blinds in the request.  Straddles are sometimes present.
    //     $user = $this->signIn();
    //     $stake = Stake::create([
    //         'user_id' => $user->id,
    //         'small_blind' => 10,
    //         'big_blind' => 20,
    //         'straddle_1' => 30,
    //         'straddle_2' => 40,
    //         'straddle_3' => 50,
    //     ]);

    //     $attributes = ['small_blind' => 100, 'big_blind' => 200];

    //     $this->patchJson(route('stake.update', ['stake' => $stake->id]), $attributes)->assertOk();

    //     $stake->refresh();
    //     $this->assertEquals(100, $stake->small_blind);
    //     $this->assertEquals(200, $stake->big_blind);
    //     $this->assertNull($stake->straddle_1);
    //     $this->assertNull($stake->straddle_2);
    //     $this->assertNull($stake->straddle_3);
    // }

    public function testUserCanDeleteTheirCustomStake()
    {
        $defaultStakes = Stake::all();
        $user = $this->signIn();
        $stake = Stake::create(['user_id' => $user->id, 'small_blind' => 22, 'big_blind' => 33]);

        // Assert stake was created as there should be one more than the number of default stakes.
        $this->assertEquals($user->stakes->count(), $defaultStakes->count() + 1);

        // Assert ok to delete.
        $this->deleteJson(route('stake.delete', ['stake' => $stake->id]))->assertOk();

        $this->assertDatabaseMissing('stakes', $stake->toArray());
        // Assert user stakes count is now just the default stakes count
        $this->assertEquals($user->stakes->count(), $defaultStakes->count());

    }

    public function testUserCannotManageAnotherUsersCustomStake()
    {
        $user = $this->signIn();
        $stake = Stake::create(['user_id' => $user->id, 'small_blind' => 2, 'big_blind' => 3]);

        // Create and sign in a new user
        $this->signIn();

        // Try to update user 1's custom stake as user 2.
        $attributes = ['small_blind' => 10, 'big_blind' => 20];
        $this->patchJson(route('stake.update', ['stake' => $stake->id]), $attributes)->assertForbidden();

        // Still as user 2, try to delete user 1's custom stake.
        $this->deleteJson(route('stake.delete', ['stake' => $stake->id]))->assertForbidden();
    }

    public function testUserCannotManageTheDefaultStakes()
    {
        // Grab the first default stake
        $stake = Stake::first();
        $this->assertNull($stake->user_id);

        // Create and sign in a new user
        $this->signIn();

        // Try to update default stake
        $attributes = ['small_blind' => 10, 'big_blind' => 20];
        $this->patchJson(route('stake.update', ['stake' => $stake->id]), $attributes)->assertForbidden();

        // Try to delete default stake
        $this->deleteJson(route('stake.delete', ['stake' => $stake->id]))->assertForbidden();
    }
}
