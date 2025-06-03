<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admission;
use App\Models\EducationDetails;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use App\Http\Requests\AdmissionRequest;
use App\Models\CourceRegistration;
use App\Models\Master\MasterCourse;
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

use Illuminate\Support\Facades\Storage;

class AdmissionController extends Controller
{
    public $modules = [];
    public function __construct()
    {
        $this->modules = [
            'title' => 'Admission',
            'folder_path' => 'software.module.admission',
            'route' => 'admission',
            'table_name' => (new Admission())->getTable(),
            'permisstion_prefix' => 'admission',
        ];

        // $this->middleware('permission:'.$this->modules['permisstion_prefix'].'-list', ['only' => ['index','show']]);
        // $this->middleware('permission:'.$this->modules['permisstion_prefix'].'-create', ['only' => ['create','store']]);
        // $this->middleware('permission:'.$this->modules['permisstion_prefix'].'-edit', ['only' => ['edit','update']]);
        // $this->middleware('permission:'.$this->modules['permisstion_prefix'].'-delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $modules = $this->modules;
        View::share('modules', $modules);

        try {
            $columns = [
                (object)['data' => "id", 'name' => 'id', 'td_label' => 'Register No.'],
                (object)['data' => "admission_name", 'name' => 'first_name', 'td_label' => 'Name'],
                (object)['data' => "course_button", 'name' => 'course_button', 'td_label' => 'Course'],
                (object)['data' => "view_button", 'name' => 'view_button', 'td_label' => 'Student Info'],
                (object)['data' => "gdrivefolderurl", 'name' => 'gdrivefolderurl', 'td_label' => 'Google Drive Folder', 'className' =>  'w-5 text-center'],
                (object)['data' => "status", 'name' => 'status', 'td_label' => 'Status', 'className' =>  'w-5 text-center', 'width' => '10%'],
                (object)['data' => "action", 'name' => 'action', 'td_label' => 'Action', 'orderable' => false, 'searchable' => false, 'className' =>  'w-10 text-center'],
            ];
            View::share("columns", $columns);

            $data = [];

            if ($request->ajax()) {
                $data = Admission::withTrashed()->orderBy('id', 'desc');

                $editPermisstion = true;
                $deletePermisstion = false;

                return Datatables::of($data)
                    ->addIndexColumn()

                    ->filter(function ($query) use ($request) {
                    if ($request->has('search')) {
                            $query->orWhere('first_name', 'like', "%" . $request->search . "%");
                        }
                    })

                    ->editColumn('admission_name', function ($row) {
                    $first = $row->first_name ?? '';
                    $father = $row->father_name ?? '';
                    $last = $row->last_name ?? '';
                        return trim("{$first} {$father} {$last}");
                    })

                    ->editColumn('gdrivefolderurl', function ($row) {
                        $folderName = $row->id . '-' . $row->first_name . '-' . $row->father_name . '-' . $row->last_name;
                    return '<a href="javascript:void(0)" data-id="' . $row->id . '" data-foldername="' . $folderName . '" data-folderurl="' . $row->gdrivefolderurl . '" class="btn btn-warning btn-sm open_google_drive_folder"><i class="bx bx-copy-alt"></i> Open Doucuments</a>';
                    })

                    ->editColumn('status', function ($row) use ($modules) {
                    $dropdown = '<ul class="dropdown-menu">';
                    $button = '';

                        if ($row->status == "created") {
                        $button = '<button type="button" class="btn btn-success btn-sm w-100 dropdown-toggle" data-bs-toggle="dropdown">' . ucfirst($row->status) . '</button>';
                        $dropdown .= '<li><a href="javascript:void(0)" class="dropdown-item update-status" data-url="' . route($modules["route"] . '.status-update') . '" data-id="' . $row->id . '" data-update_status="rejected">Reject</a></li>';
                        } elseif ($row->status == "rejected") {
                        $button = '<button type="button" class="btn btn-danger btn-sm w-100 dropdown-toggle" data-bs-toggle="dropdown">' . ucfirst($row->status) . '</button>';
                        $dropdown .= '<li><a href="javascript:void(0)" class="dropdown-item update-status" data-url="' . route($modules["route"] . '.status-update') . '" data-id="' . $row->id . '" data-update_status="created">Re-Create</a></li>';
                        }

                    $dropdown .= '</ul>';
                    return $button . $dropdown;
                    })

                    ->addColumn('course_button', function ($row) {
                        return '<button class="btn btn-primary btn-sm course-btn" data-id="' . $row->id . '" data-name="' . $row->first_name . '">Course</button>';
                    })

                    ->addColumn('view_button', function ($row) {
                        return '<button class="btn btn-success btn-sm view-btn" data-id="' . $row->id . '" data-name="' . $row->first_name . '" data-mobile="' . $row->mobile_no . '">View</button>';
                    })

                    ->addColumn('action', function ($row) use ($modules, $editPermisstion, $deletePermisstion) {
                        $btn = '';
                    if (!$row->deleted_at) {
                            if ($editPermisstion) {
                            $btn .= '<a href="' . route($modules["route"] . ".edit", [$row->id]) . '" class="btn btn-light btn-icon mx-1"><i class="fa-solid fa-pen-to-square"></i></a>';
                            }
                            if ($deletePermisstion) {
                            $btn .= '<a href="javascript:void(0)" data-id="' . $row->id . '" data-did="' . route($modules["route"] . ".destroy", [$row->id]) . '" class="btn btn-danger btn-icon deletebutton mx-1"><i class="fa-solid fa-trash"></i></a>';
                            }
                        } else {
                        $btn .= '<a href="javascript:void(0)" data-restore="' . route($modules["route"] . ".restore", ['id' => $row->id]) . '" class="btn btn-light mx-1 record-restore"><i class="ti ti-history"></i> Restore</a>';
                        }

                    return $btn ?: '-';
                    })

                    ->rawColumns(['status', 'action', 'gdrivefolderurl', 'admission_name', 'course_button', 'view_button'])
                    ->make(true);
            }

            return view($modules['folder_path'] . '.index', compact('data'));
        } catch (\Exception $e) {
            return Redirect::route('dashboard')->withErrors($e->getMessage());
        }
    }

    public function create()
    {
        $modules = $this->modules;
        View::share('modules', $modules);
        return view($modules['folder_path'] . '.form', compact('modules'));
    }

    public function show(Request $request)
    {
        $student = Admission::withTrashed()->find($request->id);
        $studentCourse = CourceRegistration::with('course')->withTrashed()->where('register_id', $request->id)->first();


        if (!$student && !$studentCourse) {
            return response()->json(['success' => false, 'message' => 'Student and Course not found.']);
        }

        return response()->json([
            'success' => true,
            'student' => $student,
            'course' => $studentCourse,

        ]);
    }

    public function store(AdmissionRequest $request)
    {
        $modules = $this->modules;
        // $modules['addPermission'] = Gate::check('hasPermission', ['add',$modules['module_name']]);

        // if(!$modules['addPermission']){
        //     abort(403, 'Unauthorized');
        // }



        View::share('modules', $modules);

        $validated = $request->validated();
        // return $request->all();


        try {

            $loginUserId = Auth::user()->id;

            // return $this->adminLoginUser;
            $validated['created_by'] = $loginUserId;
            // return $validated;
            $admission = Admission::create($validated);
            $admission_id = $admission->id;

            $education = $request->education;
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

            return Redirect::route('cource-registration.create', ['id' => $admission_id])
                ->withSuccess($modules['title'] . ' New Admission created successfully');
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }

    public function edit(string $id)
    {
        try {
            $modules = $this->modules;
            View::share('modules', $modules);

            $edit = Admission::findOrFail($id);

            $education = EducationDetails::where('admission_id', $id)->get();
            View::share('education', $education);

            return view($modules['folder_path'] . '.form', compact('modules', 'edit'));
        } catch (\Exception $e) {
            return view($modules['folder_path'] . '.form', compact('modules', 'edit'));
        } catch (\Exception $e) {
            return Redirect::route('dashboard')->withErrors($e->getMessage());
        }
    }

    // public function update(AdmissionRequest $request, string $id)
    // {
    //     $modules = $this->modules;
    //     // return $request->all();
    //     $request['id'] = $id;

    //     $validated = $request->validated();
    //     try {
    //         $validated['updated_by'] = Auth::user()->id;
    //         $admission = Admission::create($validated);
    //         $admission_id = $admission->id;

    //         // return $validated;
    //         $updateData = Admission::findOrFail($id);
    //         if ($updateData) {
    //             unset($validated['id']);
    //             $updateData->update($validated);

    //             if ($request->has('education_id')) {


    //                 // EducationDetails::where('admission_id', $id)->forceDelete();

    //                 $education = $request->education;
    //                 foreach ($education as $key => $value) {

    //                     $education_id = $request->education_id[$key];
    //                     $EducationupdateData = EducationDetails::find($education_id);
    //                     if ($EducationupdateData) {
    //                         $EducationupdateData->update([
    //                             'admission_id' => $id,
    //                             'education' => $value,
    //                             'percentage_cgpa' => $request->percentage_cgpa[$key],
    //                             'seat_no_nrollment_no' => $request->seat_no_nrollment_no[$key],
    //                             'board_university' => $request->board_university[$key],
    //                             'passing_year' => $request->passing_year[$key],
    //                             'school_name_college_name' => $request->school_name_college_name[$key],
    //                         ]);
    //                     } else {
    //                         EducationDetails::create([
    //                             'admission_id' => $id,
    //                             'education' => $value,
    //                             'percentage_cgpa' => $request->percentage_cgpa[$key] ?? 0,
    //                             'seat_no_nrollment_no' => $request->seat_no_nrollment_no[$key] ?? '',
    //                             'board_university' => $request->board_university[$key],
    //                             'passing_year' => $request->passing_year[$key] ?? 0,
    //                             'school_name_college_name' => $request->school_name_college_name[$key],
    //                         ]);
    //                     }
    //                 }
    //             }

    //             Redirect::route('cource-registration.update', $admission->id)
    //                 ->withSuccess($modules['title'] . 'Admission update successfully');


    //             // return Redirect::route($modules['route'] . '.index')->withSuccess($modules['title'] . '  successfully');
    //         }
    //         return Redirect::back()->withErrors('something went wrong please try again later')->withInput();
    //     } catch (\Exception $e) {
    //         return Redirect::route('software.dashboard')->withErrors($e->getMessage());
    //     }
    // }

    public function update(AdmissionRequest $request, string $register_id)
    {
        $modules = $this->modules;
        $validated = $request->validated();
        try {
            $validated['updated_by'] = Auth::id();

            $CourceRegistration = CourceRegistration::where('register_id', $register_id)->firstOrFail();

            $admission = Admission::where('id', $CourceRegistration->register_id)->firstOrFail();

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

            return Redirect::route('cource-registration.edit', [
                'register_id' => $CourceRegistration->id
            ])->withSuccess($modules['title'] . ' updated successfully');
        } catch (\Exception $e) {
            // return $e->getMessage();
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
            'update_status' => ['required', 'in:created,rejected,completed,cancelled']
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

    public function createFolder(Request $request)
    {
        $modules = $this->modules;
        $validator = Validator::make($request->all(), [
            'id' => ['required', Rule::exists($modules['table_name'], 'id')],
            'foldername' => ['required']
        ]);

        $folderName = $request->input('name', $request->foldername);

        $client = new Google_Client();
        $client->setClientId(config('filesystems.disks.google.clientId'));
        $client->setClientSecret(config('filesystems.disks.google.clientSecret'));
        $client->refreshToken(config('filesystems.disks.google.refreshToken'));


        $service = new Drive($client);

        $fileMetadata = new DriveFile([
            'name' => $folderName,
            'mimeType' => 'application/vnd.google-apps.folder',
        ]);

        try {

            $folder = $service->files->create($fileMetadata, [
                'fields' => 'id, name'
            ]);

            $folderId = $folder->id;
            $folderUrl = "https://drive.google.com/drive/folders/{$folderId}";

            $createfolder = Admission::withTrashed()->findOrFail($request?->id);
            $createfolder->gdrivefolderurl = $folderUrl;
            $createfolder->gdrivefolderid = $folderId;
            $createfolder->gdrivefoldername = $folder->name;
            $createfolder->save();

            return Redirect::route($modules['route'] . '.index')->withSuccess($folder->name  . ' Create Google Drive Folder successfully!');
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }


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

    // public function getAdmissionDetails($id)
    // {
    //     $admission = Admission::with('courceRegistration')->find($id);

    //     if ($admission) {
    //         return response()->json([
    //             'first_name' => $admission->first_name,
    //             'last_name'  => $admission->last_name,
    //             'course_id'  => optional($admission->courceRegistration)->course_id,
    //             'date'       => optional($admission->courceRegistration)->date,
    //         ]);
    //     }

    //     return response()->json(['error' => 'Admission not found'], 404);
    // }

    public function aadharCardNoStudentData(Request $request)
    {
        $aadhar = $request->input('aadhar');

        // Fetch the user with Aadhaar
        $user = Admission::where('aadhar_card_no', $aadhar)->first();

        if ($user) {

            $educationDetails = EducationDetails::where('admission_id', $user->id)->get();

            return response()->json([
                'user' => [
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'father_name' => $user->father_name,
                    'mother_name' => $user->mother_name,
                    'mobile_no' => $user->mobile_no,
                    'email' => $user->email_address,
                    'date_of_birth' => $user->date_of_birth,
                    'gender' => $user->gender,
                    'category' => $user->category,
                    'cast' => $user->cast,
                    'enrolment_no' => $user->enrolment_no,
                    'spid' => $user->spid,
                    'occupation' => $user->occupation,
                    'status' => $user->status,
                    'temporary_address' => $user->temporary_address,
                    'permanent_address' => $user->permanent_address,
                    'parent_mobile_no' => $user->parent_mobile_no,
                    'other_mobile_no' => $user->other_mobile_no,
                    'whatsapp_no' => $user->whatsapp_no,
                    'apaari_id_abc_id' => $user->apaar_id_abc_id,
                ],
                'education_details' => $educationDetails
            ]);
        }

        return response()->json([]);
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
}
