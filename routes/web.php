<?php

//Route::get('member/register', 'MembersController@register');
// Route::resource('member', 'MembersController');

Route::redirect('/', '/login');

Route::redirect('/home', '/admin');

Auth::routes(['register' => false]);

Route::group(['prefix' => 'admin', 'as' => 'admin.', 'namespace' => 'Admin', 'middleware' => ['auth']], function () {
    Route::get('/', 'HomeController@index')->name('home');

    Route::delete('permissions/destroy', 'PermissionsController@massDestroy')->name('permissions.massDestroy');

    Route::resource('permissions', 'PermissionsController');

    Route::delete('roles/destroy', 'RolesController@massDestroy')->name('roles.massDestroy');

    Route::resource('roles', 'RolesController');

    Route::delete('users/destroy', 'UsersController@massDestroy')->name('users.massDestroy');

    Route::resource('users', 'UsersController');

    


    // keluhan pelanggan
    Route::resource('customers', 'CustomersController');
    
    Route::delete('customers/destroy', 'CustomersController@massDestroy')->name('customers.massDestroy');

    Route::resource('categories', 'CategoriesController');

    Route::delete('categories/destroy', 'CategoriesController@massDestroy')->name('categories.massDestroy');

    Route::resource('dapertements', 'DapertementsController');

    Route::delete('dapertements/destroy', 'DapertementsController@massDestroy')->name('dapertements.massDestroy');

    Route::resource('staffs', 'StaffsController');

    Route::delete('staffs/destroy', 'StaffsController@massDestroy')->name('staffs.massDestroy');

    // tickets

    Route::resource('tickets', 'TicketsController');

    Route::get('tickets/print/{ticket_id}', 'TicketsController@print')->name('tickets.print');
    
    Route::get('tickets/print-action/{ticket_id}', 'TicketsController@printAction')->name('tickets.printAction');

    Route::delete('tickets/destroy', 'TicketsController@massDestroy')->name('tickets.massDestroy');


    // action & action staff
    // start surya buat
    Route::get('actions/printservice','ActionsController@printservice')->name('actions.printservice');

    Route::get('actions/printspk','ActionsController@printspk')->name('actions.printspk');

    Route::get('actions/printreport','ActionsController@printreport')->name('actions.printreport');
    // end surya buat

    Route::resource('actions', 'ActionsController', ['only' => ['index', 'store', 'edit', 'update', 'destroy']]);

    Route::get('actions/create/{ticket_id}', 'ActionsController@create')->name('actions.create');

    Route::post('actions/staff', 'ActionsController@staff')->name('actions.staff');

    Route::get('actions/list/{action}', 'ActionsController@list')->name('actions.list');

    Route::delete('actions/destroy', 'ActionsController@massDestroy')->name('actions.massDestroy');

    Route::get('actions/staff/{action}', 'ActionsController@actionStaff')->name('actions.actionStaff');

    Route::get('actions/staff/create/{action}', 'ActionsController@actionStaffCreate')->name('actions.actionStaffCreate');

    Route::post('actions/staff/store/', 'ActionsController@actionStaffStore')->name('actions.actionStaffStore');

    Route::get('actions/staff/{action}/edit/{staff}', 'ActionsController@actionStaffEdit')->name('actions.actionStaffEdit');

    Route::put('actions/staff/update', 'ActionsController@actionStaffUpdate')->name('actions.actionStaffUpdate');

    Route::put('actions/staff/update', 'ActionsController@actionStaffUpdate')->name('actions.actionStaffUpdate');

    Route::delete('users/staff/delete/{action}/{staff}', 'ActionsController@actionStaffDestroy')->name('actions.actionStaffDestroy');

    //customer request
    Route::resource('customerrequests', 'CustomerRequestController');

    //test
    Route::resource('test-customers', 'TestController');

    Route::resource('subdapertements', 'SubdapertementsController');

    Route::delete('subdapertements/destroy', 'SubdapertementsController@massDestroy')->name('subdapertements.massDestroy');

    Route::get('get-subdapertement', 'StaffsController@getSubdapertement')->name('staffs.subdepartment');
    
});
