<?php

namespace App\Http\Controllers\admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Imports\StudentImport;
use Illuminate\Http\Request;
use App\Models\Admission;
use App\Models\EducationDetails;
use Maatwebsite\Excel\Facades\Excel;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use App\Http\Requests\AdmissionRequest;
use App\Models\CourceRegistration;
use App\Models\Master\MasterCourse;
use App\Traits\BioMetricTrait;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Yaza\LaravelGoogleDriveStorage\Gdrive;

use Google_Service_Drive;
use Google_Service_Drive_DriveFile;
use Google_Client;
use Illuminate\Container\Attributes\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class AdmissionController extends Controller
{
    use BioMetricTrait;

    public $modules = [];
    protected array $exportableColumns = [
        'id' => 'REG No.',
        // 'biometric_id' => 'Biometric ID',
        'aadhar_card_no' => 'Aadhaar No',
        'first_name' => 'First Name',
        'last_name' => 'Last Name',
        'father_name' => "Father's Name",
        'mother_name' => "Mother's Name",
        'full_name' => 'Full Name',
        'date_of_birth' => 'Date of Birth',
        'gender' => 'Gender',
        'cast' => 'Caste',
        'category' => 'Category',
        'occupation' => "Father's Occupation",
        'temporary_address' => 'Temporary Address',
        'permanent_address' => 'Permanent Address',
        'mobile_no' => 'Mobile No',
        'parent_mobile_no' => "Parent's Mobile No",
        'other_mobile_no' => 'Other Mobile No',
        'whatsapp_no' => 'Whatsapp No',
        'email_address' => 'Email Address',
        'apaar_id_abc_id' => 'APAAR ID / ABC ID',
        'udise' => 'UDISE No',
        'enrolment_no' => 'Enrollment No',
        'spid' => 'SPID',
        'gdrivefolderurl' => 'Google Drive Folder URL',
        'status' => 'Status',
        'created_at' => 'Admission Date',
        'course_names' => 'Course',
    ];

    protected array $defaultExportColumns = [
        'id',
        // 'biometric_id',
        'aadhar_card_no',
        'first_name',
        'last_name',
        'father_name',
        'mother_name',
        'full_name',
        'date_of_birth',
        'gender',
        'cast',
        'category',
        'occupation',
        'temporary_address',
        'permanent_address',
        'mobile_no',
        'parent_mobile_no',
        'other_mobile_no',
        'whatsapp_no',
        'email_address',
        'apaar_id_abc_id',
        'udise',
        'enrolment_no',
        'spid',
        'gdrivefolderurl',
        'status',
        'created_at',
        'course_names',
    ];
    public function __construct()
    {
        $this->modules = [
            'title' => 'Admission',
            'folder_path' => 'software.module.admission',
            'route' => 'admission',
            'table_name' => (new Admission())->getTable(),
            'permission_prefix' => 'admission',
            'api_ip' => 'http://192.168.31.5:98/',
        ];
    }


    public function index(Request $request)
    {

        /*
        return $apiResponse = Http::withHeaders([
            'Content-Type' => 'application/json',
            // 'Authorization' => 'Bearer ' . $token,
        ])->get('http://192.168.1.88:88/api/Employees');
        */

        $modules = $this->modules;

        $modules['login_user_role'] = Helper::getLoginUserRole();

        $modules['permission_list'] = Helper::directCan($modules['permission_prefix'] . '-list');
        $modules['permission_add'] = Helper::directCan($modules['permission_prefix'] . '-create');
        $modules['permission_edit'] = Helper::directCan($modules['permission_prefix'] . '-edit');
        $modules['permission_delete'] = Helper::directCan($modules['permission_prefix'] . '-delete');

        $modules['course-register_add'] = Helper::directCan('course-register-create');

        $modules['fees-collection-create'] = Helper::directCan('fees-collection-create');
        $modules['fees-collection-edit'] = Helper::directCan('fees-collection-edit');

        // $modules['permission_add'] = Helper::directCan($modules['permission_prefix'] . '-create');

        if (!$modules['permission_list']) {
            if (isset($request) && $request->ajax()) {
                return $this->sendError('User does not have the right permissions.', [], [], 403);
            }
            abort(403, 'User does not have the right permissions.');
        }
        // return $modules;
        View::share('modules', $modules);

        try {
            $columns = [
                (object) ['data' => "id", 'name' => 'id', 'td_label' => 'REG No.'],
                // (object) ['data' => "admission_name", 'name' => 'first_name', 'td_label' => 'Name'],
                (object) [
                    'data' => 'admission_name',
                    'name' => 'first_name',
                    'td_label' => 'Name'
                ],

                (object) ['data' => "course_button", 'name' => 'course_button', 'td_label' => 'Course'],
                (object) ['data' => "view_button", 'name' => 'view_button', 'td_label' => 'Stu <i class="bx bx-info-circle"></i>'],
                (object) ['data' => "attendance_button", 'name' => 'attendance_button', 'td_label' => 'Att.', 'className' => 'w-5 text-center'],
                (object) ['data' => "test_button", 'name' => 'test_button', 'td_label' => 'Test', 'className' => 'w-5 text-center'],

                (object) ['data' => "gdrivefolderurl", 'name' => 'gdrivefolderurl', 'td_label' => '<i class="fab fa-google-drive" style="font-size:18px;color:#4285F4;"></i>', 'className' => 'w-5 text-center'],
                // (object) ['data' => "status", 'name' => 'status', 'td_label' => 'Status', 'className' => 'w-5 text-center', 'width' => '10%'],
                (object) ['data' => "book_button", 'name' => 'book_button', 'td_label' => 'Book'],
                // (object) ['data' => "bonafide_button", 'name' => 'bonafide_button', 'td_label' => '<i class="ti ti-certificate" title="Bonafide Certificate"></i>', 'className' => 'w-5 text-center'],
                // (object) ['data' => "transfer_button", 'name' => 'transfer_button', 'td_label' => '<i class="ti ti-transfer-out" title="Transfer Certificate"></i>', 'className' => 'w-5 text-center'],
                (object) ['data' => "action", 'name' => 'action', 'td_label' => 'Update', 'orderable' => false, 'searchable' => false, 'className' => 'w-10 text-center'],
            ];
            if (!$modules['permission_edit'] && !$modules['permission_delete']) {
                $columns = array_filter($columns, function ($col) {
                    return $col->name !== 'action';
                });
            }
            View::share("columns", $columns);

            $data = [];

            if ($request->ajax()) {
                $data = Admission::withTrashed();

                if ($request->filled('course_id')) {
                    $courseId = $request->input('course_id');
                    $data->whereHas('courses', function ($q) use ($courseId) {
                        $q->where('course_id', $courseId);
                    });
                }

                $editPermisstion = $modules['permission_edit'];
                // $deletePermisstion = $modules['permission_delete'];
                $deletePermisstion = false;

                return Datatables::of($data)
                    ->addIndexColumn()

                    ->filter(function ($query) use ($request) {
                        $search = $request->input('search');
                        $searchValue = is_array($search) ? ($search['value'] ?? '') : $search;
                        $trimmedSearch = trim((string) $searchValue);

                        if ($trimmedSearch !== '') {
                            $query->where(function ($q) use ($trimmedSearch) {
                                if (is_numeric($trimmedSearch)) {
                                    $q->where('id', $trimmedSearch)
                                      ->orWhere('gr_no', $trimmedSearch)
                                      ->orWhere('biometric_id', $trimmedSearch);
                                } else {
                                    $words = array_filter(explode(' ', $trimmedSearch));
                                    foreach ($words as $word) {
                                        $q->orWhere('first_name', 'like', "%" . $word . "%")
                                          ->orWhere('last_name', 'like', "%" . $word . "%")
                                          ->orWhere('father_name', 'like', "%" . $word . "%");
                                    }
                                }
                            });
                        }
                    })
                    ->editColumn('admission_name', function ($row) {
                        $first  = $row->first_name ?? '';
                        $last   = $row->last_name ?? '';
                        $father = $row->father_name ?? '';

                        // Create LastName FirstName FatherName
                        $fullName = trim("$first $last  $father");
                        // dd($fullName);
                        return $fullName ?: '____________';
                    })
                    // ->editColumn('gdrivefolderurl', function ($row) {
                    //     $folderName = $row->id . '-' . $row->first_name . '-' . $row->father_name . '-' . $row->last_name;
                    //     return '<a href="javascript:void(0)" data-id="' . $row->id . '" data-foldername="' . $folderName . '" data-folderurl="' . $row->gdrivefolderurl . '" class="btn btn-warning btn-sm open_google_drive_folder"><i class="bx bx-copy-alt"></i> Open Doucuments</a>';
                    // })

                    ->editColumn('gdrivefolderurl', function ($row) {
                        if (!empty($row->gdrivefolderurl) && !empty($row->gdrivefolderid) && !empty($row->gdrivefoldername)) {
                            $folderName = $row->id . '-' . $row->first_name . '-' . $row->father_name . '-' . $row->last_name;
                            return '<a href="' . $row->gdrivefolderurl . '" target="_blank" data-id="' . $row->id . '" data-foldername="' . $folderName . '" data-folderurl="' . $row->gdrivefolderurl . '" class="btn btn-sm open_google_drive_folder" title="Open Google Drive Folder"> <i class="fab fa-google-drive" style="font-size:18px;"></i> </a>';
                        } else {
                            return '<a href="javascript:void(0)" data-id="' . $row->id . '" class="btn btn-primary btn-sm create_google_drive_folder" title="Create Google Drive Folder"> <i class="fa fa-folder-plus"></i> </a>';
                        }
                    })

                    // ->editColumn('status', function ($row) use ($modules) {
                    //     $dropdown = '<ul class="dropdown-menu">';
                    //     $button = '';

                    //     if ($row->status == "created") {
                    //         $button = '<button type="button" class="btn btn-success btn-sm w-100 dropdown-toggle" data-bs-toggle="dropdown">' . ucfirst($row->status) . '</button>';
                    //         $dropdown .= '<li><a href="javascript:void(0)" class="dropdown-item update-status" data-url="' . route($modules["route"] . '.status-update') . '" data-id="' . $row->id . '" data-update_status="rejected">Reject</a></li>';
                    //     } elseif ($row->status == "rejected") {
                    //         $button = '<button type="button" class="btn btn-danger btn-sm w-100 dropdown-toggle" data-bs-toggle="dropdown">' . ucfirst($row->status) . '</button>';
                    //         $dropdown .= '<li><a href="javascript:void(0)" class="dropdown-item update-status" data-url="' . route($modules["route"] . '.status-update') . '" data-id="' . $row->id . '" data-update_status="created">Re-Create</a></li>';
                    //     }

                    //     $dropdown .= '</ul>';
                    //     return $button . $dropdown;
                    // })

                    ->addColumn('course_button', function ($row) use ($modules) {
                        // if ($modules['course-register_add']) {
                        return '<button class="btn btn-primary btn-sm course-btn" data-id="' . $row->id . '" data-name="' . $row->first_name . '">Course</button>';
                        // }
                        return "-";
                    })
                    ->addColumn('book_button', function ($row) {
                        return '<button class="btn btn-info btn-sm book-btn" data-id="' . $row->id . '" data-name="' . $row->first_name . '"> Book </button>';
                    })

                    // ->addColumn('bonafide_button', function ($row) {
                    //     return '<a href="' . route('admission.bonafide-certificate', $row->id) . '" target="_blank" class="btn btn-secondary btn-sm" title="Bonafide Certificate"> <i class="ti ti-certificate"></i> </a>';
                    // })

                    // ->addColumn('transfer_button', function ($row) {
                    //     return '<a href="' . route('admission.transfer-certificate', $row->id) . '" target="_blank" class="btn btn-dark btn-sm" title="Transfer Certificate"> <i class="ti ti-transfer-out"></i> </a>';
                    // })

                    ->addColumn('view_button', function ($row) {
                        return '<button class="btn btn-success btn-sm view-btn" data-id="' . $row->id . '" data-name="' . $row->first_name . '" data-mobile="' . $row->mobile_no . '">View</button>';
                    })
                    // Replace the existing attendance_button code in your controller (around line 130)
                    ->addColumn('attendance_button', function ($row) {
                        return '<a href="' . url('software/attedance') . '" class="btn btn-warning btn-sm open-attendance" data-admission-id="' . $row->id . '" target="_blank" title="View Attendance"> <i class="bx bx-calendar-check"></i> </a>';
                    })

                    ->addColumn('test_button', function ($row) {
                        return '<a href="' . url('software/test_report') . '" class="btn btn-info btn-sm open-test" data-admission-id="' . $row->id . '" title="View Test Report"> <i class="bx bx-file"></i> </a>';
                    })

                    ->addColumn('action', function ($row) use ($modules, $editPermisstion, $deletePermisstion) {
                        $btn = '';
                        if (!$row->deleted_at) {
                            if ($editPermisstion) {
                                $btn .= '<a href="' . route($modules["route"] . ".edit", [$row->id]) . '" class="btn btn-primary btn-sm mx-1">Update</a>';
                            }

                            if ($deletePermisstion) {
                                $btn .= '<a href="javascript:void(0)" data-id="' . $row->id . '" data-did="' . route($modules["route"] . ".destroy", [$row->id]) . '" class="btn btn-danger btn-icon deletebutton mx-1"><i class="fa-solid fa-trash"></i></a>';
                            }
                        } else {
                            $btn .= '<a href="javascript:void(0)" data-restore="' . route($modules["route"] . ".restore", ['id' => $row->id]) . '" class="btn btn-light mx-1 record-restore"><i class="ti ti-history"></i> Restore</a>';
                        }

                        return $btn ?: '-';
                    })

                    ->rawColumns(['status', 'action', 'gdrivefolderurl', 'admission_name', 'course_button', 'view_button', 'book_button', 'attendance_button', 'test_button'])
                    ->make(true);
            }

            $availableExportColumns = $this->exportableColumns;
            $defaultExportColumns = $this->defaultExportColumns;
            $courses = \App\Models\Master\MasterCourse::where('status', 'active')->orderBy('course_name')->get();
            $batches = \App\Models\Master\MasterBatch::where('status', 'active')->orderBy('batch_name')->get();

            return view($modules['folder_path'] . '.index', compact('data', 'availableExportColumns', 'defaultExportColumns', 'courses', 'batches'));
        } catch (\Exception $e) {
            return Redirect::route('dashboard')->withErrors($e->getMessage());
        }
    }

    public function create()
    {
        $modules = $this->modules;
        $modules['login_user_role'] = Helper::getLoginUserRole();
        $modules['permission_add'] = Helper::directCan($modules['permission_prefix'] . '-create');
        if (!$modules['permission_add']) {
            if (isset($request) && $request->ajax()) {
                return $this->sendError('User does not have the right permissions.', [], [], 403);
            }
            abort(403, 'User does not have the right permissions.');
        }
        View::share('modules', $modules);
        return view($modules['folder_path'] . '.form', compact('modules'));
    }


    public function store(AdmissionRequest $request)
    {
        $modules = $this->modules;
        $modules['login_user_role'] = Helper::getLoginUserRole();
        $modules['permission_add'] = Helper::directCan($modules['permission_prefix'] . '-create');
        if (!$modules['permission_add']) {
            if (isset($request) && $request->ajax()) {
                return $this->sendError('User does not have the right permissions.', [], [], 403);
            }
            abort(403, 'User does not have the right permissions.');
        }

        View::share('modules', $modules);
        $validated = $request->validated();
        if ($request->hasFile('profile_pic')) {
            $file = $request->file('profile_pic');

            $firstName = $request->input('first_name');
            $profileName = $request->input('last_name'); // or another field if needed
            $folderName = $firstName . '_' . $profileName;

            $destinationPath = public_path('uploads/profile_pics/' . $folderName);

            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }

            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move($destinationPath, $filename);

            $validated['profile_pic'] = 'uploads/profile_pics/' . $folderName . '/' . $filename;
        }

        $validated['first_name']  = strtoupper($validated['first_name']);
        $validated['last_name']   = strtoupper($validated['last_name']);
        $validated['father_name'] = strtoupper($validated['father_name']);
        $validated['mother_name'] = strtoupper($validated['mother_name']);


        // dd($request->all(), $validated);
        $token = Session::get('external_api_token');

        try {

            $loginUserId = Auth::user()->id;
            $validated['created_by'] = $loginUserId;


            $admission = Admission::create($validated);

            $admission_id = $admission->id;
            try {
                $folderName = $admission->id . '- ' . $admission->last_name . ' ' . $admission->first_name . ' ' . $admission->father_name;

                $parentFolderId = '1pG7EV04G2A0fR7M9AF0ZppiY_gBhjsxI'; // Main Student Folder ID

                $folderData = \App\Helpers\GoogleDriveHelper::createFolder($folderName, $parentFolderId);

                $admission->update([
                    'gdrivefolderid'   => $folderData['id'] ?? null,
                    'gdrivefoldername' => $folderData['name'] ?? $folderName,
                    'gdrivefolderurl'  => $folderData['link'] ?? null,
                ]);
            } catch (\Exception $e) {
                \Log::error("Google Drive folder creation failed: " . $e->getMessage());
            }
            $education = $request->education;
            if ($education != "") {
                foreach ($education as $key => $value) {
                    EducationDetails::create([
                        'admission_id' => $admission_id,
                        'education' => $value,
                        'percentage_cgpa' => $request->percentage_cgpa[$key] ?? 0,
                        'seat_no_nrollment_no' => $request->seat_no_nrollment_no[$key] ?? '',
                        'board_university' => $request->board_university[$key],
                        'passing_year' => $request->passing_year[$key] ?? 0,
                        'school_name_college_name' => $request->school_name_college_name[$key],
                    ]);
                }
            }

            /*
            $gender = $admission?->gender;
            if ($gender == "Male") {
                $gender = 0;
            } else if ($gender == "Female") {
                $gender = 1;
            } else if ($gender == "Other") {
                $gender = 2;
            }

            $student_name = $admission?->first_name . " " . $admission?->last_name;
            $apiResponse = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $token,
            ])->post($modules['api_ip'] . 'api/Employees', [
                "Id" => 0,
                "Code" => (string) 'SA' . $admission_id,
                "DeviceRegisterId" => (string) $admission->aadhar_card_no ?? '',
                "Name" => $student_name ?? '',
                "Gender" => $gender ?? "",
                "DepartmentId" => 0,
                "Department" => "",
                "LocationId" => 0,
                "Location" => $admission->permanent_address ?? "",
                "ImagePath" => "",
                "CreatedBy" => (string) $loginUserId,
                "CreatedOn" => now()->toIso8601String(),
                "UpdatedBy" => "",
                "UpdatedOn" => now()->toIso8601String()
            ]);

            if ($apiResponse->successful()) {

                $responseBody = $apiResponse->json();
                $biometricId = $responseBody['Id'] ?? null;

                if ($biometricId) {
                    $admission->update([
                        'biometric_id' => $biometricId
                    ]);
                }

                $deviceResponse = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                ])->get($modules['api_ip'] . 'api/Device');

                if (!$deviceResponse->successful()) {
                    // dd("ln-246" . $deviceResponse->body());
                    return Redirect::route($modules['route'] . '.create')
                        ->withErrors('Device fetch failed : ' . $deviceResponse->body());
                }

                $deviceData = $deviceResponse->json();

                $device_id = $deviceData[0]['DeviceKey'] ?? null;

                $deviceKey = $device_id;

                $uploadUserPayload = [
                    [
                        "UserId" => (string) $biometricId,
                        "UserName" => $student_name ?? '',
                        "CardNumber" => (string) ($admission->aadhar_card_no ?? ''),
                        "Password" => $admission->mobile_no ?? '',
                        "AccessLevel" => "USER",
                        "DeviceKeys" => [
                            $deviceKey
                        ],
                        "OnlineEnrollment" => false,
                        "UploadBiometricDataIfAvailable" => true,
                        "FingerPrintUpload" => false,
                        "FaceUpload" => false,
                        "CardUpload" => false,
                        "PasswordUpload" => false
                    ]
                ];

                $uploadUserResponse = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $token,
                ])->post($modules['api_ip'] . 'api/DeviceCommand/UploadUser', $uploadUserPayload);

                // dd($uploadUserResponse->body(), $uploadUserResponse->successful());


                if (!$uploadUserResponse->successful()) {
                    return Redirect::route($modules['route'] . '.index')
                        ->withErrors('Admission created and synced, but device upload failed: ' . $uploadUserResponse->body());
                }

                return Redirect::route('cource-registration.create', ['admission_id' => $admission_id])
                    ->with('success', $admission->name . ' has been successfully admitted and synced with the external system.');
            } else {
                return Redirect::route($modules['route'] . '.index')
                    ->withErrors('Admission created, but API sync failed: ' . $apiResponse->body());
            }
                    */
            if (Helper::directCan('course-register-create')) {
                return Redirect::route('cource-registration.create', ['admission_id' => $admission_id])->with('success', $admission->first_name . ' has been successfully admitted.');
            }
            return Redirect::route($modules['route'] . '.index')->with('success', $admission->first_name . ' has been successfully admitted.');
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }

    public function show(Request $request)
    {
        $student = Admission::withTrashed()->find($request->id);

        $studentCourses = CourceRegistration::with(['course', 'class', 'shift', 'batch'])
            ->withTrashed()
            ->where('register_id', $request->id)
            ->get();

        if (!$student && $studentCourses->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Student and Course not found.']);
        }

        return response()->json([
            'success' => true,
            'student' => $student,
            'courses' => $studentCourses,
        ]);
    }

    public function edit(string $id)
    {
        $modules = $this->modules;
        $modules['login_user_role'] = Helper::getLoginUserRole();
        $modules['permission_edit'] = Helper::directCan($modules['permission_prefix'] . '-edit');
        if (!$modules['permission_edit']) {
            if (isset($request) && $request->ajax()) {
                return $this->sendError('User does not have the right permissions.', [], [], 403);
            }
            abort(403, 'User does not have the right permissions.');
        }
        View::share('modules', $modules);
        try {


            $edit = Admission::findOrFail($id);

            $education = EducationDetails::where('admission_id', $id)->get();
            View::share('education', $education);

            return view($modules['folder_path'] . '.form', compact('modules', 'edit'));
        } catch (\Exception $e) {

            return Redirect::route('dashboard')->withErrors($e->getMessage());
        }
    }

    public function update(AdmissionRequest $request, string $id)
    {
        $modules = $this->modules;
        $modules['login_user_role'] = Helper::getLoginUserRole();
        $modules['permission_edit'] = Helper::directCan($modules['permission_prefix'] . '-edit');
        if (!$modules['permission_edit']) {
            if (isset($request) && $request->ajax()) {
                return $this->sendError('User does not have the right permissions.', [], [], 403);
            }
            abort(403, 'User does not have the right permissions.');
        }
        $validated = $request->validated();
        $validated['first_name']  = strtoupper($validated['first_name']);
        $validated['last_name']   = strtoupper($validated['last_name']);
        $validated['father_name'] = strtoupper($validated['father_name']);
        $validated['mother_name'] = strtoupper($validated['mother_name']);

        // dd($validated,$request->all());
        $token = Session::get('external_api_token');
        try {
            $loginUserId = Auth::user()->id;
            $validated['updated_by'] = $loginUserId;

            $admission = Admission::where('id', $id)->firstOrFail();
            $admission_id = $admission->id;
            if ($request->hasFile('profile_pic')) {
                $file = $request->file('profile_pic');

                $firstName = $request->input('first_name');
                $profileName = $request->input('last_name');
                $folderName = $firstName . '_' . $profileName;

                $destinationPath = public_path('uploads/profile_pics/' . $folderName);

                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }

                $filename = time() . '_' . $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                $filename = time() . '_' . pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . '.' . $extension;
                $file->move($destinationPath, $filename);

                $validated['profile_pic'] = 'uploads/profile_pics/' . $folderName . '/' . $filename;
            }

            $admission->update($validated);

            if ($request->has('education_id')) {
                $education = $request->education;
                foreach ($education as $key => $value) {
                    $education_id = $request->education_id[$key];
                    $data = [
                        'admission_id' => $admission->id,
                        'education' => $value,
                        'percentage_cgpa' => $request->percentage_cgpa[$key] ?? 0,
                        'seat_no_nrollment_no' => $request->seat_no_nrollment_no[$key] ?? '',
                        'board_university' => $request->board_university[$key],
                        'passing_year' => $request->passing_year[$key] ?? 0,
                        'school_name_college_name' => $request->school_name_college_name[$key],
                    ];

                    if ($education_id) {
                        $existingEdu = EducationDetails::find($education_id);
                        $existingEdu ? $existingEdu->update($data) : EducationDetails::create($data);
                    } else {
                        EducationDetails::create($data);
                    }
                }
            }

            /*
            if (!$admission?->biometric_id && false) {
                //     $response = $this?->storeBioMetricEmployee($loginUserId, $admission);
                //     return $response;
                //     if ($response['status'] && $response['data']) {
                //         $responseData = (object)$response['data'];
                //         $responseData?->root?->Id;
                //         $admission = Admission::where('id', $CourceRegistration->register_id)->firstOrFail();
                //         $admission->biometric_id = $responseData?->root?->Id;
                //         $admission->save();
                //     }
                $gender
                    = $admission?->gender;
                if ($gender == "Male") {
                    $gender = 0;
                } else if ($gender == "Female") {
                    $gender = 1;
                } else if ($gender == "Other") {
                    $gender = 2;
                }

                $student_name = $admission?->first_name . " " . $admission?->last_name;
                $apiResponse = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $token,
                ])->post($modules['api_ip'] . 'api/Employees', [
                    "Id" => 0,
                    "Code" => (string) 'SA' . $admission_id,
                    "DeviceRegisterId" => (string) $admission->aadhar_card_no ?? '',
                    "Name" => $student_name ?? '',
                    "Gender" => $gender ?? "",
                    "DepartmentId" => 0,
                    "Department" => "",
                    "LocationId" => 0,
                    "Location" => $admission->permanent_address ?? "",
                    "ImagePath" => "",
                    "CreatedBy" => (string) $loginUserId,
                    "CreatedOn" => now()->toIso8601String(),
                    "UpdatedBy" => "",
                    "UpdatedOn" => now()->toIso8601String()
                ]);

                if ($apiResponse->successful()) {

                    $responseBody = $apiResponse->json();
                    $biometricId = $responseBody['Id'] ?? null;

                    if ($biometricId) {
                        $admission->update([
                            'biometric_id' => $biometricId
                        ]);
                    }

                    $deviceResponse = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $token,
                    ])->get($modules['api_ip'] . 'api/Device');

                    if (!$deviceResponse->successful()) {
                        // dd("ln-246" . $deviceResponse->body());
                        return Redirect::route($modules['route'] . '.create')
                            ->withErrors('Device fetch failed : ' . $deviceResponse->body());
                    }

                    $deviceData = $deviceResponse->json();

                    $device_id = $deviceData[0]['DeviceKey'] ?? null;

                    $deviceKey = $device_id;

                    $uploadUserPayload = [
                        [
                            "UserId" => (string) $biometricId,
                            "UserName" => $student_name ?? '',
                            "CardNumber" => (string) ($admission->aadhar_card_no ?? ''),
                            "Password" => $admission->mobile_no ?? '',
                            "AccessLevel" => "USER",
                            "DeviceKeys" => [
                                $deviceKey
                            ],
                            "OnlineEnrollment" => false,
                            "UploadBiometricDataIfAvailable" => true,
                            "FingerPrintUpload" => false,
                            "FaceUpload" => false,
                            "CardUpload" => false,
                            "PasswordUpload" => false
                        ]
                    ];

                    $uploadUserResponse = Http::withHeaders([
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Bearer ' . $token,
                    ])->post($modules['api_ip'] . 'api/DeviceCommand/UploadUser', $uploadUserPayload);

                    // dd($uploadUserResponse->body(), $uploadUserResponse->successful());


                    if (!$uploadUserResponse->successful()) {
                        return Redirect::route($modules['route'] . '.index')
                            ->withErrors('Admission created and synced, but device upload failed: ' . $uploadUserResponse->body());
                    }
                    if (Helper::directCan('course-register-edit')) {
                        return Redirect::route('cource-registration.edit', [ 'register_id' => $CourceRegistration->id])->withSuccess($modules['title'] . ' updated successfully');
                    }
                    return Redirect::route($modules['route'] . '.index')->withSuccess($modules['title'] . ' updated successfully');
                } else {
                    return Redirect::back()
                        ->withErrors('Admission updated, but API sync failed: ' . $apiResponse->body());
                }
            }
                */



            $CourceRegistration = CourceRegistration::where('register_id', $admission_id)->first();
            if (!$CourceRegistration) {

                Session::put('admission_id', $admission_id);
                if (Helper::directCan('course-register-create')) {
                    return Redirect::route('cource-registration.create', [$admission_id])->with('success', $admission->first_name . ' has been successfully admitted.');
                }
                return Redirect::route($modules['route'] . '.index')->with('success', $modules['title'] . ' updated successfully');

                return Redirect::back()->withErrors('Register id not found on course.');
            }

            if (Helper::directCan('course-register-edit')) {
                return Redirect::route('cource-registration.edit', [
                    'register_id' => $CourceRegistration->id
                ])->withSuccess($modules['title'] . ' updated successfully');
            }
            return Redirect::route($modules['route'] . '.index')->with('success', $modules['title'] . ' updated successfully');
        } catch (\Exception $e) {
            return $e->getMessage();
            return Redirect::back()->withErrors($e->getMessage())->withInput();
        }
    }


    public function education_detail_delete(Request $request)
    {
        $id = $request->id;
        $EducationData = EducationDetails::find($id);
        if ($EducationData) {
            $EducationData->delete();
            return $this->sendResponse([], 'Education Details removed successfully!');
        }
        return $this->sendError('Education Details not found!');
    }

    public function destroy(Request $request, string $id)
    {
        $modules = $this->modules;
        $modules['login_user_role'] = Helper::getLoginUserRole();
        $modules['permission_delete'] = Helper::directCan($modules['permission_prefix'] . '-delete');
        if (!$modules['permission_delete']) {
            if (isset($request) && $request->ajax()) {
                return $this->sendError('User does not have the right permissions.', [], [], 403);
            }
            abort(403, 'User does not have the right permissions.');
        }
        $isAjax = $request->ajax();

        try {
            $dataDelete = Admission::findOrFail($id);

            // Soft delete logic
            $dataDelete->deleted_by = Auth::user()?->id;
            $dataDelete->save();

            if ($dataDelete->delete()) {
                if ($isAjax) {
                    return response()->json([
                        'success' => true,
                        'message' => $modules['title'] . ' deleted successfully.',
                    ]);
                }

                return redirect()->route($modules['route'] . '.index')
                    ->withSuccess($modules['title'] . ' deleted successfully.');
            }

            if ($isAjax) {
                return response()->json([
                    'success' => false,
                    'message' => 'Something went wrong, please try again later.',
                ]);
            }

            return redirect()->route($modules['route'] . '.index')
                ->withErrors('Something went wrong, please try again later.');
        } catch (\Exception $e) {
            if ($isAjax) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ]);
            }

            return redirect()->route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }

    public function status_update(Request $request)
    {
        $isAjax = ($request->ajax()) ? true : false;

        $modules = $this->modules;
        $validator = Validator::make($request->all(), [
            'id' => ['required', Rule::exists($modules['table_name'], 'id')],
            'update_status' => ['required', 'in:running,completed,cancel']
        ]);

        if ($validator->fails() && $isAjax) {
            return $this->sendError($validator->messages()->first(), $validator->messages(), [], 401);
        }

        try {
            $designation = Admission::withTrashed()->findOrFail($request?->id);

            if ($designation) {
                $designation->status = $request->update_status;
                $designation->save();
                if ($isAjax) {
                    return $this->sendResponse($designation, $modules['title'] . ' status update successfully.');
                }
                return Redirect::route($modules['route'] . '.index')->withSuccess($modules['title'] . ' status update successfully.');
            }
            if ($isAjax) {
                return $this->sendError('something went wrong please try again later');
            }
            return Redirect::back()->withErrors('something went wrong please try again later')->withInput();
        } catch (\Exception $e) {
            if ($isAjax) {
                return $this->sendError($e->getMessage(), $e->getMessage(), [], 401);
            }
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }

    public function restore($id)
    {

        $modules = $this->modules;

        try {

            $restore_data = Admission::withTrashed()->findOrFail($id);
            $restore_data->restore();

            return Redirect::back()->withSuccess($modules['title'] . ' restored successfully!');
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }

    //  =================== Main Folder Ni ander Multipal Folder create krva  no code niche no che vrundavan computer line -462 thi start thay che ==============================
    public function createFolder(Request $request)
    {
        $admission = Admission::findOrFail($request->id);

        try {
            $folderName = $admission->id . '- ' . $admission->last_name . ' ' . $admission->first_name . ' ' . $admission->father_name;

            $parentFolderId = '1pG7EV04G2A0fR7M9AF0ZppiY_gBhjsxI'; // Main Student Folder ID

            $folderData = \App\Helpers\GoogleDriveHelper::createFolder($folderName, $parentFolderId);

            $admission->update([
                'gdrivefolderid'   => $folderData['id'] ?? null,
                'gdrivefoldername' => $folderData['name'] ?? $folderName,
                'gdrivefolderurl'  => $folderData['link'] ?? null,
            ]);

            return response()->json(['success' => true, 'message' => 'Folder created successfully']);
        } catch (\Exception $e) {
            \Log::error("Google Drive folder creation failed: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // private function getOrCreateFolder(Drive $service, string $folderName): string
    // {
    //     $query = "mimeType='application/vnd.google-apps.folder' and name='$folderName' and trashed=false";
    //     $results = $service->files->listFiles([
    //         'q' => $query,
    //         'fields' => 'files(id, name)',
    //     ]);

    //     $files = $results->getFiles();

    //     if (empty($files)) {
    //         $fileMetadata = new DriveFile([
    //             'name' => $folderName,
    //             'mimeType' => 'application/vnd.google-apps.folder',
    //         ]);

    //         $folder = $service->files->create($fileMetadata, [
    //             'fields' => 'id',
    //         ]);

    //         return $folder->id;
    //     } else {
    //         return $files[0]->id;
    //     }
    // }

    // ========================= line no - 545 lagin che Folder crate thay che ane te folder  ni ander multipal Folder create thay code End ==============================


    public function renameFolder(Request $request)
    {
        $modules = $this->modules;
        $validator = Validator::make($request->all(), [
            'id' => ['required', Rule::exists($modules['table_name'], 'id')],
            'foldername' => ['required'],
            'OldfolderName' => ['required'],
            'OldfolderId' => ['required']
        ]);

        $folderName = $request->input('name', $request->foldername);
        $OldfolderName = $request->OldfolderName;
        $OldfolderId = $request->OldfolderId;

        $client = new Google_Client();
        $client->setClientId(config('filesystems.disks.google.clientId'));
        $client->setClientSecret(config('filesystems.disks.google.clientSecret'));
        $client->refreshToken(config('filesystems.disks.google.refreshToken'));


        $service = new Drive($client);

        $fileMetadata = new DriveFile([
            'name' => $folderName
        ]);

        try {

            $folder = $service->files->update($OldfolderId, $fileMetadata, [
                'fields' => 'id, name'
            ]);

            $folderId = $folder->id;
            $folderUrl = "https://drive.google.com/drive/folders/{$folderId}";

            $createfolder = Admission::withTrashed()->findOrFail($request?->id);
            $createfolder->gdrivefolderurl = $folderUrl;
            $createfolder->gdrivefolderid = $folderId;
            $createfolder->gdrivefoldername = $folder->name;
            $createfolder->save();

            return response()->json([
                'folder_id' => $folderId,
                'folder_name' => $folder->name,
                'folder_url' => $folderUrl
            ]);
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }
    public function getAdmissionDetails($id)
    {
        $admission = Admission::find($id);

        if ($admission) {
            return response()->json([
                'first_name' => $admission->first_name,
                'last_name' => $admission->last_name,

            ]);
        }

        return response()->json(['error' => 'Admission not found'], 404);
    }

    public function aadharCardNoToData(Request $request)
    {
        $aadhar = $request->aadhar;
        $user = Admission::where('aadhar_card_no', $aadhar)->first();

        // If no admission found, no redirect, just say no user
        if (!$user) {
            return response()->json([
                'redirect' => false,
                'message' => 'No admission found with this Aadhaar number.'
            ]);
        }

        // Check if course registration exists
        $hasCourseRegistration = CourceRegistration::where('register_id', $user->id)->exists();

        if ($hasCourseRegistration) {
            // Course registration exists
            return response()->json([
                'redirect' => true,
                'redirect_url' => route('cource-registration.create', ['admission_id' => $user->id]),
                'message' => "<div style='text-align:left; font-size:16px; line-height:1.7em;'>
                                <strong>Name:</strong> {$user->first_name} {$user->last_name}<br>
                                <strong>Father Name:</strong> {$user->father_name}<br>
                                <strong>Aadhaar:</strong> {$user->aadhar_card_no}<br>
                                <strong>ID:</strong> {$user->id}<br>
                                You already have a course registration.
                              </div>",
            ]);
        } else {
            return response()->json([
                'redirect' => true,
                'redirect_url' => route('cource-registration.create', ['admission_id' => $user->id]),
                'message' => "<div style='text-align:left; font-size:16px; line-height:1.7em;'>
                                <strong>Name:</strong> {$user->first_name} {$user->last_name}<br>
                                <strong>Father Name:</strong> {$user->father_name}<br>
                                <strong>Aadhaar:</strong> {$user->aadhar_card_no}<br>
                                <strong>ID:</strong> {$user->id}<br>
                                Admission form complete but course registration not done yet.
                              </div>",
            ]);
        }
    }




    public function courseFeesGet(Request $request)
    {
        $data = CourceRegistration::join('master_course', 'cource_registration.course_id', '=', 'master_course.id')
            ->where('cource_registration.register_id', $request->register_id)
            ->where('cource_registration.course_id', $request->course_id)
            ->select('master_course.course_name as course_name', 'cource_registration.fee')
            ->first();

        return response()->json($data);
    }

    public function courseFeesUpdate(Request $request)
    {
        $request->validate([
            'register_id' => 'required|integer',
            'course_id' => 'required|integer',
            'fee' => 'required|numeric'
        ]);

        $registration = CourceRegistration::where('register_id', $request->register_id)
            ->where('course_id', $request->course_id)
            ->first();

        if ($registration) {
            $registration->fee = $request->fee;
            $registration->save();

            return response()->json(['message' => 'Course Fee Updated successfully.']);
        } else {
            return response()->json(['message' => 'Record not found.'], 404);
        }
    }

    public function updateCourseStatus(Request $request)
    {
        $user = Auth::user();
        if (!$user || !(method_exists($user, 'hasRole') && ($user->hasRole('super-admin') || $user->hasRole('developer')))) {
            return $this->sendError('Only Super-admin can update the status.', [], [], 403);
        }

        $request->validate([
            'register_id' => 'required|integer',
            'course_id'   => 'required|integer',
            'status'      => 'required|in:Running,Completed,Cancel',
        ]);

        $course = CourceRegistration::where('register_id', $request->register_id)
            ->where('course_id', $request->course_id)
            ->first();

        if (!$course) {
            return $this->sendError('Course not found.');
        }

        $course->status = $request->status;
        $course->updated_at = now();
        $course->save();

        return $this->sendResponse($course, 'Status updated successfully.');
    }
    public function bonafideCertificate(Request $request, $id)
    {
        $admission = Admission::findOrFail($id);
        $courseRegistration = CourceRegistration::where('register_id', $id)
            ->with('course')
            ->latest('id')
            ->first();

        $certificateData = $this->resolveCertificateRequestData($request);

        return view($this->modules['folder_path'] . '.bonafide-certificate', compact('admission', 'courseRegistration', 'certificateData'));
    }

    public function transferCertificate(Request $request, $id)
    {
        $admission = Admission::findOrFail($id);
        $courseRegistration = CourceRegistration::where('register_id', $id)
            ->with(['course', 'class'])
            ->latest('id')
            ->first();

        $certificateData = $this->resolveCertificateRequestData($request);

        return view($this->modules['folder_path'] . '.transfer-certificate', compact('admission', 'courseRegistration', 'certificateData'));
    }

    public function englishMediumCertificate(Request $request, $id)
    {
        $admission = Admission::findOrFail($id);
        $courseRegistration = CourceRegistration::where('register_id', $id)
            ->with('course')
            ->latest('id')
            ->first();

        $certificateData = $this->resolveCertificateRequestData($request);

        return view($this->modules['folder_path'] . '.english-medium-certificate', compact('admission', 'courseRegistration', 'certificateData'));
    }

    public function letterOfRecommendation(Request $request, $id)
    {
        $admission = Admission::findOrFail($id);
        $courseRegistration = CourceRegistration::where('register_id', $id)
            ->with('course')
            ->latest('id')
            ->first();

        $certificateData = $this->resolveCertificateRequestData($request);

        return view($this->modules['folder_path'] . '.letter-of-recommendation', compact('admission', 'courseRegistration', 'certificateData'));
    }

    public function view($register_id, $course_id)
    {
        $modules = $this->modules;
        $admission = Admission::findOrFail($register_id);

        $course = CourceRegistration::where('register_id', $register_id)
            ->where('course_id', $course_id)
            ->with('course')
            ->firstOrFail();

        $mother_letters = str_split(strtoupper($admission->mother_name));

        $full_name = strtoupper($admission->first_name . ' ' . $admission->last_name . ' ' . $admission->father_name);
        $full_name_letters = str_split($full_name);

        $permanent_address_letters = str_split(strtoupper($admission->permanent_address ?? ''));


        View::share('modules', $modules);
        View::share('mother_letters', $mother_letters);
        View::share('full_name_letters', $full_name_letters);
        View::share('permanent_address_letters', $permanent_address_letters);

        // dd($course);
        return view($modules['folder_path'] . '.view', compact('admission', 'course'));
    }
    public function checkAadharDuplicate(Request $request)
    {
        $aadharCardNo = $request->input('aadhar_card_no');
        $editId = $request->input('edit_id');

        // Remove spaces
        $aadharCardNo = str_replace(' ', '', $aadharCardNo);

        $query = Admission::where('aadhar_card_no', $aadharCardNo);

        if ($editId) {
            $query->where('id', '!=', $editId);
        }

        $exists = $query->exists();

        return response()->json([
            'exists' => $exists,
            'message' => $exists ? 'Aadhaar card number already exists' : ''
        ]);
    }

    public function checkMobileDuplicate(Request $request)
    {
        $mobileNo = $request->input('mobile_no');
        $editId = $request->input('edit_id');

        $query = Admission::where('mobile_no', $mobileNo);

        if ($editId) {
            $query->where('id', '!=', $editId);
        }

        $exists = $query->exists();

        return response()->json([
            'exists' => $exists,
            'message' => $exists ? 'Mobile number already exists' : ''
        ]);
    }
    public function import()
    {
        try {
            $modules = $this->modules;
            return view($modules['folder_path'] . '.import');
        } catch (\Exception $e) {
            return Redirect::route('software.dashboard')->withErrors($e->getMessage());
        }
    }

    public function importStore(Request $request)
    {
        $request->validate([
            'import_file' => 'required|mimes:xlsx,xls,csv'
        ]);

        try {
            $import = new StudentImport();
            Excel::import($import, $request->file('import_file'));

            // Get report from import class
            $report = $import->report;

            // Save report in session so blade can show it
            Session::flash('import_report', $report);


            $message = "Import finished — Inserted: " . count($report['inserted']) .
                ", Skipped: " . count($report['skipped']) .
                ", Errors: " . count($report['errors']);

            return back()->with('success', $message);
        } catch (\Exception $e) {
            return back()->withErrors($e->getMessage());
        }
    }

    /**
     * Export admissions to Excel.
     */
    public function exportExcel(Request $request)
    {
        $this->authorizeAdmissionList();

        $scope = strtolower($request->get('scope', 'filtered')) === 'all' ? 'all' : 'filtered';
        $selectedColumns = $this->resolveSelectedColumns($request->input('columns', []));
        $columnLabels = $this->mapColumnLabels($selectedColumns);

        $admissions = $this->buildAdmissionExportQuery($request, $scope)->with('courses.course')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        foreach (array_values($columnLabels) as $index => $label) {
            $sheet->setCellValueByColumnAndRow($index + 1, 1, $label);
        }

        $rowNumber = 2;
        foreach ($admissions as $admission) {
            foreach ($selectedColumns as $columnIndex => $columnKey) {
                $sheet->setCellValueByColumnAndRow(
                    $columnIndex + 1,
                    $rowNumber,
                    $this->formatColumnValueForExcel($admission, $columnKey)
                );
            }
            $rowNumber++;
        }

        if (!empty($selectedColumns)) {
            for ($i = 1; $i <= count($selectedColumns); $i++) {
                $columnLetter = Coordinate::stringFromColumnIndex($i);
                $sheet->getColumnDimension($columnLetter)->setAutoSize(true);
            }
        }

        $fileName = 'admission-' . now()->format('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Print view for admissions.
     */
    public function printView(Request $request)
    {
        $this->authorizeAdmissionList();

        $scope = strtolower($request->get('scope', 'filtered')) === 'all' ? 'all' : 'filtered';
        $selectedColumns = $this->resolveSelectedColumns($request->input('columns', []));
        $columnLabels = $this->mapColumnLabels($selectedColumns);
        $searchTerm = $scope === 'filtered' ? trim((string) $request->get('search', '')) : null;

        $admissions = $this->buildAdmissionExportQuery($request, $scope)->with('courses.course')->get();

        return view($this->modules['folder_path'] . '.print', [
            'admissions' => $admissions,
            'columnLabels' => $columnLabels,
            'columnKeys' => $selectedColumns,
            'scope' => $scope,
            'searchTerm' => $searchTerm,
            'generatedAt' => now(),
        ]);
    }

    /**
     * Permission check for admission list.
     */
    protected function authorizeAdmissionList(): void
    {
        $canList = Helper::directCan($this->modules['permission_prefix'] . '-list');
        if (!$canList) {
            abort(403, 'User does not have the right permissions.');
        }
    }

    protected function resolveCertificateRequestData(Request $request): array
    {
        $today = now();

        return [
            'issue_date' => $this->formatCertificateDate($request->issue_date, $today->format('j-m-Y')),
            'academic_year' => trim((string) $request->academic_year) !== ''
                ? trim((string) $request->academic_year)
                : $today->format('Y'),
            'period_from' => $this->formatCertificateDate($request->semester_start, ''),
            'period_to' => $this->formatCertificateDate($request->semester_end, ''),
            'recommender_name' => $request->recommender_name ?? 'Hitesh Vadalia',
            'recommender_designation' => $request->recommender_designation ?? 'I/C. Principal',
            'recommender_type' => $request->recommender_type ?? 'principal',
            'faculty_id' => $request->faculty_id ?? null,
        ];
    }

    protected function formatCertificateDate($value, string $default = ''): string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return $default;
        }

        $formats = ['d-m-Y', 'j-m-Y', 'Y-m-d', 'd/m/Y', 'j/n/Y'];

        foreach ($formats as $format) {
            try {
                return \Carbon\Carbon::createFromFormat($format, $value)->format('j-m-Y');
            } catch (\Exception $e) {
                continue;
            }
        }

        try {
            return \Carbon\Carbon::parse($value)->format('j-m-Y');
        } catch (\Exception $e) {
            return $default;
        }
    }

    /**
     * Build filtered query for export/print.
     */
    protected function buildAdmissionExportQuery(Request $request, string $scope)
    {
        $query = Admission::query()->withTrashed()->orderByDesc('id');

        if ($request->filled('course_id')) {
            $courseId = $request->input('course_id');
            $query->whereHas('courses', function ($q) use ($courseId) {
                $q->where('course_id', $courseId);
            });
        }

        if ($request->filled('batch_id')) {
            $batchId = $request->input('batch_id');
            $query->whereHas('courses', function ($q) use ($batchId) {
                $q->where('batch_id', $batchId);
            });
        }

        if ($request->filled('class_id')) {
            $classId = $request->input('class_id');
            $query->whereHas('courses', function ($q) use ($classId) {
                $q->where('class_id', $classId);
            });
        }

        if ($scope === 'all') {
            return $query;
        }

        $search = trim((string) $request->get('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('father_name', 'like', "%{$search}%")
                    ->orWhere('mobile_no', 'like', "%{$search}%")
                    ->orWhere('aadhar_card_no', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return $query;
    }

    protected function resolveSelectedColumns($columns): array
    {
        if (!is_array($columns) || empty($columns)) {
            return $this->defaultExportColumns;
        }

        $validColumns = array_keys($this->exportableColumns);
        $filtered = array_values(array_filter($columns, function ($column) use ($validColumns) {
            return in_array($column, $validColumns, true);
        }));

        return !empty($filtered) ? $filtered : $this->defaultExportColumns;
    }

    protected function mapColumnLabels(array $selectedColumns): array
    {
        $labels = [];
        foreach ($selectedColumns as $columnKey) {
            $labels[$columnKey] = $this->exportableColumns[$columnKey] ?? ucfirst(str_replace('_', ' ', $columnKey));
        }
        return $labels;
    }

    protected function formatColumnValueForExcel(Admission $admission, string $columnKey)
    {
        switch ($columnKey) {
            case 'id':
                return $admission->id;
            case 'biometric_id':
                return $admission->biometric_id ?? '-';
            case 'full_name':
                $first = $admission->first_name ?? '';
                $last = $admission->last_name ?? '';
                $father = $admission->father_name ?? '';
                $fullName = trim("{$first} {$last} {$father}");
                return $fullName !== '' ? $fullName : '-';
            case 'date_of_birth':
                return $admission->date_of_birth
                    ? \Carbon\Carbon::parse($admission->date_of_birth)->format('d-m-Y')
                    : '-';
            case 'gender':
                return $admission->gender ?? '-';
            case 'mobile_no':
                return $admission->mobile_no ?? '-';
            case 'parent_mobile_no':
                return $admission->parent_mobile_no ?? '-';
            case 'other_mobile_no':
                return $admission->other_mobile_no ?? '-';
            case 'whatsapp_no':
                return $admission->whatsapp_no ?? '-';
            case 'email_address':
                return $admission->email_address ?? '-';
            case 'aadhar_card_no':
                return $admission->aadhar_card_no ?? '-';
            case 'status':
                return $admission->status ? ucfirst($admission->status) : '-';
            case 'created_at':
                return $admission->created_at
                    ? $admission->created_at->format('d-m-Y')
                    : '-';
            case 'course_names':
                $courseNames = $admission->courses
                    ->map(fn($cr) => $cr->course?->course_name)
                    ->filter()
                    ->unique()
                    ->join(', ');
                return $courseNames ?: '-';
            default:
                return data_get($admission, $columnKey, '-');
        }
    }
}
