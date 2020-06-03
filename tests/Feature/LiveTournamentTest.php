<?php

namespace Tests\Feature;

use App\Tournament;
use App\Attributes\Limit;
use App\Attributes\Variant;
use App\Attributes\TableSize;
use App\Transactions\CashOut;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LiveTournamentTest extends TestCase
{
    use RefreshDatabase;

    public function testAUserMustBeLoggedInToStartTournament()
    {
        factory('App\User')->create();

        $this->postJson(route('tournament.live.start'))
                ->assertUnauthorized();

        $this->getJson(route('tournament.live.current'))
                ->assertUnauthorized();
    }

    public function testAUserCanStartALiveTournament()
    {   
        $user = $this->signIn();

        $request = $this->postJson(route('tournament.live.start'), $this->getLiveTournamentAttributes())
                ->assertOk()
                ->assertJsonStructure(['success', 'tournament'])
                ->assertJson([
                    'success' => true
                ]);

        $this->assertCount(1, Tournament::all());
        $tournament = Tournament::first();
        $this->assertEquals($user->id, $tournament->user_id);
    }

    public function testAUserCanStartATournamentAtASpecifiedTime()
    {
        $user = $this->signIn();

        // We'll be passing a Y-m-d H:i:s string from the front end.
        $start_time = Carbon::create('-2 hours')->toDateTimeString();

        $this->postJson(route('tournament.live.start'), $this->getLiveTournamentAttributes(1000, $start_time));
        $tournament = Tournament::first();

        $this->assertEquals($start_time, $tournament->start_time);
    }

    public function testAUserCannotStartATournamentInTheFuture()
    {
        $user = $this->signIn();

        // We'll be passing a Y-m-d H:i:s string from the front end.
        $start_time = Carbon::create('+1 min')->toDateTimeString();

        $this->postJson(route('tournament.live.start'), $this->getLiveTournamentAttributes(1000, $start_time))
                ->assertStatus(422);
    }

    public function testAUserCanGetTheirCurrentLiveTournament()
    {
        $user = $this->signIn();
        // Start Tournament
        $this->post(route('tournament.live.start'), $this->getLiveTournamentAttributes());

        $response = $this->getJson(route('tournament.live.current'))
                            ->assertJsonStructure(['success', 'status', 'tournament'])
                            ->assertJson([
                                'success' => true,
                                'status' => 'live'
                            ])
                            ->assertOk();

        $this->assertEquals($response->json()['status'], 'live');
        $this->assertEquals($response->json()['tournament'], $user->liveTournament()->toArray());
    }

    public function testGettingAUsersLiveTournamentWhenNotStartedResultsInNull()
    {
        $user = $this->signIn();
        // Don't start Tournament

        $this->getJson(route('tournament.live.current'))
                ->assertJsonStructure(['success', 'message'])
                ->assertJson([
                    'success' => false
                ]);
                            
        $this->assertEmpty($user->liveTournament());
    }

    public function testAUserCannotStartATournamentWhenThereIsOneInProgress()
    {
        $this->signIn();

        // Start one tournament
        $this->postJson(route('tournament.live.start'), $this->getLiveTournamentAttributes());
        
        // Start the second tournament without finishing the other one
        $this->postJson(route('tournament.live.start'), $this->getLiveTournamentAttributes())
                ->assertStatus(422)
                ->assertJsonStructure(['success', 'message'])
                ->assertJson([
                    'success' => false,
                ]);
    }

    public function testAUserCanEndATournament()
    {
        $user = $this->signIn();

        // Start one tournament
        $this->postJson(route('tournament.live.start'), $this->getLiveTournamentAttributes());

        //  End the tournament
        $this->postJson(route('tournament.live.end'), [
                'amount' => 100
            ])->assertOk();

        $tournament = $user->tournaments()->first();

        $this->assertEquals($tournament->end_time, Carbon::now()->toDateTimeString());      
    }

    public function testAUserCanEndATournamentAtASuppliedTime()
    {
        $user = $this->signIn();

        // Start one tournament
        $start_time = Carbon::create('-1 hour')->toDateTimeString();

        $this->postJson(route('tournament.live.start'), $this->getLiveTournamentAttributes(1000, $start_time))->assertOk();

        $end_time = Carbon::create('-10 mins')->toDateTimeString();

        // End the tournament
        $this->postJson(route('tournament.live.end'), [
                    'end_time' => $end_time,
                    'amount' => 100
                ])
                ->assertOk();

        $tournament = $user->tournaments()->first();
        $this->assertEquals($tournament->end_time, $end_time);
    }

    public function testAUserCannotEndATournamentAtAInvalidTime()
    {
        $this->signIn();

        // Start one tournament
        $this->postJson(route('tournament.live.start'), $this->getLiveTournamentAttributes())
                ->assertOk();

        // End the tournament trying a string and a number
        $this->postJson(route('tournament.live.end'), [
                    'end_time' => 'this is not a date',
                    'amount' => 100
                ])
                ->assertStatus(422);

        $this->postJson(route('tournament.live.end'), [
                    'end_time' => 34732,
                    'amount' => 100
                ])
                ->assertStatus(422);
    }

    public function testAUserCannotEndATournamentWhichDoesntExist()
    {
        $user = $this->signIn();

        // Don't Start Tournament

        // End the tournament
        $this->postJson(route('tournament.live.end'), [
                    'amount' => 100
                ])
                ->assertStatus(422)
                ->assertJsonStructure(['success', 'message'])
                ->assertJson([
                    'success' => false,
                ]);
    }

    public function testAUserCannotEndATournamentBeforeItsStartTime()
    {
        $start_time = Carbon::create('-1 hour')->toDateTimeString();
        $end_time = Carbon::create('-2 hour')->toDateTimeString();

        $this->signIn();

        // Start one tournament at $start_time
        $this->postJson(route('tournament.live.start'), $this->getLiveTournamentAttributes(1000, $start_time))
                ->assertOk();

        // End the tournament at $end_time which is before $start_time
        $this->postJson(route('tournament.live.end'), [
                    'end_time' => $end_time,
                    'amount' => 100
                ])
                ->assertStatus(422)
                ->assertJsonStructure(['success', 'message'])
                ->assertJson([
                    'success' => false,
                ]);
    }

    public function testABuyInCanBeSuppliedWhenStartingATournament()
    {
        $user = $this->signIn();

        // Start one tournament
        $this->postJson(route('tournament.live.start'), $this->getLiveTournamentAttributes(5000))
                ->assertOk();

        // Tournament profit should be -5000 as it's a BuyIn
        $tournament = $user->tournaments()->first();
        $this->assertEquals(-5000, $tournament->profit);
        $this->assertCount(1, $tournament->buyIns);

        $buy_in = $tournament->buyIns()->first();
        $this->assertEquals(5000, $buy_in->amount);

        //Check user's bankroll.  It should be 5000 as orignal bankroll is 10000, but we subtract 5000 as it's a buyIn (negative transaction)
        $this->assertEquals(5000, $user->fresh()->bankroll);
    }

    public function testTheBuyInAmountMustBeValidWhenStartingATournament()
    {
        $this->signIn();

        // Should fail when starting with a negative number
        $this->postJson(route('tournament.live.start'), $this->getLiveTournamentAttributes(-1000))->assertStatus(422);

        // NOTE: 2020-04-29 Float numbers are now valid.
        // Should fail when starting with a float number
        $this->postJson(route('tournament.live.start'), $this->getLiveTournamentAttributes(5.2))->assertOk();

        // Delete the tournament for the test because we can only have one tournament at a time which was created
        // in the assertion above
        Tournament::first()->delete();

        // Starting with 0 is ok because of Freerolls
        $this->postJson(route('tournament.live.start'), $this->getLiveTournamentAttributes(0))->assertOk();
    }

    public function testAUserCanSupplyACashOutWhenEndingTheirSession()
    {
        $user = $this->signIn();

        // Start one tournament
        $this->postJson(route('tournament.live.start'), $this->getLiveTournamentAttributes(1000));

        // End the tournament with a CashOut amount
        $this->postJson(route('tournament.live.end'), [
                'amount' => 5000
            ])
            ->assertOk();

        // Tournament profit should be 4000. (-1000 buyIn + 5000 cashOut)
        $tournament = $user->tournaments()->first();
        $this->assertEquals(4000, $tournament->profit);

        $cash_out = $tournament->cashOutModel;
        $this->assertInstanceOf(CashOut::class, $cash_out);
        $this->assertEquals(5000, $cash_out->amount);

        //Check user's bankroll.  It should be 14,000 as factory default is 10,000.  (Bankroll + Tournament Profit)
        $this->assertEquals(14000, $user->fresh()->bankroll);
    }

    public function testTheCashOutAmountMustBeValidWhenEndingATournament()
    {
        $user = $this->signIn();

        // Start one tournament
        $this->postJson(route('tournament.live.start'), $this->getLiveTournamentAttributes());

        // Should fail when ending with a negative number
        $this->postJson(route('tournament.live.end'), [
                'amount' => -1000
            ])
            ->assertStatus(422);

        // Ending with 0 is ok
        $this->postJson(route('tournament.live.end'), [
                'amount' => 0
            ])
            ->assertOk();
    }

    public function testTournamentAttributesMustBeValidWhenAdding()
    {
        $user = $this->signIn();

        // Variant must exist in database
        $this->postJson(route('tournament.live.start'), [
                    'amount' => 1000,
                    'limit_id' => Limit::inRandomOrder()->first()->id,
                    'location' => 'Casino MK',
                ])
                ->assertStatus(422);

        // Variant must exist in database
        $this->postJson(route('tournament.live.start'), [
                    'amount' => 1000,
                    'variant_id' => 999,
                    'limit_id' => Limit::inRandomOrder()->first()->id,
                    'location' => 'Casino MK',
                ])
                ->assertStatus(422);

        // Limit must exist in database
        $this->postJson(route('tournament.live.start'), [
                    'amount' => 1000,
                    'variant_id' => Variant::inRandomOrder()->first()->id,
                    'location' => 'Casino MK',
                ])
                ->assertStatus(422);

        // Limit must exist in database
        $this->postJson(route('tournament.live.start'), [
                    'amount' => 1000,
                    'limit_id' => 999,
                    'variant_id' => Variant::inRandomOrder()->first()->id,
                    'location' => 'Casino MK',
                ])
                ->assertStatus(422);

        // Location must be supplied
        $this->postJson(route('tournament.live.start'), [
                    'amount' => 1000,
                    'variant_id' => Variant::inRandomOrder()->first()->id,
                    'limit_id' => Limit::inRandomOrder()->first()->id,
                ])
                ->assertStatus(422);

        // Location must be a string
        $this->postJson(route('tournament.live.start'), [
                    'amount' => 1000,
                    'variant_id' => Variant::inRandomOrder()->first()->id,
                    'limit_id' => Limit::inRandomOrder()->first()->id,
                    'location' => 328
                ])
                ->assertStatus(422);

        // Entries must be an positive integer
        $this->postJson(route('tournament.live.start'), [
                    'amount' => 1000,
                    'variant_id' => Variant::inRandomOrder()->first()->id,
                    'limit_id' => Limit::inRandomOrder()->first()->id,
                    'location' => 'Casino MK',
                    'entries' => -3
                ])
                ->assertStatus(422);
        $this->postJson(route('tournament.live.start'), [
                    'amount' => 1000,
                    'variant_id' => Variant::inRandomOrder()->first()->id,
                    'limit_id' => Limit::inRandomOrder()->first()->id,
                    'location' => 'Casino MK',
                    'entries' => 'not an integer'
                ])
                ->assertStatus(422);
    }

    public function testAUserCanUpdateTheLiveTournamentDetails()
    {
        $this->withoutExceptionHandling();

        $tournament = $this->startLiveTournament();

        $start_time = Carbon::create('-1 hour')->toDateTimeString();

        $attributes = [
            'start_time' => $start_time,
            'limit_id' => 2,
            'variant_id' => 2,
            'entries' => 55,
            'location' => 'Las Vegas',
            'entries' => 300,
            'comments' => 'New comments',
        ];

        $this->patchJson(route('tournament.live.update', ['tournament' => $tournament->id]), $attributes)
                ->assertOk()
                ->assertJsonStructure(['success', 'tournament'])
                ->assertJson([
                    'success' => true
                ]);

        $this->assertDatabaseHas('tournaments', $attributes);
    }
}
