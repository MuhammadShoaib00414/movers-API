<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MovingDetailsController;
use App\Http\Controllers\DeliveryDetailController;
use App\Http\Middleware\IsUser;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('sign_in', [UserController::class, 'login']);
Route::post('user_registration', [UserController::class, 'register']);
Route::middleware(['auth:sanctum', 'isUser'])->group(function () {
    Route::post('otp_verification', [UserController::class, 'verifyOtp']);
    Route::post('moving_details', [MovingDetailsController::class, 'storeMoveDetails'])->name('moving_details');
    
    Route::post('resend_otp', [UserController::class, 'resendOtp']);
    Route::post('create_password', [UserController::class, 'createPassword']);
    Route::post('forgot_password', [UserController::class, 'forgotPassword']);
    Route::post('edit_profile', [UserController::class, 'editProfile']);

    // Use controller method for 'moving-details' and 'delivery-details' routes
    Route::post('moving-details', [UserController::class, 'storeMoveDetails']);
    Route::post('delivery-details', [DeliveryDetailController::class, 'storeDeliveryDetails']);
});
Route::middleware(['auth:sanctum', 'isAdmin'])->group(function () {

});

