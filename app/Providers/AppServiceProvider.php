<?php

namespace App\Providers;

use App\BankrollTransaction;
use Illuminate\Support\ServiceProvider;
use App\Observers\BankrollTransactionObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        BankrollTransaction::observe(BankrollTransactionObserver::class);
    }
}
