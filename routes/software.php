<?php
// Route::get('/', [DashboardController::class, 'dashboard'])->name('dashboard');

use App\Http\Controllers\admin\ApplicationUserController;
use App\Http\Controllers\admin\DashboardController;
use App\Http\Controllers\admin\DocumentController;
use App\Http\Controllers\admin\DocumentTypeController;
use App\Http\Controllers\admin\master\MasterCityController;
use App\Http\Controllers\admin\master\MasterCountryController;
use App\Http\Controllers\admin\master\MasterPincodeController;
use App\Http\Controllers\admin\master\MasterStateController;
use App\Http\Controllers\admin\MasterRedemptionTypeController;
use App\Http\Controllers\admin\RedemptionController;
use App\Http\Controllers\admin\ReportController;
use App\Http\Controllers\admin\SettingController;
use App\Http\Controllers\admin\SliderController;
use App\Http\Controllers\admin\spatie\PermissionController;
use App\Http\Controllers\admin\spatie\RoleController;
use App\Http\Controllers\admin\spatie\UserController;
use App\Http\Controllers\admin\WithdrawRequestController;
use App\Http\Controllers\admin\AdmissionController;
use App\Http\Controllers\admin\master\MasterCourseController;
use App\Http\Controllers\admin\master\MasterBatchController;
use App\Http\Controllers\admin\TestController;
use App\Http\Controllers\admin\AttedanceController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;


Route::get('/', [DashboardController::class, 'dashboard'])->name('software.dashboard');
Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
Route::get('/removeimage/{id}', [DashboardController::class, 'remove_image'])->name('remove_image');

/** User Modules */
Route::resource('users', UserController::class);
route::post("search-permission", [UserController::class, 'searchPermission'])->name('search-permission');

/** Roles Modules */
Route::resource('roles', RoleController::class);

/** Permission Modules */
Route::resource('permissions', PermissionController::class);


/** Master Admission Modules */
Route::resource('/admission', AdmissionController::class);
Route::match(['get', 'post'], 'admission/restore/{id}', [AdmissionController::class, 'restore'])->name('admission.restore');
Route::match(['get', 'post'], 'admission-update-status', [AdmissionController::class, 'status_update'])->name('admission.status-update');
Route::match(['get', 'post'], 'admission-education-detail-delete', [AdmissionController::class, 'education_detail_delete'])->name('admission.education-detail-delete');

/** Master Course Modules */
Route::resource('/course', MasterCourseController::class);
Route::match(['get', 'post'], 'course/restore/{id}', [MasterCourseController::class, 'restore'])->name('course.restore');
Route::match(['get', 'post'], 'course-update-status', [MasterCourseController::class, 'status_update'])->name('course.status-update');

/** Master Batch Modules */
Route::resource('/batch', MasterBatchController::class);
Route::match(['get', 'post'], 'batch/restore/{id}', [MasterBatchController::class, 'restore'])->name('batch.restore');
Route::match(['get', 'post'], 'batch-update-status', [MasterBatchController::class, 'status_update'])->name('batch.status-update');

/** Test Modules */
Route::resource('/test', TestController::class);
Route::match(['get', 'post'], 'test/restore/{id}', [TestController::class, 'restore'])->name('test.restore');
Route::match(['get', 'post'], 'test-update-status', [TestController::class, 'status_update'])->name('test.status-update');

/** Attedance Modules */
Route::resource('/attedance', AttedanceController::class);
Route::match(['get', 'post'], 'attedance/restore/{id}', [AttedanceController::class, 'restore'])->name('attedance.restore');
Route::match(['get', 'post'], 'attedance-update-status', [AttedanceController::class, 'status_update'])->name('attedance.status-update');
Route::match(['get', 'post'], 'filter-student-data', [AttedanceController::class, 'filter_student_data'])->name('attedance.filter-student-data');

/** Master Copuntry Modules */
Route::resource('/country', MasterCountryController::class);
Route::GET('/country-sync', [MasterCountryController::class, 'country_sync'])->name('country.sync');

/** Master State Modules */
Route::resource('/state', MasterStateController::class);
// Route::GET('/state-sync', [MasterStateController::class, 'state_sync'])->name('state.sync');
// Route::GET('/getStateByCountryId', [MasterStateController::class, 'getStateByCountryId'])->name('getStateByCountryId');

/** Master City Modules */
Route::resource('/city', MasterCityController::class);
// Route::GET('/city-sync', [MasterCityController::class, 'city_sync'])->name('city.sync');
Route::GET('/auto_complete_city', [MasterCityController::class, 'auto_complete_city'])->name('auto_complete_city');

/** Master Pincode Modules */
Route::resource('/pincode', MasterPincodeController::class);

/** Sliders */
Route::resource('sliders', SliderController::class);
// Route::get('sliders-status/{id}/{update_status}', [SliderController::class, 'update_slider_status'])->name('sliders.update.status');

/** Application User */
Route::resource('application-user', ApplicationUserController::class);
Route::match(['get', 'post'], 'application-user-status', [ApplicationUserController::class, 'redemptionItemStatuUpdate'])->name('application-user-status');

/** Redemption */
Route::resource('redemption', RedemptionController::class);
Route::match(['get', 'post'], 'redemption-item-status', [RedemptionController::class, 'redemptionItemStatuUpdate'])->name('redemption-item-status');

/** Withdraw Request */
Route::resource('withdraw-request', WithdrawRequestController::class);

/** document-type */
Route::resource('document-type', DocumentTypeController::class);

/** document-type */
Route::resource('documents', DocumentController::class);

/** Reports */
Route::group(['prefix' => 'report'], function () {
    Route::controller(ReportController::class)->group(function () {
        Route::match(['get', 'post'], "scannedReport", 'scannedReport')->name("scanned-report");
    });
});

/** Settings */
Route::match(['get', 'post'], 'set-point-to-rupees', [SettingController::class, 'setPointToRupees'])->name('set-point-to-rupees');

Route::match(['get', 'post'], 'set-min-withdraw-request-amount', [SettingController::class, 'SetMinWithdrawRequestAmount'])->name('set-min-withdraw-request-amount');

/** Settiung */
Route::get('/generate-swagger-api', function () {
    Artisan::call('optimize:clear');
    Artisan::call("l5-swagger:generate");
    return Redirect::back()->with('success', 'Generate API documentation.');
})->name('generate-swagger-api');
