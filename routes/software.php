<?php
// Route::get('/', [DashboardController::class, 'dashboard'])->name('dashboard');

use App\Http\Controllers\admin\ApplicationUserController;
use App\Http\Controllers\admin\DashboardController;
use App\Http\Controllers\admin\DocumentController;
use App\Http\Controllers\admin\DocumentTypeController;
use App\Http\Controllers\admin\FeesCollectionController;
use App\Http\Controllers\admin\master\MasterCityController;
use App\Http\Controllers\admin\master\MasterCountryController;
use App\Http\Controllers\admin\master\MasterPincodeController;
use App\Http\Controllers\admin\master\MasterStateController;

use App\Http\Controllers\admin\spatie\PermissionController;
use App\Http\Controllers\admin\spatie\RoleController;
use App\Http\Controllers\admin\spatie\UserController;

use App\Http\Controllers\admin\AdmissionController;
use App\Http\Controllers\admin\CourceRegistrationController;
use App\Http\Controllers\admin\master\MasterCourseController;
use App\Http\Controllers\admin\master\MasterBatchController;
use App\Http\Controllers\admin\TestController;
use App\Http\Controllers\admin\AttedanceController;
use App\Http\Controllers\admin\Master\MasterSemesterController;

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

/** Master Semester Modules */
Route::resource('/semester', MasterSemesterController::class);
Route::match(['get', 'post'], 'semester/restore/{id}', [MasterSemesterController::class, 'restore'])->name('semester.restore');
Route::match(['get', 'post'], 'semester-update-status', [MasterSemesterController::class, 'status_update'])->name('semester.status-update');

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
Route::match(['get', 'post'], 'attedance-filter-student-data', [AttedanceController::class, 'filter_student_data'])->name('attedance.filter-student-data');
Route::match(['get', 'post'], 'attedance-student-in', [AttedanceController::class, 'student_in'])->name('attedance.student-in');

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

/** document-type */
Route::resource('document-type', DocumentTypeController::class);

/** document-type */
Route::resource('documents', DocumentController::class);

// Cource Registration

  Route::resource('cource-registration', CourceRegistrationController::class);
  Route::match(['get', 'post'], 'cource-registration/restore/{id}', [CourceRegistrationController::class, 'restore'])->name('cource-registration.restore');
  Route::match(['get', 'post'], 'cource-registration-update-status', [CourceRegistrationController::class, 'update_status'])->name('cource-registration.status-update');

// Fees Collection

  Route::resource('fees-collection', FeesCollectionController::class);
  Route::match(['get', 'post'], 'fees-collection/restore/{id}', [FeesCollectionController::class, 'restore'])->name('fees-collection.restore');
  Route::match(['get', 'post'], 'fees-collection-update-status', [FeesCollectionController::class, 'update_status'])->name('fees-collection.status-update');


