<?php

use App\Http\Controllers\api\ApiAdminController;
use App\Http\Controllers\api\SliderController;
use Illuminate\Support\Facades\Route;

/*
| Admin mobile app API. Student routes stay in routes/api.php.
*/

Route::prefix('admin')->group(function () {
    Route::post('login', [ApiAdminController::class, 'login']);

    Route::middleware('auth:api')->group(function () {
        Route::post('logout', [ApiAdminController::class, 'logout']);
        Route::post('profile', [ApiAdminController::class, 'profile']);
        Route::post('dashboard', [ApiAdminController::class, 'dashboard']);
        Route::match(['get', 'post'], 'staff-list', [ApiAdminController::class, 'staffList']);
        Route::match(['get', 'post'], 'supplier-list', [ApiAdminController::class, 'supplierList']);
        Route::match(['get', 'post'], 'supplier-contact', [ApiAdminController::class, 'supplierList']);
        Route::match(['get', 'post'], 'supplier-contacts', [ApiAdminController::class, 'supplierList']);
        // Student Management (App Menu: Student / Total Students Screen)
        Route::match(['get', 'post'], 'student', [ApiAdminController::class, 'studentList']);
        Route::match(['get', 'post'], 'student-list', [ApiAdminController::class, 'studentList']);
        Route::match(['get', 'post'], 'student-detail/{id?}', [ApiAdminController::class, 'studentDetail']);
        Route::match(['get', 'post'], 'student/{id}', [ApiAdminController::class, 'studentDetail'])->where('id', '[0-9]+');
        Route::match(['get', 'post'], 'student-birthdays', [ApiAdminController::class, 'studentBirthdays']);
        Route::match(['get', 'post'], 'filter-options', [ApiAdminController::class, 'filterOptions']);

        // Attendance Management (Screen: Today Attendance / Total Attendance)
        Route::match(['get', 'post'], 'today-attendance', [ApiAdminController::class, 'todayAttendance']);
        Route::match(['get', 'post'], 'total-attendance', [ApiAdminController::class, 'todayAttendance']);
        Route::match(['get', 'post'], 'attendance', [ApiAdminController::class, 'todayAttendance']);
        Route::match(['get', 'post'], 'class-attendance', [ApiAdminController::class, 'classAttendanceDetail']);
        Route::match(['get', 'post'], 'class-attendance/{class_id}', [ApiAdminController::class, 'classAttendanceDetail']);
        Route::match(['get', 'post'], 'course-batch-attendance', [ApiAdminController::class, 'classAttendanceDetail']);
        Route::post('mark-attendance', [ApiAdminController::class, 'markAttendance']);

        // Slider APIs for Admin
        Route::post('slider-add', [SliderController::class, 'slider_add']);
        Route::post('slider-list', [SliderController::class, 'slider_list']);
        Route::post('slider-update', [SliderController::class, 'slider_update']);
        Route::match(['delete', 'post'], 'slider-delete/{id?}', [SliderController::class, 'slider_destroy']);
    });
});
