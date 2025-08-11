<?php

use App\Http\Controllers\api\ApiStudentController;
use App\Http\Controllers\api\AuthenticatedController;
use App\Http\Controllers\api\AuthenticationController;
use App\Http\Controllers\api\CommonController;
use App\Http\Controllers\Api\StudentRequestController;
use App\Http\Controllers\api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::any('test', function () {
    return "API Test";
});

Route::controller(CommonController::class)->group(function () {

    Route::any('get-country', 'get_country');
    Route::any('get-state', 'get_state')->name('get-state');
    Route::any('get-cities', 'get_cities')->name('get-cities');
    Route::any('check-pincode', 'check_pincode')->name('check-pincode');

    Route::any('get-course', 'get_course')->name('get-course');
    Route::any('get-semester', 'get_semester')->name('get-semester');
    Route::any('get-batch', 'get_batch')->name('get-batch');
    Route::any('get-admission', 'get_admission')->name('get-admission');
    Route::any('get-class', 'get_class')->name('get-class');
    Route::any('get-shift', 'get_shift')->name('get-shift');
    Route::any('get-class-bybatch', 'get_classbybatch')->name('get-class-bybatch');
    Route::any('get-courceregistration', 'get_courceregistration')->name('get-courceregistration');
});

Route::post("student/send-otp", [AuthenticationController::class, 'studentSendOtp']);
Route::post("student/verify-otp", [AuthenticationController::class, 'studentVerifyOtp']);


Route::group(['middleware' => ['auth:student-api']], function () {

    /** All Student API store in this prefix */
    Route::prefix('student')->group(function () {
        Route::post('profile-detail', [AuthenticatedController::class, 'student_profile_detail']);
        Route::post('update-profile', [AuthenticatedController::class, 'student_update_profile']);
        Route::post('academic-details', [ApiStudentController::class, 'academic_details']);
        Route::post('fee-details', [ApiStudentController::class, 'fee_details']);
        Route::post('contact-details', [ApiStudentController::class, 'contact_details']);
        Route::post('attendance-details', [ApiStudentController::class, 'attendance_details']);
        // Route::post('request', [StudentRequestController::class, 'store']);
        // Route::post('request-subject-list', [StudentRequestController::class, 'subjects']);
    });

    Route::controller(StudentRequestController::class)->group(function () {
        Route::post('request-form', [StudentRequestController::class, 'request_form']);
        Route::post('request-subject-list', [StudentRequestController::class, 'request_subject_list']);
    });


    Route::controller(UserController::class)->group(function () {
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
