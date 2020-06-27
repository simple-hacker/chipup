<?php

namespace App\Providers;

use App\Observers\GameObserver;
use App\Observers\BankrollTransactionObserver;
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
        \App\CashGame::observe(GameObserver::class);
        \App\Tournament::observe(GameObserver::class);
        \App\Transactions\Bankroll::observe(BankrollTransactionObserver::class);
        \App\Transactions\BuyIn::observe(NegativeGameTransactionObserver::class);
        \App\Transactions\Expense::observe(NegativeGameTransactionObserver::class);
        \App\Transactions\Rebuy::observe(NegativeGameTransactionObserver::class);
        \App\Transactions\AddOn::observe(NegativeGameTransactionObserver::class);
        \App\Transactions\CashOut::observe(PositiveGameTransactionObserver::class);
    }
}
