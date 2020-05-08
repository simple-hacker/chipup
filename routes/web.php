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


Route::get('/setup', 'SetupController@index')->name('setup.index')->middleware(['auth', 'setup.incomplete']);
Route::post('/setup', 'SetupController@complete')->name('setup.complete')->middleware(['auth', 'setup.incomplete']);

Auth::routes();

Route::get('login/{provider}', 'Auth\SocialLoginController@redirectToProvider');
Route::get('login/{provider}/callback','Auth\SocialLoginController@handleProviderCallback');

// TODO: Go through application and change route('dashboard') to 'dashboard'
// Keeping dashboard route for now because a lot of the application depends on the route name.  This was done before setting up SPA.
Route::get('/dashboard', 'SpaController@index')->name('dashboard')->middleware(['auth', 'setup.complete']);

// Redirect All other Routes to Vue-Router SPA.  All of these will use auth and setup.complete middleware
Route::get('/{any}', 'SpaController@index')->where('any', '.*')->middleware(['auth', 'setup.complete']);
