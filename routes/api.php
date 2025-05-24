<?php

use App\Http\Controllers\api\AuthenticationController;
use App\Http\Controllers\api\CommonController;
use App\Http\Controllers\api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::any('test', function () {
    return "API Test";
});

Route::controller(CommonController::class)->group(function () {
    Route::post('user_type', 'user_type');
    Route::any('get-country', 'get_country');
    Route::any('get-state', 'get_state')->name('get-state');
    Route::any('get-cities', 'get_cities')->name('get-cities');
    Route::any('check-pincode', 'check_pincode')->name('check-pincode');

    Route::any('get-course', 'get_course')->name('get-course');
    Route::any('get-batch', 'get_batch')->name('get-batch');
    Route::any('get-admission', 'get_admission')->name('get-admission');
    Route::any('get-class', 'get_class')->name('get-class');
});

Route::controller(AuthenticationController::class)->group(function () {
    Route::post('register', 'register');
    Route::post('login', 'login');
    // Route::post('profile', 'profile');
});

/** */
Route::group(['middleware' => ['auth:api']], function () {
    Route::controller(UserController::class)->group(function () {
        Route::post('get-profile', 'getProfile');
        Route::post('update-profile', 'updateProfile');
        Route::post('withdraw-request', 'withdrawRequest');
        Route::post('point-check-withdraw-request-amount', 'PointCheckWithdrawRequestAmount');
        Route::post('withdraw-history', 'withdrawHistory');
    });

    Route::controller(CommonController::class)->group(function () {
        Route::post('sliders', 'sliders');
        Route::post('verify-qr-scan', 'verifyQrScan');
        Route::post('scan-history', 'scanHistory');
        Route::post('my-points', 'myPoints');
        Route::post('documents', 'documents');
    });
});
