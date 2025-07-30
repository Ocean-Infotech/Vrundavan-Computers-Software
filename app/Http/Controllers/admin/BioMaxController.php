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
            'biomax_url' => 'http://192.168.1.88:88/',
            // 'biomax_url' => 'http://192.168.31.5:98/',
        ];
    }

    public function biomax_status(Request $request)
    {
        $modules = $this->modules;
        $this->modules['external_api_token'] = null;
        if ($request->ajax()) {
            $returnResponse = [];
            $returnResponse['biomax_url'] = $this->modules['biomax_url'];

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

                        $returnResponse['external_api_token'] = $token;
                        Session::put('external_api_token', $token);

                        // api response token add in database
                        // $loginUserId = Auth::user()->id;
                        // ApiToken::create([
                        //     'api_token' => $token,
                        //     'created_by' => $loginUserId,
                        // ]);

                        $deviceResponse = Http::withHeaders([
                            'Authorization' => 'Bearer ' . $token,
                        ])->get($returnResponse['biomax_url'] . 'api/Device');

                        if (!$deviceResponse->successful()) {
                            // dd("ln-246" . $deviceResponse->body());
                        }
                        $columns = [
                            (object)['data' => 'Name', 'name' => 'Name', 'td_label' => 'Name', 'orderable' => false, 'searchable' => false],
                            (object)['data' => 'Serial Number', 'name' => 'course_id', 'td_label' => 'Serial Number', 'orderable' => false, 'searchable' => false],
                            (object)['data' => "id", 'name' => 'id', 'td_label' => 'Update On', 'orderable' => false, 'searchable' => false],
                            (object)['data' => "id", 'name' => 'id', 'td_label' => 'Log Count', 'orderable' => false, 'searchable' => false],
                            (object)['data' => "id", 'name' => 'id', 'td_label' => 'User Count', 'orderable' => false, 'searchable' => false],
                            (object)['data' => "id", 'name' => 'id', 'td_label' => 'Face Count', 'orderable' => false, 'searchable' => false],
                            (object)['data' => "id", 'name' => 'id', 'td_label' => 'FP Count', 'orderable' => false, 'searchable' => false],
                            (object)['data' => "id", 'name' => 'id', 'td_label' => 'Plam Count', 'orderable' => false, 'searchable' => false],
                            (object)['data' => "id", 'name' => 'id', 'td_label' => 'Location Count', 'orderable' => false, 'searchable' => false],
                            // (object)['data' => 'leve_status', 'name' => 'leve_status', 'td_label' => 'Leave Status', 'orderable' => false, 'searchable' => false],
                        ];

                        $data = $deviceResponse->json();
                        $returnResponse['api_device'] = $data;
                        $deviceView = view($modules['folder_path'] . '.device', compact('columns', 'data'))->render();
                        $returnResponse['api_device_html'] = $deviceView;

                        return $this->sendResponse($returnResponse, 'Login successful! New token saved.');
                    } else {
                        return $this->sendResponse($returnResponse, 'Login successful! No token returned.');
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
