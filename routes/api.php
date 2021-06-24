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


// Route::group(['prefix' => 'open', 'namespace' => 'Api\V1\Admin'], function () {
//     Route::get('customers', 'CustomersController@index');
// });

// Route::group(['prefix' => 'close', 'namespace' => 'Api\V1\Admin', 'middleware' => 'auth:customersApi'], function () {
    
// });

Route::group(['prefix' => 'open/customer', 'namespace' => 'Api\V1\Customer'], function () {
    Route::post('login', 'CustomersApiController@login');
    
    Route::post('register/public', 'CustomersApiController@register_public');
});