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

// Unauthenticated API ping
Route::get('ping', function (Request $request) {
    return ['status' => 'success'];
});

// Current user auth check call
Route::middleware('auth:api')->get('user', 'ApiController@user');

// Main API URL namespace
Route::group(['prefix' => 'v1.0', 'middleware' => 'api.response'], function () {

    // Easy client registration.
    Route::get('access/claim', 'UserController@claimAccess');

    // All API methods accessible
    Route::group(['middleware' => 'auth:ph4-token'], function(){

        // Basic auth test
        Route::get('user', 'ApiController@user');

        // Add certificate to the monitoring
        Route::post('certificate/add', 'ApiController@addCertificate');

        // Add domain to the monitoring
        Route::post('servers/add', 'ApiController@addDomain');

        // Get domain cert expiration
        Route::get('servers/expiration', 'ApiController@domainCertExpiration');
    });
});

Route::group(['prefix' => 'v1','middleware' => 'auth:api'], function () {
    //    Route::resource('task', 'TasksController');

    //Please do not remove this if you want adminlte:route and adminlte:link commands to works correctly.
    #adminlte_api_routes
});

