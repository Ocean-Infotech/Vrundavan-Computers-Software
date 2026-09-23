<?php
// Route::get('/', [DashboardController::class, 'dashboard'])->name('dashboard');

use App\Http\Controllers\admin\ApplicationUserController;
use App\Http\Controllers\admin\DashboardController;
use App\Http\Controllers\admin\DocumentController;
use App\Http\Controllers\admin\FeeHistoryController;
use App\Http\Controllers\admin\master\LibraryMasterBookController;
use App\Http\Controllers\admin\master\MasterFeeDetailController;
use App\Http\Controllers\admin\master\MasterSubjectController;
use App\Http\Controllers\admin\master\MasterEventController;
use App\Http\Controllers\admin\master\MasterHolidayController;
use App\Http\Controllers\admin\DocumentTypeController;
use App\Http\Controllers\admin\FeesCollectionController;
use App\Http\Controllers\admin\master\MasterCityController;
use App\Http\Controllers\admin\master\MasterCountryController;
use App\Http\Controllers\admin\master\MasterPincodeController;
use App\Http\Controllers\admin\master\MasterStateController;
use App\Http\Controllers\admin\master\MasterUniversityController;
use App\Http\Controllers\admin\spatie\PermissionController;
use App\Http\Controllers\admin\spatie\RoleController;
use App\Http\Controllers\admin\spatie\UserController;
use App\Http\Controllers\admin\StudentAchievementController;
use App\Http\Controllers\admin\AdmissionController;
use App\Http\Controllers\admin\CourceRegistrationController;
use App\Http\Controllers\admin\master\MasterCourseController;
use App\Http\Controllers\admin\master\MasterBatchController;
use App\Http\Controllers\admin\SyllabusController;
use App\Http\Controllers\admin\TestController;
use App\Http\Controllers\admin\AttedanceController;
use App\Http\Controllers\admin\FacultyAttedanceController;
use App\Http\Controllers\admin\DailyAttedanceController;
use App\Http\Controllers\admin\BioMaxController;
use App\Http\Controllers\admin\FeescollectionpendingController;
use App\Http\Controllers\admin\IssueCertificateController;
use App\Http\Controllers\admin\FacultyComplaintReportController;
use App\Http\Controllers\admin\SupplierController;
use App\Http\Controllers\admin\GoogleDriveController;
use App\Http\Controllers\admin\master\IssuedBookController;
use App\Http\Controllers\admin\master\MasterClassController;
use App\Http\Controllers\admin\master\MasterDepartmentController;
use App\Http\Controllers\admin\master\MasterSemesterController;
use App\Http\Controllers\admin\master\MasterShiftController;
use App\Http\Controllers\admin\StudentsFeesReportController;
use App\Http\Controllers\admin\SystemUserController;
use App\Http\Controllers\admin\MarksheetIssueController;
use App\Http\Controllers\admin\AssessmentController;
use App\Http\Controllers\admin\TestreportController;
use App\Http\Controllers\admin\ResultController;
use App\Http\Controllers\admin\SliderController;
use App\Http\Controllers\admin\OtherListSelectionController;
use App\Http\Controllers\admin\StudentReportController;
use App\Http\Controllers\admin\OtherListController;
use App\Http\Controllers\NotificationSettingController;
use App\Http\Controllers\admin\AssignmentController;
use App\Http\Controllers\admin\TaskController;
use App\Http\Controllers\admin\CombinedReportController;
use App\Http\Controllers\PushController;
use App\Http\Controllers\TimetableController;
use App\Http\Controllers\StudentRequestController;
use Illuminate\Support\Facades\Storage;
use Google\Client;
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
Route::delete(
    '/slider/permanent-delete/{id}',
    [SliderController::class, 'permanentDelete']
)->name('slider.permanent-delete');


Route::get('admission/export-excel', [AdmissionController::class, 'exportExcel'])->name('admission.export-excel');
Route::get('admission/print', [AdmissionController::class, 'printView'])->name('admission.print');
Route::get('marksheet-issue/get-students', [MarksheetIssueController::class, 'getStudents'])->name('marksheet-issue.get-students');
Route::get('marksheet-issue/export-excel', [MarksheetIssueController::class, 'exportExcel'])->name('marksheet-issue.export-excel');
Route::get('marksheet-issue/print', [MarksheetIssueController::class, 'printView'])->name('marksheet-issue.print');
Route::post('marksheet-issue/toggle', [MarksheetIssueController::class, 'toggle'])->name('marksheet-issue.toggle');
Route::resource('marksheet-issue', MarksheetIssueController::class);

Route::get('achievement/get-students', [StudentAchievementController::class, 'getStudents'])->name('achievement.get-students');
Route::get('achievement/student-data/{id}', [StudentAchievementController::class, 'studentData'])->name('achievement.student-data');
Route::get('achievement/event-data/{id}', [StudentAchievementController::class, 'eventData'])->name('achievement.event-data');
Route::resource('achievement', StudentAchievementController::class);
Route::get('assessment/export-excel', [AssessmentController::class, 'exportExcel'])->name('assessment.export-excel');
Route::get('assessment/print', [AssessmentController::class, 'printView'])->name('assessment.print');
Route::resource('assessment', AssessmentController::class);
Route::resource('admission', AdmissionController::class)->except(['show']);
// Result Entry
Route::get('result/entry', [ResultController::class, 'entryIndex'])->name('result.entry.index');
Route::get('result/entry/{course_id}/{batch_id}/{class_id}/{semester}', [ResultController::class, 'entryIndex'])->name('result.entry.direct');
Route::get('result/entry/students', [ResultController::class, 'entryGetStudents'])->name('result.entry.students');
Route::post('result/entry/store', [ResultController::class, 'entryStore'])->name('result.entry.store');

// Result Report
Route::get('result/report', [ResultController::class, 'reportIndex'])->name('result.report.index');
Route::get('result/report/data', [ResultController::class, 'reportGet'])->name('result.report.data');
Route::post('result/report/export-excel', [ResultController::class, 'exportExcel'])->name('result.report.export-excel');

// Result Master
Route::resource('result', ResultController::class)->except(['show']);

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
Route::get('admission/import', [AdmissionController::class, 'import'])->name('admission.import');
Route::post('admission/import-store', [AdmissionController::class, 'importStore'])->name('admission.import-store');




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
Route::get('bonafide-certificate/{id}', [AdmissionController::class, 'bonafideCertificate'])->name('bonafide-certificate');
Route::get('english-medium-certificate/{id}', [AdmissionController::class, 'englishMediumCertificate'])->name('english-medium-certificate');
Route::get('letter-of-recommendation/{id}', [AdmissionController::class, 'letterOfRecommendation'])->name('letter-of-recommendation');
Route::get('transfer-certificate/{id}', [AdmissionController::class, 'transferCertificate'])->name('transfer-certificate');


/** Master Course Modules */
Route::get('course/export-excel', [MasterCourseController::class, 'exportExcel'])->name('course.export-excel');
Route::get('course/print', [MasterCourseController::class, 'printView'])->name('course.print');
Route::resource('/course', MasterCourseController::class);
Route::match(['get', 'post'], 'course/restore/{id}', [MasterCourseController::class, 'restore'])->name('course.restore');
Route::match(['get', 'post'], 'course-update-status', [MasterCourseController::class, 'status_update'])->name('course.status-update');
Route::delete(
    '/course/permanent-delete/{id}',
    [MasterCourseController::class, 'permanent_delete']
)->name('course.permanent-delete');


/** Master Semester Modules */
Route::resource('/semester', MasterSemesterController::class);
Route::match(['get', 'post'], 'semester/restore/{id}', [MasterSemesterController::class, 'restore'])->name('semester.restore');
Route::match(['get', 'post'], 'semester-update-status', [MasterSemesterController::class, 'status_update'])->name('semester.status-update');

/** Master Shift Modules */
Route::get('shift/export-excel', [MasterShiftController::class, 'exportExcel'])->name('shift.export-excel');
Route::get('shift/print', [MasterShiftController::class, 'printView'])->name('shift.print');
Route::resource('/shift', MasterShiftController::class);
Route::match(['get', 'post'], 'shift/restore/{id}', [MasterShiftController::class, 'restore'])->name('shift.restore');
Route::match(['get', 'post'], 'shift-update-status', [MasterShiftController::class, 'status_update'])->name('shift.status-update');

/** Master Batch Modules */
Route::get('batch/export-excel', [MasterBatchController::class, 'exportExcel'])->name('batch.export-excel');
Route::get('batch/print', [MasterBatchController::class, 'printView'])->name('batch.print');
Route::resource('/batch', MasterBatchController::class);
Route::match(['get', 'post'], 'batch/restore/{id}', [MasterBatchController::class, 'restore'])->name('batch.restore');
Route::match(['get', 'post'], 'batch-update-status', [MasterBatchController::class, 'status_update'])->name('batch.status-update');
Route::delete(
    'batch/permanent-delete/{id}',
    [MasterBatchController::class, 'permanentDelete']
)->name('batch.permanent-delete');


/** Master Department Modules */
Route::get('department/export-excel', [MasterDepartmentController::class, 'exportExcel'])->name('department.export-excel');
Route::get('department/print', [MasterDepartmentController::class, 'printView'])->name('department.print');
Route::resource('/department', MasterDepartmentController::class);
Route::match(['get', 'post'], 'department/restore/{id}', [MasterDepartmentController::class, 'restore'])->name('department.restore');
Route::match(['get', 'post'], 'department-update-status', [MasterDepartmentController::class, 'status_update'])->name('department.status-update');
Route::delete('/department/permanent-delete/{id}', [MasterDepartmentController::class, 'permanentDelete'])->name('department.permanent-delete');


/** Master Department Modules */
Route::resource('/university', MasterUniversityController::class);
Route::match(['get', 'post'], 'university/restore/{id}', [MasterUniversityController::class, 'restore'])->name('university.restore');
Route::match(['get', 'post'], 'university-update-status', [MasterUniversityController::class, 'status_update'])->name('university.status-update');
Route::delete('/university/permanent-delete/{id}', [MasterUniversityController::class, 'permanentDelete'])->name('university.permanent-delete');


/** Library Book Master Modules */
Route::resource('/library-book-master', LibraryMasterBookController::class);
Route::match(['get', 'post'], 'library-book-master/restore/{id}', [LibraryMasterBookController::class, 'restore'])->name('library-book-master.restore');
Route::match(['get', 'post'], 'library-book-master/status-update', [LibraryMasterBookController::class, 'status_update'])->name('library-book-master.status-update');


/** Issue Book Modules */
Route::resource('/issued-book', IssuedBookController::class);
Route::match(['get', 'post'], 'issued-book/return/{id}', [IssuedBookController::class, 'returnBook'])->name('issued-book.return');

Route::match(['get', 'post'], 'issued-book/restore/{id}', [IssuedBookController::class, 'restore'])->name('issued-book.restore');
/** Attedance Modules */
Route::get('attedance/export-excel', [AttedanceController::class, 'exportExcel'])->name('attedance.export-excel');
Route::get('attedance/classwise-check-students', [AttedanceController::class, 'checkClasswiseStudents'])->name('attedance.classwise-check-students');
Route::get('attedance/classwise-export-excel', [AttedanceController::class, 'exportClasswiseMonthlyExcel'])->name('attedance.classwise-export-excel');
Route::get('attedance/print', [AttedanceController::class, 'printView'])->name('attedance.print');
Route::resource('/attedance', AttedanceController::class)->except(['show']);
Route::match(['get', 'post'], 'attedance/restore/{id}', [AttedanceController::class, 'restore'])->name('attedance.restore');
Route::match(['get', 'post'], 'attedance-update-status', [AttedanceController::class, 'status_update'])->name('attedance.status-update');
Route::match(['get', 'post'], 'attedance-filter-student-data', [AttedanceController::class, 'filter_student_data'])->name('attedance.filter-student-data');
Route::match(['get', 'post'], 'attedance-student-in', [AttedanceController::class, 'student_in'])->name('attedance.student-in');
Route::match(['get', 'post'], 'attedance-student-out', [AttedanceController::class, 'student_out'])->name('attedance.student-out');
Route::match(['get', 'post'], 'attedance-student-leave', [AttedanceController::class, 'student_leave'])->name('attedance.student-leave');
Route::post('/attedance/fetch-logs', [AttedanceController::class, 'fetchLogs'])->name('attedance.fetch-logs');
Route::get('/faculty-attendance/monthly-details', [FacultyAttedanceController::class, 'monthlyDetails'])->name('faculty-attendance.monthly-details');
Route::resource('/faculty-attendance', FacultyAttedanceController::class);

/** Daily Attendance Module (Student Only) */
Route::get('daily-attendance/student-detail', [DailyAttedanceController::class, 'studentDetail'])->name('daily-attendance.student-detail');
Route::resource('/daily-attendance', DailyAttedanceController::class);

/** Attedance Report Modules */
Route::get('attedance-report', [\App\Http\Controllers\admin\AttedanceReportController::class, 'index'])->name('attedance-report.index');
Route::get('attedance-report/export-excel', [\App\Http\Controllers\admin\AttedanceReportController::class, 'exportExcel'])->name('attedance-report.export-excel');
Route::get('attedance-report/print', [\App\Http\Controllers\admin\AttedanceReportController::class, 'printList'])->name('attedance-report.print');
Route::get('attedance-report/student-detail', [\App\Http\Controllers\admin\AttedanceReportController::class, 'studentDetail'])->name('attedance-report.student-detail');



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
    ->name('fees-collection.create_by_id');
Route::match(['get', 'post'], 'fees-collection/restore/{id}', [FeesCollectionController::class, 'restore'])->name('fees-collection.restore');
Route::match(['get', 'post'], 'fees-collection-update-status', [FeesCollectionController::class, 'update_status'])->name('fees-collection.status-update');
Route::match(['get', 'post'], 'search-fees-collection-details', [FeesCollectionController::class, 'search_fees_collection_details'])->name('fees-collection.search-fees-collection-details');
Route::match(['get', 'post'], 'check-master-password', [FeesCollectionController::class, 'check_master_password'])->name('fees-collection.check-master-password');
Route::get('/fees-collection/get-details/{id}', [FeesCollectionController::class, 'getDetails'])->name('fees-collection.getDetails');
Route::get('/fees-collection/print/{id}', [FeesCollectionController::class, 'print'])->name('software.fees-collection.print');
Route::get('fees-collection/{register_id}/semester-report', [FeesCollectionController::class, 'semesterReport'])->name('fees-collection.semester-report');

// Fees Pending Report (Menu 1)
Route::get('fees-pending-report', [\App\Http\Controllers\admin\FeesPendingReportController::class, 'index'])->name('fees-pending-report.index');
Route::get('fees-pending-report/print-list', [\App\Http\Controllers\admin\FeesPendingReportController::class, 'printList'])->name('fees-pending-report.print-list');
Route::get('fees-pending-report/export-excel', [\App\Http\Controllers\admin\FeesPendingReportController::class, 'exportExcel'])->name('fees-pending-report.export-excel');

// Fees Collection Report (Menu 2)
Route::get('fees-collection-report', [\App\Http\Controllers\admin\FeesCollectionReportController::class, 'index'])->name('fees-collection-report.index');
Route::get('fees-collection-report/print-list', [\App\Http\Controllers\admin\FeesCollectionReportController::class, 'printList'])->name('fees-collection-report.print-list');
Route::get('fees-collection-report/export-excel', [\App\Http\Controllers\admin\FeesCollectionReportController::class, 'exportExcel'])->name('fees-collection-report.export-excel');

// Other List
Route::get('other-list', [OtherListController::class, 'index'])->name('other-list.index');
Route::get('other-list/print-list', [OtherListController::class, 'printList'])->name('other-list.print-list');
Route::get('other-list/export-excel', [OtherListController::class, 'exportExcel'])->name('other-list.export-excel');

// Other List Selection
Route::get('other-list-selection', [OtherListSelectionController::class, 'index'])->name('other-list-selection.index');
Route::get('other-list-selection/student-options', [OtherListSelectionController::class, 'getStudentOptions'])->name('other-list-selection.student-options');
Route::get('other-list-selection/print-list', [OtherListSelectionController::class, 'printList'])->name('other-list-selection.print-list');
Route::get('other-list-selection/export-excel', [OtherListSelectionController::class, 'exportExcel'])->name('other-list-selection.export-excel');

// Issue Certificate
Route::get('issue-certificate/bonafide', [IssueCertificateController::class, 'bonafideIndex'])->name('issue-certificate.bonafide');
Route::get('issue-certificate/english-medium', [IssueCertificateController::class, 'englishMediumIndex'])->name('issue-certificate.english-medium');
Route::get('issue-certificate/letter-recommendation', [IssueCertificateController::class, 'letterRecommendationIndex'])->name('issue-certificate.letter-recommendation');
Route::get('issue-certificate/tc', [IssueCertificateController::class, 'tcIndex'])->name('issue-certificate.tc');
Route::post('issue-certificate/history', [IssueCertificateController::class, 'storeHistory'])->name('issue-certificate.store-history');
Route::get('faculty-complaint-report/export-excel', [FacultyComplaintReportController::class, 'exportExcel'])->name('faculty-complaint-report.export-excel');
Route::get('faculty-complaint-report/print', [FacultyComplaintReportController::class, 'printView'])->name('faculty-complaint-report.print');
Route::resource('faculty-complaint-report', FacultyComplaintReportController::class);
Route::match(['get', 'post'], 'faculty-complaint-report/restore/{id}', [FacultyComplaintReportController::class, 'restore'])->name('faculty-complaint-report.restore');
Route::delete('faculty-complaint-report/permanent-delete/{id}', [FacultyComplaintReportController::class, 'permanentDelete'])->name('faculty-complaint-report.permanent-delete');
Route::get('supplier/export-excel', [SupplierController::class, 'exportExcel'])->name('supplier.export-excel');
Route::get('supplier/print', [SupplierController::class, 'printView'])->name('supplier.print');
Route::resource('supplier', SupplierController::class);

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
Route::delete(
    '/syllabus/permanent-delete/{id}',
    [SyllabusController::class, 'permanentDelete']
)->name('syllabus.permanent-delete');



Route::resource('/subject', MasterSubjectController::class);
Route::match(['get', 'post'], 'subject/restore/{id}', [MasterSubjectController::class, 'restore'])->name('subject.restore');
Route::match(['get', 'post'], 'subject-update-status', [MasterSubjectController::class, 'update_status'])->name('subject.status-update');
Route::delete(
    '/subject/permanent-delete/{id}',
    [MasterSubjectController::class, 'permanentDelete']
)->name('subject.permanent-delete');

Route::resource('/event', MasterEventController::class);
Route::match(['get', 'post'], 'event/restore/{id}', [MasterEventController::class, 'restore'])->name('event.restore');
Route::match(['get', 'post'], 'event-update-status', [MasterEventController::class, 'update_status'])->name('event.status-update');
Route::delete(
    '/event/permanent-delete/{id}',
    [MasterEventController::class, 'permanentDelete']
)->name('event.permanent-delete');

Route::resource('/holiday', MasterHolidayController::class);
Route::match(['get', 'post'], 'holiday/restore/{id}', [MasterHolidayController::class, 'restore'])->name('holiday.restore');
Route::match(['get', 'post'], 'holiday-update-status', [MasterHolidayController::class, 'update_status'])->name('holiday.status-update');
Route::delete(
    '/holiday/permanent-delete/{id}',
    [MasterHolidayController::class, 'permanentDelete']
)->name('holiday.permanent-delete');


/** Master Class Modules */
Route::get('class/export-excel', [MasterClassController::class, 'exportExcel'])->name('class.export-excel');
Route::get('class/print', [MasterClassController::class, 'printView'])->name('class.print');
Route::resource('/class', MasterClassController::class);
Route::match(['get', 'post'], 'class/restore/{id}', [MasterClassController::class, 'restore'])->name('class.restore');
Route::match(['get', 'post'], 'class-update-status', [MasterClassController::class, 'status_update'])->name('class.status-update');
Route::delete(
    '/class/permanent-delete/{id}',
    [MasterClassController::class, 'permanent_delete']
)->name('class.permanent-delete');

//Fee Details
Route::resource('fees', MasterFeeDetailController::class);


/** Test Modules */
Route::get('test/export-excel', [TestController::class, 'exportExcel'])->name('test.export-excel');
Route::get('test/print', [TestController::class, 'printView'])->name('test.print');
Route::resource('/test', TestController::class);
Route::match(['get', 'post'], 'test/restore/{id}', [TestController::class, 'restore'])->name('test.restore');
Route::match(['get', 'post'], 'test-update-status', [TestController::class, 'status_update'])->name('test.status-update');
Route::get('/get-total-students/{batchId}', [TestController::class, 'getTotalStudents']);
Route::get('/add-marks/{id}', [TestController::class, 'addMarks'])->name('test.add-marks');
Route::get('/test/{id}/print-marks', [TestController::class, 'printMarks'])->name('test.print-marks');
Route::post('/save-marks', [TestController::class, 'saveMarks'])->name('test.save-marks');
Route::post('/test/save-marks-ajax', [TestController::class, 'saveMarksAjax'])->name('test.save-marks-ajax');
Route::delete(
    'test/permanent-delete/{id}',
    [TestController::class, 'permanentDelete']
)->name('test.permanent-delete');


Route::get('test_report/export-excel', [TestreportController::class, 'exportExcel'])->name('test_report.export-excel');
Route::get('test_report/print', [TestreportController::class, 'printView'])->name('test_report.print');
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
/** Timetable Routes */
Route::resource('timetable', TimetableController::class);

Route::match(
    ['get', 'post'],
    'timetable/restore/{id}',
    [TimetableController::class, 'restore']
)->name('timetable.restore');

Route::delete(
    'timetable/permanent-delete/{id}',
    [TimetableController::class, 'permanentDelete']
)->name('timetable.permanent-delete');

Route::get(
    'timetable/students/{id}',
    [TimetableController::class, 'getStudents']
)->name('timetable.students');



// Student request
Route::post('/student-requests/respond', [StudentRequestController::class, 'respond'])->name('student-requests.respond');
Route::resource('/student-requests', StudentRequestController::class);

/** Assignment Routes */
Route::get('assignment/report/{id}', [AssignmentController::class, 'report'])->name('assignment.report');
Route::post('assignment/save-report-ajax', [AssignmentController::class, 'saveReportAjax'])->name('assignment.save-report-ajax');
Route::resource('assignment', AssignmentController::class);
Route::post('assignment/change-status', [AssignmentController::class, 'changeStatus'])->name('assignment.change-status');

/** Task Routes */
Route::resource('task', TaskController::class);
Route::post('task/change-status', [TaskController::class, 'changeStatus'])->name('task.change-status');
Route::post('task/save-reply', [TaskController::class, 'saveReply'])->name('task.save-reply');

/** Combined Report Routes */
Route::get('combined-report/export-excel', [CombinedReportController::class, 'exportExcel'])->name('combined-report.export-excel');
Route::post('combined-report/get-data', [CombinedReportController::class, 'getDataAjax'])->name('combined-report.get-data');
Route::resource('combined-report', CombinedReportController::class);




/** System Login User */
Route::resource('system-user', SystemUserController::class);
Route::match(['get', 'post'], 'system-user-status', [SystemUserController::class, 'redemptionItemStatuUpdate'])->name('system-user-status');


Route::post('/push-test', [PushController::class, 'sendStatic']);

// Device Shield Management
Route::get('/device-shield', [App\Http\Controllers\DeviceBindingController::class, 'dashboard'])->name('device-shield.dashboard');
Route::get('/device-shield/devices', [App\Http\Controllers\DeviceBindingController::class, 'index'])->name('device-shield.index');
Route::post('/device-shield/devices/{id}/approve', [App\Http\Controllers\DeviceBindingController::class, 'approve'])->name('device-shield.approve');
Route::post('/device-shield/devices/{id}/reject', [App\Http\Controllers\DeviceBindingController::class, 'reject'])->name('device-shield.reject');
Route::post('/device-shield/devices/{id}/suspend', [App\Http\Controllers\DeviceBindingController::class, 'suspend'])->name('device-shield.suspend');
Route::post('/device-shield/devices/{id}/reactivate', [App\Http\Controllers\DeviceBindingController::class, 'reactivate'])->name('device-shield.reactivate');
Route::post('/device-shield/devices/{id}/force-rebind', [App\Http\Controllers\DeviceBindingController::class, 'forceRebind'])->name('device-shield.force-rebind');
Route::post('/device-shield/devices/{id}/update-name', [App\Http\Controllers\DeviceBindingController::class, 'updateDeviceName'])->name('device-shield.update-name');
Route::get('/device-shield/logs', [App\Http\Controllers\DeviceBindingController::class, 'logs'])->name('device-shield.logs');
Route::post('/device-shield/users/{id}/toggle-check', [App\Http\Controllers\DeviceBindingController::class, 'toggleDeviceCheck'])->name('device-shield.users.toggle-check');

/** Student Report Routes */
Route::get('student_report', [StudentReportController::class, 'index'])->name('student_report.index');
Route::get('student_report/data', [StudentReportController::class, 'getReportData'])->name('student_report.data');
Route::get('student_report/print', [StudentReportController::class, 'printReport'])->name('student_report.print');
Route::get('student_report/pdf', [StudentReportController::class, 'downloadPdf'])->name('student_report.pdf');
Route::get('student_report/students-list', [StudentReportController::class, 'getStudentsList'])->name('student_report.students_list');
Route::match(['get', 'post'], 'student_report/bulk-pdf', [StudentReportController::class, 'bulkDownloadPdf'])->name('student_report.bulk_pdf');


