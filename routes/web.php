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


Route::post('/bankroll/add', 'BankrollController@add')->name('bankroll.add')->middleware('auth');
Route::post('/bankroll/withdraw', 'BankrollController@withdraw')->name('bankroll.withdraw')->middleware('auth');
Route::patch('/bankroll/{bankrollTransaction}/update', 'BankrollController@update')->name('bankroll.update')->middleware('auth');
Route::delete('/bankroll/{bankrollTransaction}/delete', 'BankrollController@delete')->name('bankroll.delete')->middleware('auth');

/*
    /{user}/bankroll/add
    /{user}/bankroll/withdraw

    /{bankroll}/update
    /{bankroll}/delete

    /{user}/cash/start
    /{user}/cash/current
    /{user}/tournament/start
    /{user}/tournament/current

    /cash/buyin/add
    /cash/buyin/update
    /cash/buyin/delete











*/
Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
