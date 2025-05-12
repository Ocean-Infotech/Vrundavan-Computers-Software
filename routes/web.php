<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Middleware\SoftwareAuthMiddleware;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;

Auth::routes();


Route::get('optimize', function () {
    Artisan::call('optimize:clear');
    return Redirect::back()->with('success', 'Optimize the site, cleare all cache');
})->name('optimize');

Route::get('/software/login', [LoginController::class, 'showLoginForm'])->name('software.login');

/** Software Routes */
Route::group(['prefix' => 'software', 'middleware' => [SoftwareAuthMiddleware::class]], function () {
    include  base_path("routes/software.php");
});


Route::get('/nimit', function () {
    return view('welcome');
});

Route::get('/', function () {
    return redirect('software/', 301);
});
