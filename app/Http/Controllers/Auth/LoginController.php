<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\ApiToken;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;

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

        return view('software.auth.login');
    }

    // public function login(Request $request)
    // {
    //     // Custom validation rules
    //     $request->validate([
    //         'email' => 'required|email',
    //         'password' => 'required|min:6',
    //     ]);

    //     // Attempt login with 'api' guard
    //     if (Auth::attempt($request->only('email', 'password'), $request->filled('remember'))) {
    //         $user = Auth::user();

    //         if (in_array(Helper::getLoginUserRole(), Helper::getApplicationUserRoles()->pluck('name')->toArray())) {
    //             Auth::logout(); // Logs out the user
    //             return Redirect::back()->withErrors('Your not admin login user.');
    //         }
    //         // dd("Admin 72", $user->roles->toArray(), Helper::getLoginUserRole(), Helper::getApplicationUserRoles()->pluck('name')->toArray());
    //         if ($user->status == "inactive") {
    //             Auth::logout(); // Logs out the user
    //             return Redirect::back()->withErrors(["Conntact to admin. You are Inactive."]);
    //         }
    //         return Redirect::route('software.dashboard')->with('success', 'Login successful!');
    //     }
    //     return Redirect::back()->withErrors(["Invalid credentials"]);
    //     // Custom failed response
    //     return response()->json([
    //         'message' => 'Invalid credentials'
    //     ], 401);
    // }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        if (Auth::attempt($request->only('email', 'password'), $request->filled('remember'))) {
            $user = Auth::user();

            if (in_array(Helper::getLoginUserRole(), Helper::getApplicationUserRoles()->pluck('name')->toArray())) {
                Auth::logout();
                return Redirect::back()->withErrors('You are not authorized to login as admin.');
            }

            if ($user->status == "inactive") {
                Auth::logout();
                return Redirect::back()->withErrors(["Contact admin. Your account is inactive."]);
            }

            //  check for token alraday store to direct login for user
            // $existingToken = ApiToken::latest()->first();
            // if ($existingToken && !empty($existingToken->token)) {
            //     Session::put('external_api_token', $existingToken->token);
            //     return Redirect::route('software.dashboard')
            //         ->with('success', 'Login successful! Token loaded from database.');
            // }
            return Redirect::route('software.dashboard')->with('success', 'Login successful!');

            try {
                $apiResponse = Http::post('http://192.168.31.5:98/api/Auth/Login', [
                    'Id'          => 0,
                    'Username'    => 'biomax',
                    'Password'    => 'biomax',
                    'OldPassword' => ''
                ]);

                if ($apiResponse->successful()) {
                    $responseData = $apiResponse->json();

                    if (isset($responseData['Token'])) {
                        $token = $responseData['Token'];

                        Session::put('external_api_token', $token);

                        // api response token add in database
                        // $loginUserId = Auth::user()->id;
                        // ApiToken::create([
                        //     'api_token' => $token,
                        //     'created_by' => $loginUserId,
                        // ]);

                        return Redirect::route('software.dashboard')
                            ->with('success', 'Login successful! New token saved.');
                    } else {
                        return Redirect::route('software.dashboard')
                            ->with('success', 'Login successful! No token returned.');
                    }
                } else {
                    Auth::logout();
                    return Redirect::back()->withErrors(['External API login failed.']);
                }
            } catch (\Exception $e) {
                Auth::logout();
                return Redirect::back()->withErrors(['API connection error: ' . $e->getMessage()]);
            }
        }
        return Redirect::back()->withErrors(["Invalid credentials"]);
    }

    public function logout(Request $request)
    {
        Auth::logout(); // Logs out the user

        $request->session()->invalidate(); // Invalidates the session
        $request->session()->regenerateToken(); // Regenerates the CSRF token
        return redirect()->route("software.login");
    }
}
