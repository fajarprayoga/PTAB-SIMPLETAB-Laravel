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

// Route::group(['prefix' => 'v1', 'as' => 'admin.', 'namespace' => 'Api\V1\Admin'], function () {
//     Route::apiResource('permissions', 'PermissionsApiController');

//     Route::apiResource('roles', 'RolesApiController');

//     Route::apiResource('users', 'UsersApiController');

//     Route::apiResource('products', 'ProductsApiController');
// });

Route::group(['prefix' => 'open', 'namespace' => 'Api\V1\Admin'], function () {
    Route::post('/login', 'CustomersApiController@login');
    Route::post('/loginagent', 'CustomersApiController@loginagent');
    Route::post('/register', 'CustomersApiController@register');
    Route::post('/register-agent', 'CustomersApiController@registerAgent');
    Route::get('/logout', 'CustomersApiController@logout')->middleware('auth:api');
    Route::post('/reset', 'CustomersApiController@resetUser');
    Route::post('/user-block', 'CustomersApiController@userBlock');
});

Route::group(['prefix' => 'close', 'namespace' => 'Api\V1\Admin', 'middleware' => 'auth:api'], function () {
    Route::post('students', 'MasterApiController@students');
    Route::get('student/{id}', 'MasterApiController@studentshow');
    Route::get('teachers', 'MasterApiController@teachers');
    Route::get('teacher/{id}', 'MasterApiController@teachershow');    
    Route::get('periods', 'MasterApiController@periods');
    Route::get('period/{id}', 'MasterApiController@periodshow');
    Route::get('semesters', 'MasterApiController@semesters');
    Route::get('semester/{id}', 'MasterApiController@semestershow');
    Route::get('grades', 'MasterApiController@grades');
    Route::get('grade/{id}', 'MasterApiController@gradeshow');   
    Route::get('teams', 'MasterApiController@teams');
    Route::get('team/{id}', 'MasterApiController@teamshow');
    Route::get('subjects', 'MasterApiController@subjects');
    Route::get('subject/{id}', 'MasterApiController@subjectshow');

    Route::post('schedules', 'LearningApiController@schedules');
    Route::get('schedule/{id}', 'LearningApiController@scheduleshow');
    Route::post('absents/list/{id}/{grade}', 'LearningApiController@absents');
    Route::get('absent/presence/{sid}/{reg}/{ssid}', 'LearningApiController@presence');
    Route::post('absent/presence-process', 'LearningApiController@presenceProcess');
    Route::get('absent/bill/{sid}/{reg}/{ssid}', 'LearningApiController@bill');
    Route::post('absent/bill-process', 'LearningApiController@billProcess');
    Route::post('bills', 'LearningApiController@bills');
    Route::get('bills/paid/{id}/{period}', 'LearningApiController@paid');
    Route::post('bills/paid-process', 'LearningApiController@paidProcess');
});
