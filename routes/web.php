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

Route::get('/dashboard', 'DashboardController@index')->name('dashboard')->middleware(['auth', 'setup.complete']);

Route::get('/setup', 'SetupController@index')->name('setup.index')->middleware(['auth', 'setup.incomplete']);
Route::post('/setup', 'SetupController@complete')->name('setup.complete')->middleware(['auth', 'setup.incomplete']);

Auth::routes();