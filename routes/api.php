<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::middleware(['auth:sanctum', 'setup.complete'])->group(function () {
    // Bankroll Routes
    Route::prefix('bankroll')->name('bankroll.')->group(function () {
        Route::get('', 'BankrollController@index')->name('index');
        Route::post('', 'BankrollController@create')->name('create');
        Route::get('{bankrollTransaction}', 'BankrollController@view')->name('view');
        Route::patch('{bankrollTransaction}', 'BankrollController@update')->name('update');
        Route::delete('{bankrollTransaction}', 'BankrollController@delete')->name('delete');
    });

    // Live Session Routes
    Route::prefix('live')->name('live.')->group(function () {
        // Route::post('start', 'LiveGameController@start')->name('start');
        Route::get('current', 'LiveGameController@current')->name('current');
        // Route::patch('update', 'LiveGameController@update')->name('update');
        Route::post('end', 'LiveGameController@end')->name('end'); 
    });
    
    // Cash Game Routes
    Route::prefix('cash')->name('cash.')->group(function () {

        Route::prefix('live')->name('live.')->group(function () {
            Route::post('start', 'LiveCashGameController@start')->name('start');
            // Route::get('current', 'LiveCashGameController@current')->name('current');
            Route::patch('update', 'LiveCashGameController@update')->name('update');
            // Route::post('end', 'LiveCashGameController@end')->name('end'); 
        });
 
        Route::get('', 'CashGameController@index')->name('index');
        Route::post('', 'CashGameController@create')->name('create');
        Route::get('{cash_game}', 'CashGameController@view')->name('view');
        Route::patch('{cash_game}', 'CashGameController@update')->name('update');
        Route::delete('{cash_game}', 'CashGameController@destroy')->name('delete');
    });
    
    // Tournament Routes
    Route::prefix('tournament')->name('tournament.')->group(function () {

        Route::prefix('live')->name('live.')->group(function () {
            Route::post('start', 'LiveTournamentController@start')->name('start');
            // Route::get('current', 'LiveTournamentController@current')->name('current');
            Route::patch('update', 'LiveTournamentController@update')->name('update');
            // Route::post('end', 'LiveTournamentController@end')->name('end'); 
        });

        Route::get('', 'TournamentController@index')->name('index');
        Route::post('', 'TournamentController@create')->name('create');
        Route::get('{tournament}', 'TournamentController@view')->name('view');
        Route::patch('{tournament}', 'TournamentController@update')->name('update');
        Route::delete('{tournament}', 'TournamentController@destroy')->name('delete');
    });
    
    // BuyIn Routes
    Route::prefix('buyin')->name('buyin.')->group(function () {
        Route::post('', 'BuyInController@create')->name('create');
        Route::get('{buy_in}', 'BuyInController@view')->name('view');
        Route::patch('{buy_in}', 'BuyInController@update')->name('update');
        Route::delete('{buy_in}', 'BuyInController@destroy')->name('delete');
    });
    
    // Expenses Routes
    Route::prefix('expense')->name('expense.')->group(function () {
        Route::post('', 'ExpenseController@create')->name('create');
        Route::get('{expense}', 'ExpenseController@view')->name('view');
        Route::patch('{expense}', 'ExpenseController@update')->name('update');
        Route::delete('{expense}', 'ExpenseController@destroy')->name('delete');
    });
    
    // CashOut Routes
    Route::prefix('cashout')->name('cashout.')->group(function () {
        Route::post('', 'CashOutController@create')->name('create');
        Route::get('{cash_out}', 'CashOutController@view')->name('view');
        Route::patch('{cash_out}', 'CashOutController@update')->name('update');
        Route::delete('{cash_out}', 'CashOutController@destroy')->name('delete');
    });
    
    // Rebuy Routes
    Route::prefix('rebuy')->name('rebuy.')->group(function () {
        Route::post('', 'RebuyController@create')->name('create');
        Route::get('{rebuy}', 'RebuyController@view')->name('view');
        Route::patch('{rebuy}', 'RebuyController@update')->name('update');
        Route::delete('{rebuy}', 'RebuyController@destroy')->name('delete');
    });
    
    // AddOn Routes
    Route::prefix('addon')->name('addon.')->group(function () {
        Route::post('', 'AddOnController@create')->name('create');
        Route::get('{add_on}', 'AddOnController@view')->name('view');
        Route::patch('{add_on}', 'AddOnController@update')->name('update');
        Route::delete('{add_on}', 'AddOnController@destroy')->name('delete');
    });
});

Route::middleware(['auth:sanctum'])->group(function () {
    // Stake Routes
    Route::prefix('stake')->name('stake.')->group(function () {
        Route::post('', 'StakeController@create')->name('create');
        Route::get('{stake}', 'StakeController@view')->name('view');
        Route::patch('{stake}', 'StakeController@update')->name('update');
        Route::delete('{stake}', 'StakeController@destroy')->name('delete');
    });
});