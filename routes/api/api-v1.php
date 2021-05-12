<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

/**Defining Cors Parameters*/


header('Access-Control-Allow-Headers: Origin, app-token, session-token, Content-Type');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');

$Api_Version = 'v1';

Route::get('login-error', function () {
    return response()->json([
        'session_token' => null,
        'success' => false,
        'message' => 'login-error',
        'data' => [],
    ], 401);
});

Route::get('login-invalid', function () {
    return response()->json([
        'session_token' => null,
        'success' => false,
        'message' => 'login-invalid',
        'data' => [],
    ], 401);
});


Route::group(['middleware' => $Api_Version . '.app.auth.token'], function () use ($Api_Version) {


    /**Non Auth Routes*/

    Route::group(['prefix' => 'user'], function () use ($Api_Version) {

        Route::post('login', $Api_Version . '\UserManagementController@loginUser');

    });


    /**Auth Routes*/

    Route::group(['middleware' => $Api_Version . '.login.status'], function () use ($Api_Version) {

        Route::group(['prefix' => 'user'], function () use ($Api_Version) {

            Route::post('logout', $Api_Version . '\UserManagementController@logoutUser');

        });

        Route::group(['prefix' => 'employee'], function () use ($Api_Version) {

            Route::post('register', $Api_Version . '\employeeManagementController@saveEmployee');
            Route::post('update', $Api_Version . '\employeeManagementController@updateEmployee');

            Route::post('check-in', $Api_Version . '\employeeManagementController@checkIN');
            Route::post('check-out', $Api_Version . '\employeeManagementController@checkOUT');

            Route::get('all-attendance', $Api_Version . '\employeeManagementController@getAllAttendance');
            Route::get('search', $Api_Version . '\employeeManagementController@searchEmployee');

        });

    });

});
