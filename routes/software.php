<?php
// Route::get('/', [DashboardController::class, 'dashboard'])->name('dashboard');

use App\Http\Controllers\admin\ApplicationUserController;
use App\Http\Controllers\admin\DashboardController;
use App\Http\Controllers\admin\DocumentController;
use App\Http\Controllers\admin\FeeHistoryController;
use App\Http\Controllers\admin\master\IssueBookController;
use App\Http\Controllers\admin\master\LibraryMasterBookController;
use App\Http\Controllers\admin\master\MasterFeeDetailController;
use App\Http\Controllers\admin\master\MasterSubjectController;
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
use App\Http\Controllers\admin\SyllabusController;
use App\Http\Controllers\admin\TestController;
use App\Http\Controllers\admin\AttedanceController;
use App\Http\Controllers\admin\BioMaxController;
use App\Http\Controllers\admin\FeescollectionpendingController;
use App\Http\Controllers\admin\GoogleDriveController;
use App\Http\Controllers\admin\master\MasterClassController;
use App\Http\Controllers\admin\master\MasterDepartmentController;
use App\Http\Controllers\admin\master\MasterSemesterController;
use App\Http\Controllers\admin\master\MasterShiftController;
use App\Http\Controllers\admin\StudentsFeesReportController;
use App\Http\Controllers\admin\SystemUserController;
use App\Http\Controllers\admin\TestreportController;
use App\Http\Controllers\admin\SliderController;
use App\Http\Controllers\NotificationSettingController;
use App\Http\Controllers\PushController;
use App\Http\Controllers\TimetableController;
use App\Http\Controllers\StudentRequestController;
use Illuminate\Support\Facades\Storage;
use Google\Client;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'dashboard'])->name('software.dashboard');
Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard.showUsers');

Route::get('/removeimage/{id}', [DashboardController::class, 'remove_image'])->name('remove_image');

/** User Modules */
Route::resource('users', UserController::class);
route::post("search-permission", [UserController::class, 'searchPermission'])->name('search-permission');

/** Roles Modules */
Route::resource('roles', RoleController::class);

/** Permission Modules */
Route::resource('permissions', PermissionController::class);


/** Master Admission Modules */

// Route::resource('/admission', AdmissionController::class);
// Route::match(['get', 'post'], 'admission/restore/{id}', [AdmissionController::class, 'restore'])->name('admission.restore');
// Route::match(['get', 'post'], 'admission-update-status', [AdmissionController::class, 'status_update'])->name('admission.status-update');
// Route::match(['get', 'post'], 'admission-education-detail-delete', [AdmissionController::class, 'education_detail_delete'])->name('admission.education-detail-delete');
// Route::match(['get', 'post'], 'admission-create-google-drive-folder', [AdmissionController::class, 'createFolder'])->name('admission.create-google-drive-folder');
// Route::match(['get', 'post'], 'admission-google-drive-rename-folder', [AdmissionController::class, 'renameFolder'])->name('admission.google-drive-rename-folder');
// Route::match(['get', 'post'], 'admission-aadhar-card-no-suggestion', [AdmissionController::class, 'aadharCardNoSuggestion'])->name('admission.aadhar-card-no-suggestion');
// Route::match(['get', 'post'], 'admission-aadhar-card-no-to-data', [AdmissionController::class, 'aadharCardNoStudentData'])->name('admission.aadhar-card-no-to-data');
// Route::get('/aadhar-card-no-to-data', [AdmissionController::class, 'aadharCardNoToData'])->name('admission.aadhar-card-no-to-data');
// routes/web.php or routes/api.php

// Resource routes for Admission
// Route::resource('admission', AdmissionController::class);

// Slider Route
Route::resource('slider', SliderController::class);
Route::match(['get', 'post'], 'slider/restore/{id}', [SliderController::class, 'restore'])->name('slider.restore');
Route::match(['get', 'post'], 'slider-update-status', [SliderController::class, 'update_status'])->name('slider.status-update');


Route::resource('admission', AdmissionController::class)->except(['show']);

// Additional routes

Route::post('admission/restore/{id}', [AdmissionController::class, 'restore'])->name('admission.restore');
// Route::post('admission-update-status', [AdmissionController::class, 'status_update'])->name('admission.status-update');
Route::post('admission/check-aadhar-duplicate', [AdmissionController::class, 'checkAadharDuplicate'])->name('admission.check-aadhar-duplicate');
Route::post('admission/check-mobile-duplicate', [AdmissionController::class, 'checkMobileDuplicate'])->name('admission.check-mobile-duplicate');

Route::post('admission/update-course-status', [AdmissionController::class, 'updateCourseStatus'])->name('admission.update-course-status');
Route::get('admission/view/{register_id}/{course_id}', [AdmissionController::class, 'view'])->name('admission.view');
Route::post('admission-education-detail-delete', [AdmissionController::class, 'education_detail_delete'])->name('admission.education-detail-delete');
Route::post('admission-create-google-drive-folder', [AdmissionController::class, 'createFolder'])->name('admission.create-google-drive-folder');
Route::post('admission-google-drive-rename-folder', [AdmissionController::class, 'renameFolder'])->name('admission.google-drive-rename-folder');
// routes/web.php
Route::post('admission/create-folder', [AdmissionController::class, 'createFolder'])
    ->name('admission.create-folder');


// AJAX routes for Aadhaar
Route::get('admission-aadhar-card-no-suggestion', [AdmissionController::class, 'aadharCardNoSuggestion'])->name('admission.aadhar-card-no-suggestion');
Route::get('admission-aadhar-card-no-to-data', [AdmissionController::class, 'aadharCardNoToData'])->name('admission.aadhar-card-no-to-data');

// Route::get('/admission/details/{id}', [AdmissionController::class, 'getAdmissionDetails'])->name('get-admission-details');


Route::get('get-admission-details/{id}', [AdmissionController::class, 'getAdmissionDetails'])->name('get-admission-details');
Route::get('admission/show', [AdmissionController::class, 'show'])->name('admission.show');
Route::get('admission-course', [AdmissionController::class, 'course'])->name('admission.course');

Route::match(['get', 'post'], 'course-fees-get', [AdmissionController::class, 'courseFeesGet'])->name('admission.course-fees-get');
Route::match(['get', 'post'], 'course-fees-update', [AdmissionController::class, 'courseFeesUpdate'])->name('admission.course-fees-update');

Route::get('admission/attendance/{id}', [AdmissionController::class, 'viewAttendance'])->name('admission.view-attendance');
Route::get('admission/test-marks/{id}', [AdmissionController::class, 'viewTestMarks'])->name('admission.view-test-marks');


/** Master Course Modules */
Route::resource('/course', MasterCourseController::class);
Route::match(['get', 'post'], 'course/restore/{id}', [MasterCourseController::class, 'restore'])->name('course.restore');
Route::match(['get', 'post'], 'course-update-status', [MasterCourseController::class, 'status_update'])->name('course.status-update');

/** Master Semester Modules */
Route::resource('/semester', MasterSemesterController::class);
Route::match(['get', 'post'], 'semester/restore/{id}', [MasterSemesterController::class, 'restore'])->name('semester.restore');
Route::match(['get', 'post'], 'semester-update-status', [MasterSemesterController::class, 'status_update'])->name('semester.status-update');

/** Master Shift Modules */
Route::resource('/shift', MasterShiftController::class);
Route::match(['get', 'post'], 'shift/restore/{id}', [MasterShiftController::class, 'restore'])->name('shift.restore');
Route::match(['get', 'post'], 'shift-update-status', [MasterShiftController::class, 'status_update'])->name('shift.status-update');

/** Master Batch Modules */
Route::resource('/batch', MasterBatchController::class);
Route::match(['get', 'post'], 'batch/restore/{id}', [MasterBatchController::class, 'restore'])->name('batch.restore');
Route::match(['get', 'post'], 'batch-update-status', [MasterBatchController::class, 'status_update'])->name('batch.status-update');

/** Master Department Modules */
Route::resource('/department', MasterDepartmentController::class);
Route::match(['get', 'post'], 'department/restore/{id}', [MasterDepartmentController::class, 'restore'])->name('department.restore');
Route::match(['get', 'post'], 'department-update-status', [MasterDepartmentController::class, 'status_update'])->name('department.status-update');

/** Library Book Master Modules */
Route::resource('/library-book-master', LibraryMasterBookController::class);
Route::match(['get', 'post'], 'library-book-master/restore/{id}', [LibraryMasterBookController::class, 'restore'])->name('library-book-master.restore');
Route::match(['get', 'post'], 'library-book-master/status-update', [LibraryMasterBookController::class, 'status_update'])->name('library-book-master.status-update');


/** Issue Book Modules */
Route::resource('/issue-book', IssueBookController::class);
Route::match(['get', 'post'], 'issue-book/restore/{id}', [IssueBookController::class, 'restore'])->name('issue-book.restore');
Route::match(['get', 'post'], 'issue-book/status-update', [IssueBookController::class, 'status_update'])->name('issue-book.status-update');


/** Attedance Modules */
Route::resource('/attedance', AttedanceController::class);
Route::match(['get', 'post'], 'attedance/restore/{id}', [AttedanceController::class, 'restore'])->name('attedance.restore');
Route::match(['get', 'post'], 'attedance-update-status', [AttedanceController::class, 'status_update'])->name('attedance.status-update');
Route::match(['get', 'post'], 'attedance-filter-student-data', [AttedanceController::class, 'filter_student_data'])->name('attedance.filter-student-data');
Route::match(['get', 'post'], 'attedance-student-in', [AttedanceController::class, 'student_in'])->name('attedance.student-in');
Route::match(['get', 'post'], 'attedance-student-out', [AttedanceController::class, 'student_out'])->name('attedance.student-out');
Route::match(['get', 'post'], 'attedance-student-leave', [AttedanceController::class, 'student_leave'])->name('attedance.student-leave');
Route::post('/attedance/fetch-logs', [AttedanceController::class, 'fetchLogs'])->name('attedance.fetch-logs');


// Route::match(['get', 'post'], 'attedance-rollNoIn', [AttedanceController::class, 'rollNoIn'])->name('attedance.rollNoIn');

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

Route::resource('cource-registration', CourceRegistrationController::class)->except(['create', 'edit']);
Route::match(['get', 'post'], 'cource-registration/restore/{id}', [CourceRegistrationController::class, 'restore'])->name('cource-registration.restore');
Route::match(['get', 'post'], 'cource-registration-update-status', [CourceRegistrationController::class, 'update_status'])->name('cource-registration.status-update');
Route::match(['get', 'post'], 'cource-registration-get-register-cource', [CourceRegistrationController::class, 'get_register_cource'])->name('cource-registration.get-register-cource');
Route::get('/cource-registration/create/{admission_id?}', [CourceRegistrationController::class, 'create'])->name('cource-registration.create');
Route::get('/cource-registration/{register_id}/edit/', [CourceRegistrationController::class, 'edit'])->name('cource-registration.edit');




// Fees Collection

Route::resource('fees-collection', FeesCollectionController::class);
Route::get('fees-collection/create/{id?}', [FeesCollectionController::class, 'create'])
    ->name('fees-collection.create');
Route::match(['get', 'post'], 'fees-collection/restore/{id}', [FeesCollectionController::class, 'restore'])->name('fees-collection.restore');
Route::match(['get', 'post'], 'fees-collection-update-status', [FeesCollectionController::class, 'update_status'])->name('fees-collection.status-update');
Route::match(['get', 'post'], 'search-fees-collection-details', [FeesCollectionController::class, 'search_fees_collection_details'])->name('fees-collection.search-fees-collection-details');
Route::match(['get', 'post'], 'check-master-password', [FeesCollectionController::class, 'check_master_password'])->name('fees-collection.check-master-password');
Route::get('/fees-collection/get-details/{id}', [FeesCollectionController::class, 'getDetails'])->name('fees-collection.getDetails');
Route::get('/fees-collection/print/{id}', [FeesCollectionController::class, 'print'])->name('software.fees-collection.print');
Route::get('fees-collection/{register_id}/semester-report', [FeesCollectionController::class, 'semesterReport'])->name('fees-collection.semester-report');
// Fee History
Route::resource('fee-history', FeeHistoryController::class)
    ->only(['index']);

Route::post('fee-history/update-status', [FeeHistoryController::class, 'updateCheckedStatus'])
    ->name('fee-history.update-status');
// fees collection pending
Route::get('fees-collection-pending/export-excel', [FeescollectionpendingController::class, 'exportExcel'])->name('fees-collection-pending.export-excel');
Route::get('fees-collection-pending/print', [FeescollectionpendingController::class, 'printView'])->name('fees-collection-pending.print');
Route::resource('fees-collection-pending', FeescollectionpendingController::class);
Route::get('fees-collection-pending/view-fee/{id}', [FeescollectionpendingController::class, 'viewFee'])->name('fees-collection-pending.view-fee');



// Students Fees Report
// Route::post('students-fees-report/export-excel', [StudentsFeesReportController::class, 'exportExcel'])->name('students-fees-report.export-excel');
Route::post('/students-fees-report/export-excel', [StudentsFeesReportController::class, 'exportExcel'])->name('students-fees-report.export-excel');
Route::get('students-fees-report/print', [StudentsFeesReportController::class, 'printView'])->name('students-fees-report.print');
Route::resource('students-fees-report', StudentsFeesReportController::class);

//syllabus

Route::resource('syllabus', SyllabusController::class);
Route::match(['get', 'post'], 'syllabus/restore/{id}', [SyllabusController::class, 'restore'])->name('syllabus.restore');
Route::match(['get', 'post'], 'syllabus-update-status', [SyllabusController::class, 'update_status'])->name('syllabus.status-update');

Route::resource('/subject', MasterSubjectController::class);
Route::match(['get', 'post'], 'subject/restore/{id}', [MasterSubjectController::class, 'restore'])->name('subject.restore');
Route::match(['get', 'post'], 'subject-update-status', [MasterSubjectController::class, 'update_status'])->name('subject.status-update');

/** Master Class Modules */
Route::resource('/class', MasterClassController::class);
Route::match(['get', 'post'], 'class/restore/{id}', [MasterClassController::class, 'restore'])->name('class.restore');
Route::match(['get', 'post'], 'class-update-status', [MasterClassController::class, 'status_update'])->name('class.status-update');

//Fee Details
Route::resource('fees', MasterFeeDetailController::class);


/** Test Modules */
Route::resource('/test', TestController::class);
Route::match(['get', 'post'], 'test/restore/{id}', [TestController::class, 'restore'])->name('test.restore');
Route::match(['get', 'post'], 'test-update-status', [TestController::class, 'status_update'])->name('test.status-update');
Route::get('/get-total-students/{batchId}', [TestController::class, 'getTotalStudents']);
Route::get('/add-marks/{id}', [TestController::class, 'addMarks'])->name('test.add-marks');
Route::post('/save-marks', [TestController::class, 'saveMarks'])->name('test.save-marks');
Route::post('/test/save-marks-ajax', [TestController::class, 'saveMarksAjax'])->name('test.save-marks-ajax');

Route::resource('/test_report', TestreportController::class);
Route::match(['get', 'post'], 'test_report/restore/{id}', [TestreportController::class, 'restore'])->name('test_report.restore');
Route::match(['get', 'post'], 'test_report-update-status', [TestreportController::class, 'status_update'])->name('test_report.status-update');

Route::post('/marks/store', [TestreportController::class, 'store'])->name('marks.store');


// Route::match(['get', 'post'], 'biomax/status', [BioMaxController::class, 'biomax_status'])->name('biomax.status');
// Notification Setting
Route::resource('/notification_setting', NotificationSettingController::class);
Route::get('get-students-by-course/{course_id}', [NotificationSettingController::class, 'getStudentsByCourse'])
    ->name('get.students.by.course');

// Timetable
Route::resource('/timetable', TimetableController::class);

// Student request
Route::resource('/student-requests', StudentRequestController::class);




/** System Login User */
Route::resource('system-user', SystemUserController::class);
Route::match(['get', 'post'], 'system-user-status', [SystemUserController::class, 'redemptionItemStatuUpdate'])->name('system-user-status');


Route::post('/push-test', [PushController::class, 'sendStatic']);
