<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Bankroll Routes
Route::post('/bankroll/add', 'BankrollController@add')->name('bankroll.add')->middleware('auth');
Route::post('/bankroll/withdraw', 'BankrollController@withdraw')->name('bankroll.withdraw')->middleware('auth');
Route::get('/bankroll/{bankrollTransaction}', 'BankrollController@view')->name('bankroll.view')->middleware('auth');
Route::patch('/bankroll/{bankrollTransaction}/update', 'BankrollController@update')->name('bankroll.update')->middleware('auth');
Route::delete('/bankroll/{bankrollTransaction}/delete', 'BankrollController@delete')->name('bankroll.delete')->middleware('auth');


// CashGame Routes
Route::post('/cash/start', 'CashGameController@start')->name('cash.start')->middleware('auth');
Route::get('/cash/current', 'CashGameController@current')->name('cash.current')->middleware('auth');
Route::post('/cash/end', 'CashGameController@end')->name('cash.end')->middleware('auth');  
Route::get('/cash', 'CashGameController@index')->name('cash.index')->middleware('auth');
Route::get('/cash/{cash_game}', 'CashGameController@view')->name('cash.view')->middleware('auth');
Route::patch('/cash/{cash_game}', 'CashGameController@update')->name('cash.update')->middleware('auth');
Route::delete('/cash/{cash_game}', 'CashGameController@destroy')->name('cash.delete')->middleware('auth');
// Potentially POST /cash to @create.

// Tournament Routes
Route::post('/tournament/start', 'TournamentController@start')->name('tournament.start')->middleware('auth');
Route::get('/tournament/current', 'TournamentController@current')->name('tournament.current')->middleware('auth');
Route::post('/tournament/end', 'TournamentController@end')->name('tournament.end')->middleware('auth');
Route::get('/tournament', 'TournamentController@index')->name('tournament.index')->middleware('auth');
Route::get('/tournament/{tournament}', 'TournamentController@view')->name('tournament.view')->middleware('auth');
Route::patch('/tournament/{tournament}', 'TournamentController@update')->name('tournament.update')->middleware('auth');
Route::delete('/tournament/{tournament}', 'TournamentController@destroy')->name('tournament.delete')->middleware('auth');
// Potentially POST /tournament to @create.

// BuyIn Routes
Route::post('/buyin/add', 'BuyInController@add')->name('buyin.add')->middleware('auth');
Route::get('/buyin/{buy_in}', 'BuyInController@view')->name('buyin.view')->middleware('auth');
Route::patch('/buyin/{buy_in}', 'BuyInController@update')->name('buyin.update')->middleware('auth');
Route::delete('/buyin/{buy_in}', 'BuyInController@destroy')->name('buyin.delete')->middleware('auth');

// Expenses Routes
Route::post('/expense/add', 'ExpenseController@add')->name('expense.add')->middleware('auth');
Route::get('/expense/{expense}', 'ExpenseController@view')->name('expense.view')->middleware('auth');
Route::patch('/expense/{expense}', 'ExpenseController@update')->name('expense.update')->middleware('auth');
Route::delete('/expense/{expense}', 'ExpenseController@destroy')->name('expense.delete')->middleware('auth');

// CashOut Routes
Route::post('/cashout/add', 'CashOutController@add')->name('cashout.add')->middleware('auth');
Route::get('/cashout/{cash_out}', 'CashOutController@view')->name('cashout.view')->middleware('auth');
Route::patch('/cashout/{cash_out}', 'CashOutController@update')->name('cashout.update')->middleware('auth');
Route::delete('/cashout/{cash_out}', 'CashOutController@destroy')->name('cashout.delete')->middleware('auth');

// Rebuy Routes
Route::post('/rebuy/add', 'RebuyController@add')->name('rebuy.add')->middleware('auth');
Route::get('/rebuy/{rebuy}', 'RebuyController@view')->name('rebuy.view')->middleware('auth');
Route::patch('/rebuy/{rebuy}', 'RebuyController@update')->name('rebuy.update')->middleware('auth');
Route::delete('/rebuy/{rebuy}', 'RebuyController@destroy')->name('rebuy.delete')->middleware('auth');

// AddOn Routes
Route::post('/addon/add', 'AddOnController@add')->name('addon.add')->middleware('auth');
Route::get('/addon/{add_on}', 'AddOnController@view')->name('addon.view')->middleware('auth');
Route::patch('/addon/{add_on}', 'AddOnController@update')->name('addon.update')->middleware('auth');
Route::delete('/addon/{add_on}', 'AddOnController@destroy')->name('addon.delete')->middleware('auth');

/*

    /tournament/start
    /tournament/current
    /tournament/{tournament}/end


*/
Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
