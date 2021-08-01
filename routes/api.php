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

    Route::get('ctm/prev', 'CtmApiController@ctmPrev');

});

Route::group(['prefix' => 'open/customer', 'namespace' => 'Api\V1\Customer'], function () {
    Route::post('login', 'CustomersApiController@login');
    Route::post('register/public', 'CustomersApiController@register_public');
    
    Route::post('OTP', 'CustomersApiController@smsApi');

    Route::get('logout',  'CustomersApiController@logout');

    Route::post('code', 'CustomersApiController@scanBarcode');
});

Route::group(['prefix' => 'close/admin', 'namespace' => 'Api\V1\Admin','middleware' => 'auth:apiadmin'], function () {
    // Route::get('customers',  'CustomersApiController@index' );
    Route::resource('customers','CustomersApiController' );
    Route::resource('categories', 'CategoriesApiController');
    Route::resource('dapertements', 'DapertementsApiController');
    Route::resource('staffs', 'StaffsApiController');
    Route::resource('tickets', 'TicketsApiController');
    Route::resource('actions', 'ActionsApiController');
    Route::get('actionlists/{ticket_id}', 'ActionsApiController@list');
    Route::get('actionStaffs/{action_id}', 'ActionsApiController@actionStaffs');
    Route::get('actionStaffLists/{action_id}', 'ActionsApiController@actionStaffLists');
    Route::post('actionStaffStore', 'ActionsApiController@actionStaffStore');
    Route::put('actionStaffUpdate', 'ActionsApiController@actionStaffUpdate');
    Route::delete('actionStaffDestroy/{action}/{staff}', 'ActionsApiController@actionStaffDestroy');
});


Route::group(['prefix' => 'open/admin', 'namespace' => 'Api\V1\Admin'], function () {
    Route::post('login',  'AdminApiController@login' );
});

Route::group(['prefix' => 'close/dapertement', 'namespace' => 'Api\V1\Dapertement'], function () {
    Route::get('actions/list/{dapertement_id}', 'ActionsApiController@list');
    Route::get('actions/listStaff/{ticket_id}', 'ActionsApiController@liststaff');
    Route::put('actions/edit', 'ActionsApiController@edit');
    Route::get('actions/ticket/{ticket_id}', 'ActionsApiController@ticket');
});