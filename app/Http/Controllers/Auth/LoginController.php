<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    // protected $redirectTo = '/home';
    protected $redirectTo = RouteServiceProvider::DASHBOARD;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    public function showLoginForm(Request $request)
    {
        // Auth::logout(); // Logs out the user

        // $request->session()->invalidate(); // Invalidates the session
        // $request->session()->regenerateToken(); // Regenerates the CSRF token

        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        // Custom validation rules
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        // Attempt login with 'api' guard
        if (Auth::attempt($request->only('email', 'password'), $request->filled('remember'))) {
            $user = Auth::user();

            if(in_array(Helper::getLoginUserRole(), Helper::getApplicationUserRoles()->pluck('name')->toArray())){
                Auth::logout(); // Logs out the user
                return Redirect::back()->withErrors('Your not admin login user.');
            }
            // dd("Admin 72", $user->roles->toArray(), Helper::getLoginUserRole(), Helper::getApplicationUserRoles()->pluck('name')->toArray());
            if($user->status == "inactive"){
                Auth::logout(); // Logs out the user
                return Redirect::back()->withErrors(["Conntact to admin. You are Inactive."]);
            }
            return Redirect::route('software.dashboard')->with('success', 'Login successful!');
        }
        return Redirect::back()->withErrors(["Invalid credentials"]);
        // Custom failed response
        return response()->json([
            'message' => 'Invalid credentials'
        ], 401);
    }

    public function logout(Request $request)
    {
        Auth::logout(); // Logs out the user

        $request->session()->invalidate(); // Invalidates the session
        $request->session()->regenerateToken(); // Regenerates the CSRF token
        return redirect()->route("software.login");
    }
}
