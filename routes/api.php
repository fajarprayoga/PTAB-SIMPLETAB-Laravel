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

Route::group(['prefix' => 'close/customer', 'namespace' => 'Api\V1\Customer', 'middleware' => 'auth:apicustomer'], function () {

    Route::post('ticket/store', 'TicketsApiController@store');

    Route::get('categories', 'CategoriesApiController@index');

    Route::get('tickets/{id}', 'TicketsApiController@index');

});

Route::group(['prefix' => 'open/customer', 'namespace' => 'Api\V1\Customer'], function () {
    Route::post('login', 'CustomersApiController@login');
    Route::post('register/public', 'CustomersApiController@register_public');
    
    Route::post('OTP', 'CustomersApiController@smsApi');

    Route::get('logout',  'CustomersApiController@logout');

    Route::post('code', 'CustomersApiController@scanBarcode');
});

Route::group(['prefix' => 'close/admin', 'namespace' => 'Api\V1\Admin'], function () {
    // Route::get('customers',  'CustomersApiController@index' );
    Route::resource('customers','CustomersApiController' );
    Route::resource('categories', 'CategoriesApiController');
});


Route::group(['prefix' => 'open/admin', 'namespace' => 'Api\V1\Admin'], function () {
    Route::post('login',  'AdminApiController@login' );
});