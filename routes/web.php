<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Middleware\SoftwareAuthMiddleware;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;

Auth::routes();

// Public pages
Route::view('/software/privacy-policy', 'software.privacy-policy')->name('software.privacy-policy');

Route::get('optimize', function () {
    Artisan::call('optimize:clear');
    return Redirect::back()->with('success', 'Optimize the site, cleare all cache');
})->name('optimize');

Route::get('/software/login', [LoginController::class, 'showLoginForm'])->name('software.login');

/** Software Routes */
Route::group(['prefix' => 'software', 'middleware' => [SoftwareAuthMiddleware::class, 'device.authorized']], function () {
    Route::get('/download-agent', [\App\Http\Controllers\DeviceBindingController::class, 'downloadAgent'])->name('download.agent');
    include base_path("routes/software.php");
});
// Route::get('/gd-test', function(){
//     $res = \App\Helpers\GoogleDriveHelper::createFolder('test-'.time(), '1Zccw9vC6d4rfBvoLAe3tPWCRNDWE9gk7');
//     return $res;
// });

Route::get('/gd-test', function () {
    $parentFolderId = '1pG7EV04G2A0fR7M9AF0ZppiY_gBhjsxI';
    $res = \App\Helpers\GoogleDriveHelper::createFolder('student_' . time(), $parentFolderId);
    return response()->json($res);
});


Route::get('/nimit', function () {
    return view('welcome');
});

Route::get('/', function () {
    return redirect('software/', 301);
});
