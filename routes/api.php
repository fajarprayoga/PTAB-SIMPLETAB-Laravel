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

    Route::post('ctm/prev', 'CtmApiController@ctmPrev');

    Route::get('ctm/list/{id}', 'CtmApiController@ctmList');

    Route::get('ctm/pay/{id}', 'CtmApiController@ctmPay');

    Route::get('ctm/customer/{id}', 'CtmApiController@ctmCustomer');    

    Route::post('ctm/request', 'CtmApiController@ctmRequest');

    Route::get('ctm/use/{id}', 'CtmApiController@ctmUse');

});

Route::group(['prefix' => 'open/customer', 'namespace' => 'Api\V1\Customer'], function () {
    Route::post('login', 'CustomersApiController@login');
    Route::post('register/public', 'CustomersApiController@register_public');
    
    Route::post('OTP', 'CustomersApiController@smsApi');

    Route::get('logout',  'CustomersApiController@logout');

    Route::post('code', 'CustomersApiController@scanBarcode');

    Route::post('customerrequests', 'CustomersApiController@requestCustomer');

    Route::post('reset', 'CustomersApiController@reset');

    //test
    // Route::get('test', 'CustomersApiController@test');
    Route::get('test', 'TicketsApiController@test');
});

Route::group(['prefix' => 'close/admin', 'namespace' => 'Api\V1\Admin','middleware' => 'auth:apiadmin'], function () {

    // custmomer
    Route::resource('customers','CustomersApiController' );
    Route::post('customer/list','CustomersApiController@customers' );

    // categori
    Route::resource('categories', 'CategoriesApiController');
    Route::get('categories/list/{page}', 'CategoriesApiController@categories');
    Route::post('category-groups/list', 'CategoriesApiController@categoryGroups');
    Route::post('category-types/list', 'CategoriesApiController@categoryTypes');

    // dapettement
    Route::resource('dapertements', 'DapertementsApiController');
    Route::get('dapertements/list/{page}', 'DapertementsApiController@dapertements');

    // staffs
    Route::resource('staffs', 'StaffsApiController');
    Route::post('staffs/list', 'StaffsApiController@staffs');
    // ticket
    Route::resource('tickets', 'TicketsApiController');
    Route::post('ticket/list','TicketsApiController@tickets' );

    // action
    Route::resource('actions', 'ActionsApiController');
    Route::post('actionlists', 'ActionsApiController@list');
    Route::get('actionStaffs/{action_id}', 'ActionsApiController@actionStaffs');
    Route::get('actionStaffLists/{action_id}', 'ActionsApiController@actionStaffLists');
    Route::post('actionStaffStore', 'ActionsApiController@actionStaffStore');
    Route::put('actionStaffUpdate', 'ActionsApiController@actionStaffUpdate');
    Route::delete('actionStaffDestroy/{action}/{staff}', 'ActionsApiController@actionStaffDestroy');
    Route::post('actionStatusUpdate', 'ActionsApiController@actionStatusUpdate');

    // sub dapettement
    Route::resource('subdapertements', 'SubdapertementsApiController');
    Route::post('subdapertements/list', 'SubdapertementsApiController@subdapertements');   
    
    Route::get('defcustomer','CustomersApiController@defcustomer' );

    //SR aktif pasif
    Route::get('customer-sr', 'ActionsApiController@getSr');
    Route::get('customer-srnew', 'ActionsApiController@getSrnew');
    Route::post('ctm-mapping', 'ActionsApiController@getCtmmapping');
    Route::get('ctm-operator', 'ActionsApiController@getCtmoperator');
    Route::get('ctm-arealgroup', 'ActionsApiController@getCtmarealgroup');
    Route::post('ctm-kubikasi', 'ActionsApiController@getCtmkubikasi');
    Route::post('ctm-statussm', 'ActionsApiController@getCtmStatussm');
    Route::post('ctm-hasilbaca', 'ActionsApiController@getCtmHasilbaca');
    Route::post('audited', 'ActionsApiController@getAudited');

    //Segel
    Route::post('segel/list','ActionsApiController@segellist');
    Route::post('segel/store','ActionsApiController@lockStore');

    //Lock
    Route::post('lock/list','ActionsApiController@locklist');
    Route::delete('lockdestroy/{lockaction_id}', 'ActionsApiController@lockDestroy');
    Route::get('lockshow/{lock_id}', 'ActionsApiController@lockShow');
    Route::get('lockcreate/{lock_id}', 'ActionsApiController@scb');
    Route::post('SubDapertementlist', 'ActionsApiController@SubDapertementlist');
    Route::get('alarm-locks', 'ActionsApiController@alarmLocks');
    
     //LockStaff
    Route::get('lockStaffs/{lockaction_id}', 'ActionsApiController@lockStaffs');
    Route::get('lockStaffList/{lockaction_id}', 'ActionsApiController@lockStaffList');
    Route::post('lockStaffStore', 'ActionsApiController@lockStaffStore');
    Route::delete('lockStaffDestroy/{action}/{staff}', 'ActionsApiController@lockStaffDestroy');
    //LockAction
    Route::get('typeshow/{lockaction_id}', 'ActionsApiController@typeshow');
    Route::post('actionlocklists', 'ActionsApiController@actionlocklist');
    Route::post('lockactionscreate', 'ActionsApiController@lockactionscreate');
    Route::delete('lockactionsdestroy/{lockaction_id}', 'ActionsApiController@lockactionsDestroy');

    // payment
    // Route::resource('payments', 'PaymentApiController');
    Route::put('payments/edit', 'PaymentApiController@updatePay');
    
});


Route::group(['prefix' => 'open/admin', 'namespace' => 'Api\V1\Admin'], function () {
    Route::post('login',  'AdminApiController@login' );
    //test
    Route::get('test/{id}', 'TicketsApiController@test');
    Route::post('login-js',  'AdminApiController@loginJs' );

    Route::post('login-api',  'AdminApiController@loginApi' );
});

Route::group(['prefix' => 'close/dapertement', 'namespace' => 'Api\V1\Dapertement'], function () {
    Route::get('actions/list/{dapertement_id}', 'ActionsApiController@list');
    Route::get('actions/listStaff/{ticket_id}', 'ActionsApiController@liststaff');
    Route::put('actions/edit', 'ActionsApiController@edit');
    Route::get('actions/ticket/{ticket_id}', 'ActionsApiController@ticket');
    Route::post('actionStaffUpdate', 'SubDapertementsApiController@edit');
    Route::get('actions/list/subdapertement/{action_id}}', 'SubDapertementsApiController@actionListSubDapertement');
});