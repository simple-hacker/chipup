<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Transactions\Bankroll' => 'App\Policies\BankrollPolicy',
        'App\Abstracts\Game' => 'App\Policies\GamePolicy',
        'App\Abstracts\GameTransaction' => 'App\Policies\GameTransactionPolicy',
        'App\Attributes\Stake' => 'App\Policies\StakePolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
    }
}
