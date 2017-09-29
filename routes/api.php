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
Route::middleware('auth:api')->get('user', function (Request $request) {
    return $request->user();
});

// Main API URL namespace
Route::prefix('v1.0')->group(function () {
    // Easy client registration.
    Route::get('access/claim', 'UserController@claimAccess');

    // All API methods accessible
    Route::group(['middleware' => 'auth:ph4-token'], function(){

        Route::get('user', function(Request $request){
            return $request->user();
        });

        // TODO: add certificate
        // TODO: add domain
        // TODO: get expiration

    });
});

Route::group(['prefix' => 'v1','middleware' => 'auth:api'], function () {
    //    Route::resource('task', 'TasksController');

    //Please do not remove this if you want adminlte:route and adminlte:link commands to works correctly.
    #adminlte_api_routes
});

