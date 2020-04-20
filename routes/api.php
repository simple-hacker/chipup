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
        Route::get('/', 'BankrollController@index')->name('index');
        Route::post('create', 'BankrollController@create')->name('create');
        Route::get('{bankrollTransaction}', 'BankrollController@view')->name('view');
        Route::patch('{bankrollTransaction}/update', 'BankrollController@update')->name('update');
        Route::delete('{bankrollTransaction}/delete', 'BankrollController@delete')->name('delete');
    });
    
    // CashGame Routes
    Route::prefix('cash')->name('cash.')->group(function () {
        Route::post('start', 'CashGameController@start')->name('start');
        Route::get('current', 'CashGameController@current')->name('current');
        Route::post('end', 'CashGameController@end')->name('end');  
        Route::get('', 'CashGameController@index')->name('index');
        Route::get('{cash_game}', 'CashGameController@view')->name('view');
        Route::patch('{cash_game}', 'CashGameController@update')->name('update');
        Route::delete('{cash_game}', 'CashGameController@destroy')->name('delete');
        // Potentially POST /cash to @create.
    });
    
    // Tournament Routes
    Route::prefix('tournament')->name('tournament.')->group(function () {
        Route::post('start', 'TournamentController@start')->name('start');
        Route::get('current', 'TournamentController@current')->name('current');
        Route::post('end', 'TournamentController@end')->name('end');
        Route::get('', 'TournamentController@index')->name('index');
        Route::get('{tournament}', 'TournamentController@view')->name('view');
        Route::patch('{tournament}', 'TournamentController@update')->name('update');
        Route::delete('{tournament}', 'TournamentController@destroy')->name('delete');
        // Potentially POST /tournament to @create.
    });
    
    // BuyIn Routes
    Route::prefix('buyin')->name('buyin.')->group(function () {
        Route::post('add', 'BuyInController@add')->name('add');
        Route::get('{buy_in}', 'BuyInController@view')->name('view');
        Route::patch('{buy_in}', 'BuyInController@update')->name('update');
        Route::delete('{buy_in}', 'BuyInController@destroy')->name('delete');
    });
    
    // Expenses Routes
    Route::prefix('expense')->name('expense.')->group(function () {
        Route::post('add', 'ExpenseController@add')->name('add');
        Route::get('{expense}', 'ExpenseController@view')->name('view');
        Route::patch('{expense}', 'ExpenseController@update')->name('update');
        Route::delete('{expense}', 'ExpenseController@destroy')->name('delete');
    });
    
    // CashOut Routes
    Route::prefix('cashout')->name('cashout.')->group(function () {
        Route::post('add', 'CashOutController@add')->name('add');
        Route::get('{cash_out}', 'CashOutController@view')->name('view');
        Route::patch('{cash_out}', 'CashOutController@update')->name('update');
        Route::delete('{cash_out}', 'CashOutController@destroy')->name('delete');
    });
    
    // Rebuy Routes
    Route::prefix('rebuy')->name('rebuy.')->group(function () {
        Route::post('add', 'RebuyController@add')->name('add');
        Route::get('{rebuy}', 'RebuyController@view')->name('view');
        Route::patch('{rebuy}', 'RebuyController@update')->name('update');
        Route::delete('{rebuy}', 'RebuyController@destroy')->name('delete');
    });
    
    // AddOn Routes
    Route::prefix('addon')->name('addon.')->group(function () {
        Route::post('add', 'AddOnController@add')->name('add');
        Route::get('{add_on}', 'AddOnController@view')->name('view');
        Route::patch('{add_on}', 'AddOnController@update')->name('update');
        Route::delete('{add_on}', 'AddOnController@destroy')->name('delete');
    });
});