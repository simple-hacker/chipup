<?php

namespace App\Providers;

use App\Observers\BankrollObserver;
use App\Observers\NegativeGameTransactionObserver;
use App\Observers\PositiveGameTransactionObserver;
use Illuminate\Support\ServiceProvider;

class ObserverServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        \App\Transactions\Bankroll::observe(BankrollObserver::class);
        \App\Transactions\BuyIn::observe(NegativeGameTransactionObserver::class);
        \App\Transactions\Expense::observe(NegativeGameTransactionObserver::class);
        \App\Transactions\Rebuy::observe(NegativeGameTransactionObserver::class);
        \App\Transactions\AddOn::observe(NegativeGameTransactionObserver::class);
        \App\Transactions\CashOut::observe(PositiveGameTransactionObserver::class);
    }
}
