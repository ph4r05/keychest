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
Route::get('ping', 'SearchController@restPing')->name('ping');
Route::post('ping', 'SearchController@restPing')->name('pping');
Route::post('timezoneSet', 'SearchController@restTimezoneSet')->name('timezoneSet');
Route::post('feedback', 'SearchController@voteFeedback')->name('feedback');
Route::post('rfeedback', 'SearchController@restSubmitFeedback')->name('rfeedback');

Route::get('scan', 'SearchController@show');
Route::post('scan', 'SearchController@search');

Route::post('submitJob', 'SearchController@restSubmitJob');
Route::get('jobState', 'SearchController@restGetJobState');
Route::get('jobResult', 'SearchController@restJobResults');

// Account and API key confirmation / flow.
Route::get('unsubscribe/{token}', 'EmailController@unsubscribe');
Route::get('verifyEmail/{token}/{apiKeyToken?}', 'UserController@verifyEmail');
Route::get('emailVerified', 'UserController@emailVerified')->name('email.verified');
Route::post('setPassword', 'Auth\SetPasswordController@reset')->name('password.set');
Route::get('blockAccount/{token}', 'UserController@blockAccount');
Route::get('blockAutoApiKeys/{token}', 'UserController@blockAutoApiKeys');
Route::get('confirmApiKey/{token}', 'ApiController@confirmApiKey');
Route::get('revokeApiKey/{token}', 'ApiController@revokeApiKey');

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
Route::post('home/servers/addMore', 'ServersController@addMore')->name('servers/addMore');
Route::post('home/servers/del', 'ServersController@del')->name('servers/del');
Route::post('home/servers/delMore', 'ServersController@delMore')->name('servers/delMore');
Route::post('home/servers/update', 'ServersController@update')->name('servers/update');
Route::post('home/servers/canAdd', 'ServersController@canAddHost')->name('servers/canAdd');
Route::post('home/servers/import', 'ServersController@importServers')->name('servers/import');

if (config('keychest.enabled_ip_scanning')) {
    Route::get('home/networks', 'NetworksController@index')->name('networks');
    Route::get('home/networks/get', 'NetworksController@ipScanList')->name('networks/get');
    Route::post('home/networks/add', 'NetworksController@add')->name('networks/add');
    Route::post('home/networks/del', 'NetworksController@del')->name('networks/del');
    Route::post('home/networks/delMore', 'NetworksController@delMore')->name('networks/delMore');
    Route::post('home/networks/update', 'NetworksController@update')->name('networks/update');
}

Route::get('home/scan', 'SearchController@showHome')
    ->name('home/scan')
    ->middleware('auth');

Route::get('home/dashboard/data', 'DashboardController@loadActiveCerts')
    ->name('dashboard/data')
    ->middleware('auth');

Route::get('home/subs/get', 'SubdomainsController@getList')->name('subs/get');
Route::get('home/subs/getDomains', 'SubdomainsController@getDomains')->name('subs/getDomains');
Route::get('home/subs/getUnfinished', 'SubdomainsController@getUnfinishedDomains')->name('subs/getUnfinished');
Route::post('home/subs/add', 'SubdomainsController@add')->name('subs/add');
Route::post('home/subs/addMore', 'SubdomainsController@addMore')->name('subs/addMore');
Route::post('home/subs/del', 'SubdomainsController@del')->name('subs/del');
Route::post('home/subs/delMore', 'SubdomainsController@delMore')->name('subs/delMore');
Route::post('home/subs/update', 'SubdomainsController@update')->name('subs/update');
Route::post('home/subs/canAdd', 'SubdomainsController@canAdd')->name('subs/canAdd');
Route::post('home/subs/import', 'SubdomainsController@importDomains')->name('subs/import');
Route::get('home/subs/res', 'SubdomainsController@getDiscoveredSubdomainsList')->name('subs/res');
Route::get('home/subs/suffix', 'SubdomainsController@watchingDomainWithSuffix')->name('subs/suffix');

Route::get('home/license', 'LicenseController@index')->name('license');
Route::post('home/account/update', 'UserController@updateAccount')->name('update-account');
Route::post('home/account/close', 'UserController@closeAccount')->name('close-account');

Route::get('home/user-guide', function () {
    return view('userguide');
})->name('user-guide');

Route::get('home/enterprise', function () {
    return view('enterprise');
})->name('enterprise');


//Please do not remove this if you want adminlte:route and adminlte:link commands to works correctly.
#adminlte_routes

