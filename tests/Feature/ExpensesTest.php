<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Transactions\Expense;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExpensesTest extends TestCase
{
    use RefreshDatabase;

    public function testOnlyAuthenticatedUsersCanAddExpense()
    {
        $user = \App\User::factory()->create();
        $cashGame = $user->startCashGame();

        $this->postJson(route('expense.create'), [
                    'game_id' => $cashGame->id,
                    'game_type' => $cashGame->game_type,
                    'amount' => 500
                ])
                ->assertUnauthorized();
    }

    public function testAExpenseCanBeAddedToACashGame()
    {
        $cashGame = $this->signIn()->startCashGame();

        $this->postJson(route('expense.create'), [
                    'game_id' => $cashGame->id,
                    'game_type' => $cashGame->game_type,
                    'amount' => 500
                ])
                ->assertOk()
                ->assertJsonStructure(['success', 'transaction']);;

        $this->assertCount(1, $cashGame->expenses);
        $this->assertEquals(500, $cashGame->expenses()->first()->amount);
    }

    public function testAExpenseCannotBeAddedToACashGameThatDoesNotExist()
    {
        $this->signIn();

        // ID 500 does not exist, assert 404
        $this->postJson(route('expense.create'), [
                    'game_id' => 99,
                    'game_type' => 'cash_game',
                    'amount' => 500
                ])
                ->assertNotFound();

        $this->assertCount(0, Expense::all());
    }

    public function testUserCanAddMultipleExpensesToCashGame()
    {
        $cashGame = $this->signIn()->startCashGame();

        $this->postJson(route('expense.create'), [
            'game_id' => $cashGame->id,
            'game_type' => $cashGame->game_type,
            'amount' => 500
        ]);
        $this->postJson(route('expense.create'), [
            'game_id' => $cashGame->id,
            'game_type' => $cashGame->game_type,
            'amount' => 1000
        ]);

        $this->assertCount(2, $cashGame->expenses);
        $this->assertEquals(-1500, $cashGame->fresh()->profit);
    }

    public function testViewingExpenseReturnsJsonOfExpenseTransaction()
    {
        $cashGame = $this->signIn()->startCashGame();

        $this->postJson(route('expense.create'), [
            'game_id' => $cashGame->id,
            'game_type' => $cashGame->game_type,
            'amount' => 500
        ]);

        $expense = $cashGame->expenses()->first();

        $this->getJson(route('expense.view', [
                    'cash_game' => $cashGame,
                    'expense' => $expense
                ]))
                ->assertOk()
                ->assertJsonStructure(['success', 'transaction']);
    }

    public function testAUserCanUpdateTheExpense()
    {
        $cashGame = $this->signIn()->startCashGame();

        $this->postJson(route('expense.create'), [
            'game_id' => $cashGame->id,
            'game_type' => $cashGame->game_type,
            'amount' => 500
        ]);

        $expense = $cashGame->expenses()->first();

        // Change amount from 500 to 1000
        $response = $this->patchJson(route('expense.update', ['expense' => $expense]), [
                                'amount' => 1000
                            ])
                            ->assertOk()
                            ->assertJsonStructure(['success', 'transaction']);

        $this->assertEquals(1000, $expense->fresh()->amount);
        $this->assertEquals(1000, $response['transaction']['amount']);
    }

    public function testAUserCanDeleteTheExpense()
    {
        $cashGame = $this->signIn()->startCashGame();

        $this->postJson(route('expense.create'), [
            'game_id' => $cashGame->id,
            'game_type' => $cashGame->game_type,
            'amount' => 500
        ]);

        $expense = $cashGame->expenses()->first();

        // Change amount from 500 to 1000
        $this->deleteJson(route('expense.update', ['expense' => $expense]))
                ->assertOk()
                ->assertJsonStructure(['success'])
                ->assertJson([
                    'success' => true
                ]);

        $this->assertCount(0, $cashGame->fresh()->expenses);
    }

    public function testExpenseAmountIsValidForAdd()
    {
        $cashGame = $this->signIn()->startCashGame();

        // Test not sending amount
        $this->postJson(route('expense.create'), [
                    'game_id' => $cashGame->id,
                    'game_type' => $cashGame->game_type,
                ])
                ->assertStatus(422);

        // NOTE: 2020-04-29 Float numbers are now valid.
        // Test float numbers
        $this->postJson(route('expense.create'), [
                    'game_id' => $cashGame->id,
                    'game_type' => $cashGame->game_type,
                    'amount' => 55.52
                ])
                ->assertOk();

        // Test negative numbers
        $this->postJson(route('expense.create'), [
                    'game_id' => $cashGame->id,
                    'game_type' => $cashGame->game_type,
                    'amount' => -10
                ])
                ->assertStatus(422);

        // Test string
        $this->postJson(route('expense.create'), [
                    'game_id' => $cashGame->id,
                    'game_type' => $cashGame->game_type,
                    'amount' => 'Invalid'
                ])
                ->assertStatus(422);

        // Zero should be okay
        // NOTE: 2020-06-01 Zero is no invalid on front end, though valid backend.
        $this->postJson(route('expense.create'), [
                    'game_id' => $cashGame->id,
                    'game_type' => $cashGame->game_type,
                    'amount' => 0
                ])
                ->assertStatus(422);
    }

    public function testExpenseAmountIsValidForUpdate()
    {
        $cashGame = $this->signIn()->startCashGame();

        $this->postJson(route('expense.create'), [
            'game_id' => $cashGame->id,
            'game_type' => $cashGame->game_type,
            'amount' => 500
        ]);
        $expense = $cashGame->expenses()->first();

        // Empty POST data is OK because it doesn't change anything.
        $this->patchJson(route('expense.update', ['expense' => $expense]), [])->assertOk();

        // NOTE: 2020-04-29 Float numbers are now valid.
        // Test float numbers
        $this->patchJson(route('expense.update', ['expense' => $expense]), ['amount' => 55.52])->assertOk();

        // Test negative numbers
        $this->patchJson(route('expense.update', ['expense' => $expense]), ['amount' => -10])->assertStatus(422);

        // Test string
        $this->patchJson(route('expense.update', ['expense' => $expense]), ['amount' => 'Invalid'])->assertStatus(422);

        // Zero should be okay
        // NOTE: 2020-06-01 Zero is no invalid on front end, though valid backend.
        $this->patchJson(route('expense.update', ['expense' => $expense]), ['amount' => 0])->assertStatus(422);
    }

    public function testTheExpenseMustBelongToTheAuthenticatedUser()
    {
        // User1 creates a CashGame and adds a Expense
        $user1 = $this->signIn();
        $cashGame = $user1->startCashGame();
        $this->postJson(route('expense.create'), [
                    'game_id' => $cashGame->id,
                    'game_type' => $cashGame->game_type,
                    'amount' => 500
                ]);
        $expense = $cashGame->expenses()->first();

        // Create and sign in the second user
        $user2 = $this->signIn();

        // User2 tries to Add Expense to User1's CashGame
        $this->postJson(route('expense.create'), [
                    'game_id' => $cashGame->id,
                    'game_type' => $cashGame->game_type,
                    'amount' => 1000
                ])
                ->assertForbidden();

        // User2 tries to view User1's Expense
        $this->getJson(route('expense.view', ['expense' => $expense]))
                ->assertForbidden();

        // User2 tries to update User1's Expense
        $this->patchJson(route('expense.update', ['expense' => $expense]), ['amount' => 1000])
                ->assertForbidden();

        // User2 tries to delete User1's Expense
        $this->deleteJson(route('expense.delete', ['expense' => $expense]))
                ->assertForbidden();
    }

    public function testAnExpenseCanHaveComments()
    {
        $cashGame = $this->signIn()->startCashGame();

        // You can add a comment when adding an expense.
        $this->postJson(route('expense.create'), [
            'game_id' => $cashGame->id,
            'game_type' => $cashGame->game_type,
            'currency' => 'GBP',
            'amount' => 500,
            'comments' => 'Comment'
        ]);

        $expense = $cashGame->expenses()->first();

        $this->assertEquals('Comment', $expense->comments);

        // You can also update the comments without sending amount
        $this->patchJson(route('expense.update', ['expense' => $expense]), [
            'comments' => 'Updated Comment'
        ]);

        $this->assertEquals('Updated Comment', $expense->fresh()->comments);
    }
}
