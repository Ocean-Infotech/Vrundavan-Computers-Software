<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;

class BioMaxController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public $modules = [];

    public function __construct()
    {
        $this->modules = [
            'title' => 'Biomax',
            'folder_path' => 'software.module.biomax',
            'route' => 'biomax',
            'table_name' => "",
            'permisstion_prefix' => 'Test Report',
            // 'biomax_url' => 'http://192.168.1.88:88/',
            'biomax_url' => 'http://192.168.31.5:98/',
        ];
    }

    public function biomax_status(Request $request)
    {
        $this->modules['external_api_token'] = null;

        if ($request->ajax()) {
            try {
                $apiResponse = Http::post($this->modules['biomax_url'] . 'api/Auth/Login', [
                    'Id'          => 0,
                    'Username'    => 'biomax',
                    'Password'    => 'biomax',
                    'OldPassword' => ''
                ]);

                if ($apiResponse->successful()) {
                    $responseData = $apiResponse->json();

                    if (isset($responseData['Token'])) {
                        $token = $responseData['Token'];

                        $this->modules['external_api_token'] = $token;
                        Session::put('external_api_token', $token);

                        // api response token add in database
                        // $loginUserId = Auth::user()->id;
                        // ApiToken::create([
                        //     'api_token' => $token,
                        //     'created_by' => $loginUserId,
                        // ]);

                        return $this->sendResponse($this->modules, 'Login successful! New token saved.');
                    } else {
                        return $this->sendResponse($this->modules, 'Login successful! No token returned.');
                    }
                } else {
                    return $this->sendError('External API login failed.');
                }
            } catch (\Exception $e) {
                return $this->sendError('API connection error: ' . $e->getMessage());
            }
            return $this->sendError('Some thing want wrong.');
        }
        $modules = $this->modules;
        View::share('modules', $modules);
        return view($modules['folder_path'] . '.status');
    }
}
