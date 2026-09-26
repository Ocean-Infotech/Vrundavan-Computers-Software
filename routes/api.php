<?php

use App\Http\Controllers\api\ApiStudentController;
use App\Http\Controllers\api\AuthenticatedController;
use App\Http\Controllers\api\AuthenticationController;
use App\Http\Controllers\api\CommonController;
use App\Http\Controllers\api\SliderController;
use App\Http\Controllers\api\StudentRequestController;
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
    Route::any('get-test-subjects', 'get_test_subjects')->name('get-test-subjects');
    Route::any('get-admission', 'get_admission')->name('get-admission');
    Route::any('get-all-admission', 'get_all_admission')->name('get-all-admission');
    Route::get('get-books', 'get_books')->name('get-books');
    Route::any('get-class', 'get_class')->name('get-class');
    Route::any('get-shift', 'get_shift')->name('get-shift');
    Route::any('get-class-bybatch', 'get_classbybatch')->name('get-class-bybatch');
    Route::any('get-courceregistration', 'get_courceregistration')->name('get-courceregistration');
    Route::any('get-department', 'get_department')->name('get_department');
    Route::get('get-university', 'get_university')->name('get_university');
    Route::any('get-fees-collection-total', 'get_fees_collection_total')->name('get-fees-collection-total');

});

Route::post("student/send-otp", [AuthenticationController::class, 'studentSendOtp']);
Route::post("student/verify-otp", [AuthenticationController::class, 'studentVerifyOtp']);
Route::post("student/logout", [AuthenticationController::class, 'studentLogout'])
    ->middleware('auth:student-api');
Route::post("student/store-device-token", [AuthenticationController::class, 'storeDeviceToken']);
Route::match(['get', 'post'], 'student/fee-receipt/{id}/download', [ApiStudentController::class, 'download_fee_receipt'])->name('student.fee-receipt.download');



Route::group(['middleware' => ['auth:student-api']], function () {

    /** All Student API store in this prefix */
    Route::prefix('student')->group(function () {
        Route::post('profile-detail', [AuthenticatedController::class, 'student_profile_detail']);
        Route::post('update-profile', [AuthenticatedController::class, 'student_update_profile']);
        Route::post('academic-details', [ApiStudentController::class, 'academic_details']);
        Route::match(['get', 'post'], 'timetable-details', [ApiStudentController::class, 'timetable_details']);
        Route::post('fee-details', [ApiStudentController::class, 'fee_details']);
        Route::post('document-details', [ApiStudentController::class, 'document_details']);
        Route::post('contact-details', [ApiStudentController::class, 'contact_details']);
        Route::post('attendance-details', [ApiStudentController::class, 'attendance_details']);
        Route::post('test-subjects', [ApiStudentController::class, 'test_subjects']);
        Route::post('test-details', [ApiStudentController::class, 'test_details']);
        Route::match(['get', 'post'], 'test-schedule', [ApiStudentController::class, 'test_schedule']);
        Route::post('assignment-details', [ApiStudentController::class, 'assignment_details'])->name('student.assignment-details');
        Route::post('notifications-list', [ApiStudentController::class, 'notifications_list']);
        Route::post('notifications-mark-read', [ApiStudentController::class, 'notifications_mark_read']);
        Route::match(['get', 'post'], 'faculty-complaint-reports', [ApiStudentController::class, 'faculty_complaint_reports']);
        Route::match(['get', 'post'], 'marksheet-details', [ApiStudentController::class, 'marksheet_details']);
        Route::match(['get', 'post'], 'unread-counts', [ApiStudentController::class, 'unread_counts']);
        Route::match(['get', 'post'], 'assessment-details', [ApiStudentController::class, 'assessment_details']);
        Route::match(['get', 'post'], 'achievement-details', [ApiStudentController::class, 'achievement_details']);
        Route::match(['get', 'post'], 'holiday-list', [ApiStudentController::class, 'holiday_list']);
    });

    Route::match(['get', 'post'], 'get-faculty-complaint-reports', [ApiStudentController::class, 'faculty_complaint_reports']);


    Route::controller(SliderController::class)->group(function () {
        Route::post('slider-add', [SliderController::class, 'slider_add']);
        Route::post('slider-list', [SliderController::class, 'slider_list']);
        Route::post('slider-update', [SliderController::class, 'slider_update']);
        Route::delete('slider-delete', [SliderController::class, 'slider_destroy']);
    });

    Route::controller(StudentRequestController::class)->group(function () {
        Route::post('request-form', [StudentRequestController::class, 'request_form']);
        Route::post('request-list', [StudentRequestController::class, 'request_list']);
        Route::post('request-subject-list', [StudentRequestController::class, 'request_subject_list']);
    });


    Route::controller(UserController::class)->group(function () {

        // Route::post('get-profile', 'getProfile');
    });

    Route::controller(CommonController::class)->group(function () {

        Route::post('documents', 'documents');
    });
});
