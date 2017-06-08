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
Route::post('feedback', 'SearchController@voteFeedback')->name('feedback');
Route::post('rfeedback', 'SearchController@restSubmitFeedback')->name('rfeedback');

Route::get('scan', 'SearchController@show');
Route::post('scan', 'SearchController@search');

Route::get('submitJob', 'SearchController@restSubmitJob');
Route::get('jobState', 'SearchController@restGetJobState');
Route::get('jobResult', 'SearchController@restJobResults');

//Route::get('/w', function () {
//    return view('welcome');
//});

// Auth routes
Route::auth();
Auth::routes();

Route::get('home', 'HomeController@index')->name('home');
Route::get('home/servers', 'ServersController@index')->name('servers');
Route::get('home/servers/get', 'ServersController@getList')->name('servers/get');
Route::post('home/servers/add', 'ServersController@add')->name('servers/add');
Route::post('home/servers/del', 'ServersController@del')->name('servers/del');
Route::post('home/servers/update', 'ServersController@update')->name('servers/update');

//Please do not remove this if you want adminlte:route and adminlte:link commands to works correctly.
#adminlte_routes

