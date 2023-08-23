<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->group(function () {

  
});


Route::post('user_registration', 'UserController@register');
Route::post('otp_verification', 'UserController@verifyOtp');
Route::post('resend_otp', 'UserController@resendOtp');
Route::post('create_password', 'UserController@createPassword');
Route::post('sign_in', 'UserController@signIn');

Route::post('/forgot_password', 'UserController@forgotPassword');


Route::post('/edit_profile', 'UserController@editProfile');

