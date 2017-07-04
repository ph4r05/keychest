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

Route::get('/', 'SearchController@show')->name('/');
Route::post('/', 'SearchController@search');
Route::post('/intro', 'SearchController@search')->name('intro');
Route::post('feedback', 'SearchController@voteFeedback')->name('feedback');
Route::post('rfeedback', 'SearchController@restSubmitFeedback')->name('rfeedback');

Route::get('scan', 'SearchController@show');
Route::post('scan', 'SearchController@search');

Route::get('submitJob', 'SearchController@restSubmitJob');
Route::get('jobState', 'SearchController@restGetJobState');
Route::get('jobResult', 'SearchController@restJobResults');

// Auth routes
Route::auth();
Auth::routes();

// Terms
Route::get('terms', function () {
    return view('terms');
});

Route::get('privacy-policy', function () {
    return view('policy');
});

// Stories
Route::get('content', function () {
    return view('stories');
});
Route::get('content/keychest_spot_check', function () {
    return view('stories.keychest_spot_check');
});
Route::get('content/letsencrypt_numbers_to_know', function () {
    return view('stories.letsencrypt_numbers_to_know');
});
Route::get('content/understand_spot_checks', function () {
    return view('stories.understand_spot_checks');
});

// Redirects
Route::get('content/keychest_spot_check.html', function(){
    return Redirect::to('content/keychest_spot_check', 301);
});
Route::get('content/letsencrypt_numbers_to_know.html', function(){
    return Redirect::to('content/letsencrypt_numbers_to_know', 301);
});
Route::get('content/understand_spot_checks.html', function(){
    return Redirect::to('content/understand_spot_checks', 301);
});

// Registered user space
Route::get('home', 'HomeController@index')->name('home');
Route::get('home/servers', 'ServersController@index')->name('servers');
Route::get('home/servers/get', 'ServersController@getList')->name('servers/get');
Route::post('home/servers/add', 'ServersController@add')->name('servers/add');
Route::post('home/servers/del', 'ServersController@del')->name('servers/del');
Route::post('home/servers/update', 'ServersController@update')->name('servers/update');
Route::post('home/servers/canAdd', 'ServersController@canAddHost')->name('servers/canAdd');
Route::get('home/scan', 'SearchController@showHome')->name('home/scan')->middleware('auth');

Route::get('home/dashboard/data', 'DashboardController@loadActiveCerts')
    ->name('dashboard/data')->middleware('auth');

Route::get('home/user-guide', function () {
    return view('userguide');
})->name('user-guide');

//Please do not remove this if you want adminlte:route and adminlte:link commands to works correctly.
#adminlte_routes

