<?php

namespace App\Observers;

use App\Transactions\Expense;

class ExpenseObserver
{
    /**
     * Handle the expense "created" event.
     *
     * @param  \App\Transactions\Expense  $expense
     * @return void
     */
    public function created(Expense $expense)
    {
        tap($expense->game)->decrement('profit', $expense->amount);
    }

    /**
     * Handle the expense "updated" event.
     *
     * @param  \App\Transactions\Expense  $expense
     * @return void
     */
    public function updated(Expense $expense)
    {
        //
    }

    /**
     * Handle the expense "deleted" event.
     *
     * @param  \App\Transactions\Expense  $expense
     * @return void
     */
    public function deleted(Expense $expense)
    {
        //
    }

    /**
     * Handle the expense "restored" event.
     *
     * @param  \App\Transactions\Expense  $expense
     * @return void
     */
    public function restored(Expense $expense)
    {
        //
    }

    /**
     * Handle the expense "force deleted" event.
     *
     * @param  \App\Transactions\Expense  $expense
     * @return void
     */
    public function forceDeleted(Expense $expense)
    {
        //
    }
}
