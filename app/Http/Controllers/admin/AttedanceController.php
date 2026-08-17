<?php

namespace App\Http\Controllers\admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Attedance;
use App\Models\Admission;
use App\Models\CourceRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Requests\AttedanceRequest;
use App\Models\CourseRegister;
use App\Models\Master\MasterBatch;
use App\Models\Master\MasterClass;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Database\Eloquent\Builder;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Models\Master\MasterCourse;

class AttedanceController extends Controller
{
    public $modules = [];
    protected array $exportableColumns = [
        'biometric_id' => 'Biometric ID',
        'student_name' => 'Student Name',
        'date' => 'Date',
        'in_time' => 'Punch In Time',
        'out_time' => 'Punch Out Time',
        'device_key' => 'Device Key',
        'device_name' => 'Device Name',
    ];

    protected array $defaultExportColumns = [
        'biometric_id',
        'student_name',
        'date',
        'in_time',
        'out_time',
        'device_key',
        'device_name',
    ];
    public function __construct()
    {
        $this->modules = [
            'title' => 'Attendance',
            'folder_path' => 'software.module.attedance',
            'route' => 'attedance',
            'table_name' => (new Attedance())->getTable(),
            'permission_prefix' => 'attedance',
            'api_ip' => 'http://192.168.31.5:98/',
        ];

        // $this->middleware('permission:'.$this->modules['permission_prefix'].'-list', ['only' => ['index','show']]);
        // $this->middleware('permission:'.$this->modules['permission_prefix'].'-create', ['only' => ['create','store']]);
        // $this->middleware('permission:'.$this->modules['permission_prefix'].'-edit', ['only' => ['edit','update']]);
        // $this->middleware('permission:'.$this->modules['permission_prefix'].'-delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $modules = $this->modules;
        $modules['login_user_role'] = Helper::getLoginUserRole();
        $modules['permission_list'] = Helper::directCan($modules['permission_prefix'] . '-list');
        $modules['permission_add'] = Helper::directCan($modules['permission_prefix'] . '-create');
        $modules['permission_edit'] = Helper::directCan($modules['permission_prefix'] . '-edit');
        $modules['permission_delete'] = Helper::directCan($modules['permission_prefix'] . '-delete');
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
                (object)['data' => 'biometric_id', 'name' => 'biometric_id', 'td_label' => 'Biometric ID'],
                (object)['data' => 'student_name', 'name' => 'student_name', 'td_label' => 'Student Name', 'orderable' => false, 'searchable' => false],
                (object)['data' => 'date', 'name' => 'date', 'td_label' => 'Date'],
                (object)['data' => 'in_time', 'name' => 'in_time', 'td_label' => 'Punch In Time', 'orderable' => false, 'searchable' => false],
                (object)['data' => 'out_time', 'name' => 'out_time', 'td_label' => 'Punch Out Time', 'orderable' => false, 'searchable' => false],
                (object)['data' => 'DeviceKey', 'name' => 'DeviceKey', 'td_label' => 'Device Key', 'orderable' => false, 'searchable' => false],
                (object)['data' => 'DeviceName', 'name' => 'DeviceName', 'td_label' => 'Device Name', 'orderable' => false, 'searchable' => false],
            ];
            View::share("columns", $columns);

            $admission_id = $request->get('admission_id');
            View::share('admission_id', $admission_id);


            $data = [];

            if ($request->ajax()) {
                // dd($request->all());
                $latestRegIds = DB::table('cource_registration')
                    ->select('register_id', DB::raw('MAX(id) as max_id'))
                    ->whereNull('deleted_at')
                    ->groupBy('register_id');

                $data = Attedance::select([
                        'attedance.admission_id',
                        'attedance.biometric_id',
                        'attedance.date',
                        DB::raw('MIN(attedance.in_time) as first_punch'),
                        DB::raw('MAX(attedance.in_time) as last_punch'),
                        DB::raw('MIN(attedance.DeviceKey) as DeviceKey'),
                        DB::raw('MIN(attedance.DeviceName) as DeviceName'),
                    ])
                    ->join('admission', function ($join) {
                        $join->on('attedance.admission_id', '=', 'admission.id')
                             ->orOn('attedance.biometric_id', '=', 'admission.biometric_id');
                    })
                    ->join('cource_registration', function ($join) {
                        $join->on('admission.id', '=', 'cource_registration.register_id')
                             ->whereRaw('attedance.date >= cource_registration.date');
                    })
                    ->joinSub($latestRegIds, 'latest_reg', function ($join) {
                        $join->on('cource_registration.id', '=', 'latest_reg.max_id');
                    })
                    ->whereIn('cource_registration.status', ['active', 'Running'])
                    ->whereNull('admission.deleted_at')
                    ->whereNull('cource_registration.deleted_at')
                    ->with(['admission'])
                    ->groupBy('attedance.admission_id', 'attedance.biometric_id', 'attedance.date')
                    ->orderByDesc('attedance.date');

                if (!empty($admission_id)) {
                    $admission = Admission::find($admission_id);
                    if ($admission) {
                        $data->where(function ($q) use ($admission_id, $admission) {
                            $q->where('attedance.admission_id', $admission_id);
                            if (!empty($admission->biometric_id)) {
                                $q->orWhere('attedance.biometric_id', $admission->biometric_id);
                            }
                        });
                    } else {
                        $data->where('attedance.admission_id', $admission_id);
                    }
                }

                if ($request->filled('filter_course') || $request->filled('filter_batch') || $request->filled('filter_class')) {
                    $registrationQuery = CourceRegistration::query();

                    if ($request->filled('filter_course')) {
                        $registrationQuery->where('course_id', $request->filter_course);
                    }

                    if ($request->filled('filter_batch')) {
                        $registrationQuery->where('batch_id', $request->filter_batch);
                    }

                    if ($request->filled('filter_class')) {
                        $registrationQuery->where('class_id', $request->filter_class);
                    }

                    $admissionIds = $registrationQuery->pluck('register_id');
                    $data->whereIn('attedance.admission_id', $admissionIds);
                }

                $editPermission = true;
                $deletePermission = true;

                $returnData = Datatables::of($data)
                    ->addIndexColumn()
                    ->filter(function ($query) use ($request) {
                        $search = trim((string) ($request->input('search.value') ?? $request->search ?? ''));
                        if ($search === '') {
                            return;
                        }

                        $query->where(function ($q) use ($search) {
                            $q->where('attedance.biometric_id', 'like', "%{$search}%")
                                ->orWhere('attedance.date', 'like', "%{$search}%")
                                ->orWhere('attedance.DeviceKey', 'like', "%{$search}%")
                                ->orWhere('attedance.DeviceName', 'like', "%{$search}%")
                                ->orWhereHas('admission', function ($admissionQuery) use ($search) {
                                    $admissionQuery->where('first_name', 'like', "%{$search}%")
                                        ->orWhere('last_name', 'like', "%{$search}%")
                                        ->orWhere('father_name', 'like', "%{$search}%");
                                })
                                ->orWhereIn('attedance.biometric_id', function ($subQuery) use ($search) {
                                    $subQuery->select('biometric_id')
                                        ->from('admission')
                                        ->where('first_name', 'like', "%{$search}%")
                                        ->orWhere('last_name', 'like', "%{$search}%")
                                        ->orWhere('father_name', 'like', "%{$search}%");
                                });
                        });
                    })
                    ->editColumn('biometric_id', function ($row) {
                        if ($row->biometric_id) {
                            return $row->biometric_id;
                        }
                        if ($row->admission_id) {
                            return $row->admission?->biometric_id ?? $row->admission_id;
                        }
                        return '-';
                    })
                    ->addColumn('student_name', function ($row) {
                        $admission = $row->admission;
                        if (!$admission && $row->biometric_id) {
                            $admission = Admission::where('biometric_id', $row->biometric_id)->first();
                        }

                        $last = $admission?->first_name ?? '';
                        $first = $admission?->last_name ?? '';
                        $father = $admission?->father_name ?? '';

                        $fullName = trim("$last $first $father");

                        return $fullName ?: '-';
                    })
                    ->editColumn('date', function ($row) {
                        return Carbon::parse($row->date)->format('d-m-Y');
                    })
                    ->editColumn('in_time', function ($row) {
                        if (empty($row->first_punch)) {
                            return '<p class="text-center">-</p>';
                        }
                        return Carbon::parse($row->first_punch)->format('h:i A');
                    })
                    ->addColumn('out_time', function ($row) {
                        if (empty($row->last_punch) || $row->last_punch === $row->first_punch) {
                            return '<p class="text-center">-</p>';
                        }
                        return Carbon::parse($row->last_punch)->format('h:i A');
                    })
                    ->editColumn('DeviceKey', function ($row) {
                        return $row->DeviceKey ?: '-';
                    })
                    ->editColumn('DeviceName', function ($row) {
                        return $row->DeviceName ?: '-';
                    })
                    ->rawColumns(['student_name', 'date', 'in_time', 'out_time'])
                    ->make(true);
                return $returnData;
            }
            $availableExportColumns = $this->exportableColumns;
            $defaultExportColumns = $this->defaultExportColumns;
            return view($modules['folder_path'] . '.index', compact('data', 'availableExportColumns', 'defaultExportColumns'));
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

        return view($modules['folder_path'] . '.form', compact(var_name: 'modules'));
    }

    public function filter_student_data(Request $request)
    {
        try {

            $data = CourceRegistration::select('*');
            $data = $data->withTrashed();
            $data = $data->with(['admission', 'course']);
            // $data = $data->orWith(['attedance']);
            // Apply filters if present

            if ($request->filled('filter_admission') && $request->filter_admission) {
                $CourceRegistration = CourceRegistration::where('register_id', $request->filter_admission)->first();

                if ($CourceRegistration) {
                    $data->where('course_id', $CourceRegistration?->course_id);
                    $data->where('batch_id', $CourceRegistration?->batch_id);
                } else {
                    $data->where('id', $request->filter_admission);
                }
            }

            if ($request->filled('filter_course') && $request->filter_course) {
                $data->where('course_id', $request->input('filter_course'));
            }

            $registrations = $data->get();
            // dd("hello---------------------------------");

            $attedances = Attedance::where('date', Carbon::now()->toDateString())
                ->orderBy('in_time')
                ->orderBy('id')
                ->get();
            // $combined = $registrations->merge($attedances);
            //dd('L-176', $combined); // Now you’ll see all values
            // return $returnData;
            return response()->json([
                'data' => $registrations,
                'attedances' => $attedances,
            ]);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
    }

    public function store(AttedanceRequest $request)
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
        // return $request->all();
        $validated = $request->validated();

        try {
            Attedance::create($validated);
            return Redirect::route($modules['route'] . '.index')->withSuccess($modules['title'] . ' Create Successfully');
        } catch (\Exception $e) {
            return Redirect::route('dashboard')->withErrors($e->getMessage());
        }
    }

    public function student_in(Request $request)
    {
        if ($request->ajax()) {
            try {
                if ($request?->admissionId) {
                    $studenCurceRegistration = CourceRegistration::where('register_id', $request->admissionId)->first();
                    if (!$studenCurceRegistration) {
                        return $this->sendError("Student course not found.");
                    }
                    $admission_id = $studenCurceRegistration?->register_id;
                    $attedanceDate = Carbon::now()->toDateString();
                    $punchCount = $this->countPunchesForDate($admission_id, $attedanceDate);

                    if ($punchCount % 2 === 1) {
                        return $this->sendError("This student Attedance all ready In.");
                    }

                    Attedance::create([
                        'admission_id' => $admission_id,
                        'date'         => $attedanceDate,
                        'in_time'      => Carbon::now()->toTimeString(),
                    ]);
                    return $this->sendResponse([], 'Attendance marked successfully.');
                } else if ($request?->studentid) {
                    $studentIds = explode(',', $request->studentid);
                    $coursereg = [];
                    foreach ($studentIds as $value) {
                        $CourceRegistr = CourceRegistration::findOrFail($value);
                        $BatchRegistr = CourceRegistration::findOrFail($value);

                        $admission_id = $CourceRegistr?->register_id;
                        $attedanceDate = Carbon::now()->toDateString();
                        $punchCount = $this->countPunchesForDate($admission_id, $attedanceDate);

                        if ($punchCount % 2 === 0) {
                            Attedance::create([
                                'admission_id' => $CourceRegistr->register_id,
                                'date'         => $attedanceDate,
                                'in_time'      => Carbon::now()->toTimeString(),
                            ]);
                        }
                    }
                    //dd('L-176', $coursereg); // Now you’ll see all values
                    return response()->json([
                        'status' => true,
                        'message' => 'Attendance marked successfully.',
                    ]);
                }
            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to mark attendance. ' . $e->getMessage(),
                ], 500);
            }
        }

        // Optional fallback for non-AJAX calls
        return Redirect::back()->with('error', 'Invalid request');
    }

    public function student_out(Request $request)
    {
        if ($request->ajax()) {
            try {
                if ($request?->admissionId) {
                    $studenCurceRegistration = CourceRegistration::where('register_id', $request->admissionId)->first();
                    if (!$studenCurceRegistration) {
                        return $this->sendError("Student course not found.");
                    }
                    $admission_id = $studenCurceRegistration?->register_id;
                    $attedanceDate = Carbon::now()->toDateString();
                    $punchCount = $this->countPunchesForDate($admission_id, $attedanceDate);

                    if ($punchCount % 2 === 0) {
                        return $this->sendError("This student attedance not in.");
                    }

                    Attedance::create([
                        'admission_id' => $admission_id,
                        'date'         => $attedanceDate,
                        'in_time'      => Carbon::now()->toTimeString(),
                    ]);

                    return $this->sendResponse([], 'Attendance Out Marked successfully.');
                } else if ($request?->studentid) {
                    $studentIds = explode(',', $request->studentid);

                    foreach ($studentIds as $studentId) {
                        $registration = CourceRegistration::findOrFail($studentId);
                        $admissionId = $registration->register_id;
                        $punchCount = $this->countPunchesForDate($admissionId, Carbon::today()->toDateString());

                        if ($punchCount % 2 === 1) {
                            Attedance::create([
                                'admission_id' => $admissionId,
                                'date'         => Carbon::now()->toDateString(),
                                'in_time'      => Carbon::now()->toTimeString(),
                            ]);
                        }
                    }
                    return response()->json([
                        'status' => true,
                        'message' => 'Attendance updated successfully.',
                    ]);
                }
            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to update attendance. ' . $e->getMessage(),
                ], 500);
            }
        }
        return Redirect::back()->with('error', 'Invalid request');
    }

    public function student_leave(Request $request)
    {
        if ($request->ajax()) {
            try {
                $studentIds = explode(',', $request->studentid);
                $coursereg = [];
                foreach ($studentIds as $value) {
                    $CourceRegistr = CourceRegistration::findOrFail($value);
                    $BatchRegistr = CourceRegistration::findOrFail($value);

                    $coursereg = [
                        'admission_id' => $CourceRegistr->register_id,
                        'course_id'    => $CourceRegistr->course_id,
                        'batch_id'     => $BatchRegistr->batch_id,
                        'date'         => Carbon::now()->toDateString(),
                        'leave' => Carbon::now()->toDateTimeString(),
                        'created_by'   => Auth::id(),
                    ];

                    Attedance::create($coursereg);
                }
                return response()->json([
                    'status' => true,
                    'message' => 'Leave marked successfully.',
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to mark leave. ' . $e->getMessage(),
                ], 500);
            }
        }
        return Redirect::back()->with('error', 'Invalid request');
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

            $edit = Attedance::findOrFail($id);
            return view($modules['folder_path'] . '.form', compact('modules', 'edit'));
        } catch (\Exception $e) {
            return Redirect::route('dashboard')->withErrors($e->getMessage());
        }
    }

    public function update(AttedanceRequest $request, string $id)
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
        // return $request->all();
        $request['id'] = $id;

        $validated = $request->validated();
        try {
            $validated['updated_by'] = Auth::user()->id;
            // return $validated;
            $updateData = Attedance::findOrFail($id);
            if ($updateData) {
                unset($validated['id']);
                $updateData->update($validated);

                return Redirect::route($modules['route'] . '.index')->withSuccess($modules['title'] . ' update successfully');
            }
            return Redirect::back()->withErrors('something went wrong please try again later')->withInput();
        } catch (\Exception $e) {
            return Redirect::route('dashboard')->withErrors($e->getMessage());
        }
    }

    public function status_update(Request $request)
    {
        $isAjax = ($request->ajax()) ? true : false;

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
        $validator = Validator::make($request->all(), [
            'id' => ['required', Rule::exists($modules['table_name'], 'id')],
            'update_status' => ['required', 'in:active,inactive']
        ]);

        if ($validator->fails() && $isAjax) {
            return $this->sendError($validator->messages()->first(), $validator->messages(), [], 401);
        }

        try {
            $designation = Attedance::findOrFail($request?->id);

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
            $dataDelete = Attedance::findOrFail($id);

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
    public function restore($id)
    {

        $modules = $this->modules;

        try {

            $restore_data = Attedance::findOrFail($id);
            $restore_data->restore();

            return Redirect::back()->withSuccess($modules['title'] . ' restored successfully!');
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }

    public function fetchLogs(Request $request)
    {
        $modules = $this->modules;
        View::share('modules', $modules);

        try {
            $fromDate = now()->toDateString();
            $toDate = now()->toDateString();

            $token = Session::get('external_api_token');

            // Fetch device logs
            $logResponse = Http::withToken($token)->get($modules['api_ip'] . "api/DeviceLog/GetAllLogsByDate", [
                'FromDate' => $fromDate,
                'ToDate' => $toDate,
            ]);

            if (!$logResponse->successful()) {
                return response()->json(['message' => 'Failed to fetch logs from API.'], 500);
            }

            $logs = $logResponse->json();
            $saved = 0;
            $skipped = 0;

            foreach ($logs as $log) {
                // Machine punch fields
                $biometricId = $log['UserId'] ?? $log['biometric_id'] ?? null;
                $date = isset($log['IOTime'])
                    ? Carbon::parse($log['IOTime'])->toDateString()
                    : ($log['date'] ?? null);
                $inTime = isset($log['IOTime'])
                    ? Carbon::parse($log['IOTime'])->format('H:i:s')
                    : ($log['in_time'] ?? null);
                $deviceKey = $log['DeviceKey'] ?? null;
                $deviceName = $log['DeviceName'] ?? null;

                if (!$biometricId || !$date || !$inTime) {
                    $skipped++;
                    continue;
                }

                // biometric_id → admission_id
                $admission = Admission::findByBiometricId($biometricId);

                if (!$admission) {
                    Log::warning('Attendance punch skipped: biometric_id not mapped', [
                        'biometric_id' => $biometricId,
                        'date' => $date,
                        'in_time' => $inTime,
                    ]);
                    $skipped++;
                    continue;
                }

                $alreadyExists = Attedance::where('admission_id', $admission->id)
                    ->whereDate('date', $date)
                    ->where('in_time', $inTime)
                    ->exists();

                if ($alreadyExists) {
                    $skipped++;
                    continue;
                }

                Attedance::create([
                    'biometric_id' => $biometricId,
                    'admission_id' => $admission->id,
                    'date' => $date,
                    'in_time' => $inTime,
                    'DeviceKey' => $deviceKey,
                    'DeviceName' => $deviceName,
                ]);

                $saved++;
            }

            return response()->json([
                'message' => 'Attendance synced successfully.',
                'saved' => $saved,
                'skipped' => $skipped,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }


    // public function fetchLogs(Request $request)
    // {
    //     try {
    //         $fromDate = now()->toDateString();
    //         $toDate = now()->toDateString();

    //         $token = Session::get('external_api_token');

    //         // Fetch all employees
    //         $employeeResponse = Http::withToken($token)->get("http://192.168.1.88:88/api/Employees");

    //         if (!$employeeResponse->successful()) {
    //             return response()->json(['message' => 'Failed to fetch employee data.'], 500);
    //         }

    //         $employees = collect($employeeResponse->json());

    //         // Fetch device logs
    //         $logResponse = Http::withToken($token)
    //             ->get("http://192.168.1.88:88/api/DeviceLog/GetAllLogsByDate", [
    //                 'FromDate' => $fromDate,
    //                 'ToDate' => $toDate,
    //             ]);

    //         if (!$logResponse->successful()) {
    //             return response()->json(['message' => 'Failed to fetch logs from API.'], 500);
    //         }

    //         $logs = $logResponse->json();

    //         foreach ($logs as $log) {
    //             $attendance = Attedance::where('biometric_id', $log['Id'])->first();

    //             $date = \Carbon\Carbon::parse($log['IOTime'])->toDateString();
    //             $time = \Carbon\Carbon::parse($log['IOTime'])->format('H:i:s');
    //             $ioTime = \Carbon\Carbon::parse($log['IOTime'])->format('Y-m-d H:i:s');

    //             // Find employee by matching UserId
    //             $employee = $employees->firstWhere('id', $log['UserId']);
    //             $employeeName = $employee['name'] ?? $log['UserName'] ?? null;

    //             if ($attendance) {
    //                 if ($log['IOMode'] === 'in') {
    //                     $attendance->update([
    //                         'in_time' => $time,
    //                     ]);
    //                 } elseif ($log['IOMode'] === 'out') {
    //                     $attendance->update([
    //                         'out_time' => $time,
    //                     ]);
    //                 }
    //             } else {
    //                 Attedance::create([
    //                     'biometric_id' => $log['Id'],
    //                     'admission_id' => $log['UserId'] ?? null,
    //                     'date' => $date,
    //                     'in_time' => $log['IOMode'] === 'in' ? $time : null,
    //                     'out_time' => $log['IOMode'] === 'out' ? $time : null,
    //                     'DeviceKey' => $log['DeviceKey'] ?? null,
    //                     'DeviceName' => $log['DeviceName'] ?? null,
    //                     'UserId' => $log['UserId'] ?? null,
    //                     'EmpCode' => $log['EmpCode'] ?? null,
    //                     'UserName' => $employeeName, // store name from Employee API
    //                     'IOTime' => $ioTime ?? null,
    //                     'IOMode' => $log['IOMode'] ?? null,
    //                     'VerifyMode' => $log['VerifyMode'] ?? null,
    //                     'WorkCode' => $log['WorkCode'] ?? null,
    //                     'ImagePath' => $log['ImagePath'] ?? null,
    //                 ]);
    //             }
    //         }

    //         return response()->json(['message' => 'Attendance synced successfully.']);
    //     } catch (\Exception $e) {
    //         return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
    //     }
    // }

    public function exportExcel(Request $request)
    {
        try {

            $this->authorizeAttendanceList();

            $scope = strtolower($request->get('scope', 'filtered')) === 'all' ? 'all' : 'filtered';
            $selectedColumns = $this->resolveSelectedColumns($request->input('columns', []));
            $columnLabels = $this->mapColumnLabels($selectedColumns);

            $attendances = $this->buildAttendanceExportQuery($request, $scope)->get();

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            foreach (array_values($columnLabels) as $index => $label) {
                $sheet->setCellValueByColumnAndRow($index + 1, 1, $label);
            }

            $rowNumber = 2;
            foreach ($attendances as $attendance) {
                foreach ($selectedColumns as $columnIndex => $columnKey) {
                    $sheet->setCellValueByColumnAndRow(
                        $columnIndex + 1,
                        $rowNumber,
                        $this->formatColumnValueForExcel($attendance, $columnKey)
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

            $fileName = 'attendance-' . now()->format('Ymd_His') . '.xlsx';

            return response()->streamDownload(function () use ($spreadsheet) {
                $writer = new Xlsx($spreadsheet);
                $writer->save('php://output');
            }, $fileName, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
        } catch (\Exception $e) {
            return $e->getMessage();
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function printView(Request $request)
    {
        $this->authorizeAttendanceList();

        $scope = strtolower($request->get('scope', 'filtered')) === 'all' ? 'all' : 'filtered';
        $selectedColumns = $this->resolveSelectedColumns($request->input('columns', []));
        $columnLabels = $this->mapColumnLabels($selectedColumns);
        $searchTerm = $scope === 'filtered' ? trim((string) $request->get('search', '')) : null;

        $attendances = $this->buildAttendanceExportQuery($request, $scope)->get();

        return view($this->modules['folder_path'] . '.print', [
            'attendances' => $attendances,
            'columnLabels' => $columnLabels,
            'columnKeys' => $selectedColumns,
            'scope' => $scope,
            'searchTerm' => $searchTerm,
            'generatedAt' => now(),
        ]);
    }

    protected function authorizeAttendanceList(): void
    {
        $canList = Helper::directCan($this->modules['permission_prefix'] . '-list');
        if (!$canList) {
            abort(403, 'User does not have the right permissions.');
        }
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

    protected function buildAttendanceExportQuery(Request $request, string $scope): Builder
    {
        $latestRegIds = DB::table('cource_registration')
            ->select('register_id', DB::raw('MAX(id) as max_id'))
            ->whereNull('deleted_at')
            ->groupBy('register_id');

        $query = Attedance::select([
                'attedance.admission_id',
                'attedance.biometric_id',
                'attedance.date',
                DB::raw('MIN(attedance.in_time) as first_punch'),
                DB::raw('MAX(attedance.in_time) as last_punch'),
                DB::raw('MIN(attedance.DeviceKey) as DeviceKey'),
                DB::raw('MIN(attedance.DeviceName) as DeviceName'),
            ])
            ->join('admission', function ($join) {
                $join->on('attedance.admission_id', '=', 'admission.id')
                     ->orOn('attedance.biometric_id', '=', 'admission.biometric_id');
            })
            ->join('cource_registration', function ($join) {
                $join->on('admission.id', '=', 'cource_registration.register_id')
                     ->whereRaw('attedance.date >= cource_registration.date');
            })
            ->joinSub($latestRegIds, 'latest_reg', function ($join) {
                $join->on('cource_registration.id', '=', 'latest_reg.max_id');
            })
            ->whereIn('cource_registration.status', ['active', 'Running'])
            ->whereNull('admission.deleted_at')
            ->whereNull('cource_registration.deleted_at')
            ->with(['admission'])
            ->groupBy('attedance.admission_id', 'attedance.biometric_id', 'attedance.date')
            ->orderByDesc('attedance.date');

        $admissionId = $request->get('admission_id');
        if (!empty($admissionId)) {
            $query->where('attedance.admission_id', $admissionId);
        }

        if ($scope === 'all') {
            return $query;
        }

        $search = trim((string) $request->get('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('attedance.biometric_id', 'like', "%{$search}%")
                    ->orWhere('attedance.date', 'like', "%{$search}%")
                    ->orWhere('attedance.DeviceKey', 'like', "%{$search}%")
                    ->orWhere('attedance.DeviceName', 'like', "%{$search}%")
                    ->orWhereHas('admission', function ($admissionQuery) use ($search) {
                        $admissionQuery->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('father_name', 'like', "%{$search}%");
                    })
                    ->orWhereIn('biometric_id', function ($subQuery) use ($search) {
                        $subQuery->select('biometric_id')
                            ->from('admission')
                            ->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('father_name', 'like', "%{$search}%");
                    });
            });
        }

        return $query;
    }

    protected function mapColumnLabels(array $selectedColumns): array
    {
        $labels = [];
        foreach ($selectedColumns as $columnKey) {
            $labels[$columnKey] = $this->exportableColumns[$columnKey] ?? ucfirst(str_replace('_', ' ', $columnKey));
        }
        return $labels;
    }

    protected function formatColumnValueForExcel(Attedance $attendance, string $columnKey)
    {
        switch ($columnKey) {
            case 'biometric_id':
                if ($attendance->biometric_id) {
                    return $attendance->biometric_id;
                }
                if ($attendance->admission_id) {
                    return $attendance->admission?->biometric_id ?? $attendance->admission_id;
                }
                return '-';
            case 'student_name':
                $admission = $attendance->admission;
                if (!$admission && $attendance->biometric_id) {
                    $admission = Admission::where('biometric_id', $attendance->biometric_id)->first();
                }
                $last = $admission?->first_name ?? '';
                $first = $admission?->last_name ?? '';
                $father = $admission?->father_name ?? '';
                $fullName = trim("{$last} {$first} {$father}");
                return $fullName !== '' ? $fullName : '-';
            case 'device_key':
                return $attendance->DeviceKey ?? '-';
            case 'device_name':
                return $attendance->DeviceName ?? '-';
            case 'date':
                return $attendance->date
                    ? Carbon::parse($attendance->date)->format('d-m-Y')
                    : '-';
            case 'in_time':
                return $attendance->first_punch
                    ? Carbon::parse($attendance->first_punch)->format('h:i A')
                    : '-';
            case 'out_time':
                return ($attendance->last_punch && $attendance->last_punch !== $attendance->first_punch)
                    ? Carbon::parse($attendance->last_punch)->format('h:i A')
                    : '-';
            case 'leave_status':
                $inTime = $attendance->in_time ?? '';
                $outTime = $attendance->out_time ?? '';
                $leave = $attendance->leave ?? '';
                if ($inTime === '' && $outTime === '' && $leave !== '') {
                    return 'Leave';
                }
                return '-';
            default:
                return data_get($attendance, $columnKey, '-');
        }
    }

    public function checkClasswiseStudents(Request $request)
    {
        $this->authorizeAttendanceList();

        $request->validate([
            'filter_course' => 'required|integer',
            'filter_batch' => 'required|integer',
            'filter_class' => 'required|integer',
        ]);

        $count = CourceRegistration::query()
            ->where('course_id', $request->filter_course)
            ->where('batch_id', $request->filter_batch)
            ->where('class_id', $request->filter_class)
            ->count();

        return response()->json([
            'status' => true,
            'count' => $count,
            'has_students' => $count > 0,
            'message' => $count > 0
                ? 'Students found successfully.'
                : 'No students found for selected Course, Batch and Class.',
        ]);
    }

    public function exportClasswiseMonthlyExcel(Request $request)
    {
        $this->authorizeAttendanceList();

        $request->validate([
            'filter_course' => 'required|integer',
            'filter_batch' => 'required|integer',
            'filter_class' => 'required|integer',
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2000|max:2100',
        ]);

        $courseId = (int) $request->filter_course;
        $batchId = (int) $request->filter_batch;
        $classId = (int) $request->filter_class;
        $month = (int) $request->month;
        $year = (int) $request->year;

        $course = MasterCourse::find($courseId);
        $masterClass = MasterClass::find($classId);

        $students = CourceRegistration::with(['admission'])
            ->where('course_id', $courseId)
            ->where('batch_id', $batchId)
            ->where('class_id', $classId)
            ->orderBy('id')
            ->get();

        $admissionIds = $students->pluck('register_id')->filter()->unique()->values();

        $monthStart = Carbon::create($year, $month, 1)->startOfDay();
        $monthEnd = $monthStart->copy()->endOfMonth();
        $daysInMonth = $monthStart->daysInMonth;

        $attendanceRows = Attedance::query()
            ->whereIn('admission_id', $admissionIds)
            ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->get();

        $attendanceMap = [];
        foreach ($attendanceRows as $row) {
            $day = Carbon::parse($row->date)->day;
            $attendanceMap[$row->admission_id][$day] = $row;
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Monthly Attendance');

        $dayAbbr = ['Su', 'M', 'Tu', 'W', 'Th', 'F', 'Sa'];
        $dayColumnCount = $daysInMonth;
        $firstDayCol = 4;
        $lastDayCol = $firstDayCol + $dayColumnCount - 1;
        $totalStartCol = $lastDayCol + 1;
        $lastCol = $totalStartCol + 2;

        $titleLeftEndCol = 20;
        $titleRightStartCol = 21;
        $teacherNameStartCol = 2;
        $teacherNameEndCol = 4;
        $courseLabelStartCol = 5;
        $courseLabelEndCol = 11;
        $courseValueStartCol = 12;
        $courseValueEndCol = 20;
        $monthLabelStartCol = 21;
        $monthLabelEndCol = 25;
        $monthValueStartCol = 26;

        $col = fn(int $index) => Coordinate::stringFromColumnIndex($index);

        $sheet->mergeCells('A1:' . $col($titleLeftEndCol) . '1');
        $sheet->setCellValue('A1', 'Monthly Class Attendance');
        $sheet->mergeCells($col($titleRightStartCol) . '1:' . $col($lastCol) . '1');
        $sheet->setCellValue($col($titleRightStartCol) . '1', 'PVM BCA COLLEGE - KESHOD');

        $teacherName = '';
        $courseLabel = trim(($course->course_name ?? '') . ' ' . ($masterClass->class ?? ''));

        $metaRowStart = 2;
        $metaRowEnd = 4;
        $monthRowEnd = 3;
        $yearRow = 4;

        $sheet->mergeCells('A' . $metaRowStart . ':A' . $metaRowEnd);
        $sheet->setCellValue('A' . $metaRowStart, 'Teacher');
        $sheet->mergeCells($col($teacherNameStartCol) . $metaRowStart . ':' . $col($teacherNameEndCol) . $metaRowEnd);
        $sheet->setCellValue($col($teacherNameStartCol) . $metaRowStart, $teacherName);
        $sheet->mergeCells($col($courseLabelStartCol) . $metaRowStart . ':' . $col($courseLabelEndCol) . $metaRowEnd);
        $sheet->setCellValue($col($courseLabelStartCol) . $metaRowStart, 'Course');
        $sheet->mergeCells($col($courseValueStartCol) . $metaRowStart . ':' . $col($courseValueEndCol) . $metaRowEnd);
        $sheet->setCellValue($col($courseValueStartCol) . $metaRowStart, $courseLabel);

        $sheet->mergeCells($col($monthLabelStartCol) . $metaRowStart . ':' . $col($monthLabelEndCol) . $monthRowEnd);
        $sheet->setCellValue($col($monthLabelStartCol) . $metaRowStart, 'Month');
        $sheet->mergeCells($col($monthValueStartCol) . $metaRowStart . ':' . $col($lastCol) . $monthRowEnd);
        $sheet->setCellValue($col($monthValueStartCol) . $metaRowStart, $monthStart->format('F'));
        $sheet->mergeCells($col($monthLabelStartCol) . $yearRow . ':' . $col($monthLabelEndCol) . $yearRow);
        $sheet->setCellValue($col($monthLabelStartCol) . $yearRow, 'Year');
        $sheet->mergeCells($col($monthValueStartCol) . $yearRow . ':' . $col($lastCol) . $yearRow);
        $sheet->setCellValue($col($monthValueStartCol) . $yearRow, $year);

        $headerRowTop = 6;
        $headerRowBottom = 7;
        $dataStartRow = 8;

        $sheet->mergeCells('A' . $headerRowTop . ':C' . $headerRowTop);
        $sheet->setCellValue('A' . $headerRowTop, 'Student');
        $sheet->setCellValue('A' . $headerRowBottom, 'ID');
        $sheet->mergeCells('B' . $headerRowBottom . ':C' . $headerRowBottom);
        $sheet->setCellValue('B' . $headerRowBottom, 'Name');

        for ($day = 1; $day <= $dayColumnCount; $day++) {
            $dayCol = $firstDayCol + $day - 1;
            $cellCol = $col($dayCol);
            if ($day <= $daysInMonth) {
                $date = Carbon::create($year, $month, $day);
                $sheet->setCellValue($cellCol . $headerRowTop, $dayAbbr[$date->dayOfWeek]);
                $sheet->setCellValue($cellCol . $headerRowBottom, $day);
            } else {
                $sheet->setCellValue($cellCol . $headerRowTop, '');
                $sheet->setCellValue($cellCol . $headerRowBottom, '');
            }
        }

        $sheet->mergeCells($col($totalStartCol) . $headerRowTop . ':' . $col($lastCol) . $headerRowTop);
        $sheet->setCellValue($col($totalStartCol) . $headerRowTop, 'Totals');
        $sheet->setCellValue($col($totalStartCol) . $headerRowBottom, 'P');
        $sheet->setCellValue($col($totalStartCol + 1) . $headerRowBottom, 'L');
        $sheet->setCellValue($col($totalStartCol + 2) . $headerRowBottom, 'TLR');

        $rowNumber = $dataStartRow;
        $serial = 1;
        $studentRows = [];

        foreach ($students as $registration) {
            $admission = $registration->admission;
            $admissionId = $registration->register_id;
            $studentName = trim(
                ($admission->first_name ?? '') . ' ' .
                    ($admission->last_name ?? '') . ' ' .
                    ($admission->father_name ?? '')
            );

            $presentCount = 0;
            $leaveCount = 0;
            $dayMarks = [];

            for ($day = 1; $day <= $dayColumnCount; $day++) {
                $mark = '';
                if ($day <= $daysInMonth) {
                    $record = $attendanceMap[$admissionId][$day] ?? null;
                    if ($record) {
                        $inTime = trim((string) ($record->in_time ?? ''));
                        $outTime = trim((string) ($record->out_time ?? ''));
                        $leave = trim((string) ($record->leave ?? ''));

                        if ($inTime !== '') {
                            $mark = 'P';
                            $presentCount++;
                        } elseif ($leave !== '' && $outTime === '') {
                            $mark = 'L';
                            $leaveCount++;
                        }
                    }
                }
                $dayMarks[$day] = $mark;
            }

            $studentRows[] = [
                'serial' => $serial,
                'name' => $studentName ?: '-',
                'marks' => $dayMarks,
                'present' => $presentCount > 0 ? $presentCount : '',
                'leave' => $leaveCount > 0 ? $leaveCount : '',
                'tlr' => ($presentCount + $leaveCount) > 0 ? ($presentCount + $leaveCount) : '',
            ];
            $serial++;
        }

        foreach ($studentRows as $studentRow) {
            $sheet->setCellValue('A' . $rowNumber, $studentRow['serial']);
            $sheet->mergeCells('B' . $rowNumber . ':C' . $rowNumber);
            $sheet->setCellValue('B' . $rowNumber, $studentRow['name']);

            for ($day = 1; $day <= $dayColumnCount; $day++) {
                $sheet->setCellValue($col($firstDayCol + $day - 1) . $rowNumber, $studentRow['marks'][$day]);
            }

            $sheet->setCellValue($col($totalStartCol) . $rowNumber, $studentRow['present']);
            $sheet->setCellValue($col($totalStartCol + 1) . $rowNumber, $studentRow['leave']);
            $sheet->setCellValue($col($totalStartCol + 2) . $rowNumber, $studentRow['tlr']);

            $rowNumber++;
        }

        $lastDataRow = $rowNumber - 1;
        $footerRow1 = $lastDataRow + 1;
        $footerRow2 = $lastDataRow + 2;

        $footer1MergedRange = 'A' . $footerRow1 . ':' . $col($lastDayCol) . $footerRow1;
        $footer1TotalsRange = $col($totalStartCol) . $footerRow1 . ':' . $col($lastCol) . $footerRow1;
        $footer2MergedRange = 'A' . $footerRow2 . ':' . $col($lastDayCol) . $footerRow2;
        $footer2TotalsRange = $col($totalStartCol) . $footerRow2 . ':' . $col($lastCol) . $footerRow2;
        $sheet->mergeCells($footer1MergedRange);
        $sheet->mergeCells($footer2MergedRange);

        $tableRange = 'A' . $headerRowTop . ':' . $col($lastCol) . $lastDataRow;
        $totalsRange = $col($totalStartCol) . $headerRowTop . ':' . $col($lastCol) . $lastDataRow;
        $lastReportRow = $footerRow2;
        $titleLeftRange = 'A1:' . $col($titleLeftEndCol) . '1';
        $titleRightRange = $col($titleRightStartCol) . '1:' . $col($lastCol) . '1';
        $metaRanges = [
            'A' . $metaRowStart . ':A' . $metaRowEnd,
            $col($teacherNameStartCol) . $metaRowStart . ':' . $col($teacherNameEndCol) . $metaRowEnd,
            $col($courseLabelStartCol) . $metaRowStart . ':' . $col($courseLabelEndCol) . $metaRowEnd,
            $col($courseValueStartCol) . $metaRowStart . ':' . $col($courseValueEndCol) . $metaRowEnd,
            $col($monthLabelStartCol) . $metaRowStart . ':' . $col($monthLabelEndCol) . $monthRowEnd,
            $col($monthValueStartCol) . $metaRowStart . ':' . $col($lastCol) . $monthRowEnd,
            $col($monthLabelStartCol) . $yearRow . ':' . $col($monthLabelEndCol) . $yearRow,
            $col($monthValueStartCol) . $yearRow . ':' . $col($lastCol) . $yearRow,
        ];
        $tableHeaderMergedRanges = [
            'A' . $headerRowTop . ':C' . $headerRowTop,
            'A' . $headerRowBottom,
            'B' . $headerRowBottom . ':C' . $headerRowBottom,
            $col($totalStartCol) . $headerRowTop . ':' . $col($lastCol) . $headerRowTop,
        ];

        $thinBorder = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ];

        $totalsFill = [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['argb' => 'FFDCBEAF'],
        ];
        $footerFill = [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['argb' => 'FFDCBEAF'],
        ];
        $sundayFill = [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['argb' => 'FFD9D9D9'],
        ];

        $tableHeaderRange = 'A' . $headerRowTop . ':' . $col($lastCol) . $headerRowBottom;

        $sheet->getStyle($tableHeaderRange)->applyFromArray([
            'fill' => $totalsFill,
            'font' => ['bold' => true],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getStyle($totalsRange)->applyFromArray([
            'fill' => $totalsFill,
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getStyle($footer1MergedRange)->applyFromArray(['fill' => $footerFill]);
        $sheet->getStyle($footer1TotalsRange)->applyFromArray(['fill' => $footerFill]);
        $sheet->getStyle($footer2MergedRange)->applyFromArray(['fill' => $footerFill]);
        $sheet->getStyle($footer2TotalsRange)->applyFromArray(['fill' => $footerFill]);

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::create($year, $month, $day);
            if ($date->dayOfWeek === Carbon::SUNDAY) {
                $dayCol = $firstDayCol + $day - 1;
                $sundayColRange = $col($dayCol) . $headerRowTop . ':' . $col($dayCol) . $lastDataRow;
                $sheet->getStyle($sundayColRange)->applyFromArray(['fill' => $sundayFill]);
            }
        }

        $sheet->getStyle($titleLeftRange)->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle($titleLeftRange)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($titleRightRange)->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle($titleRightRange)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getStyle('A' . $metaRowStart)->getFont()->setBold(true);
        $sheet->getStyle($col($courseLabelStartCol) . $metaRowStart)->getFont()->setBold(true);
        $sheet->getStyle($col($monthLabelStartCol) . $metaRowStart)->getFont()->setBold(true);
        $sheet->getStyle($col($monthLabelStartCol) . $yearRow)->getFont()->setBold(true);
        foreach ($metaRanges as $metaRange) {
            $sheet->getStyle($metaRange)->applyFromArray($thinBorder);
        }
        $sheet->getStyle($titleLeftRange)->applyFromArray($thinBorder);
        $sheet->getStyle($titleRightRange)->applyFromArray($thinBorder);

        $sheet->getStyle('B' . $dataStartRow . ':C' . $lastDataRow)
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)
            ->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A' . $headerRowBottom . ':A' . $lastDataRow)
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A' . $dataStartRow . ':' . $col($lastCol) . $lastDataRow)
            ->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($col($firstDayCol) . $dataStartRow . ':' . $col($lastDayCol) . $lastDataRow)
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $borderStyle = [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['argb' => 'FF000000'],
        ];
        $allBordersStyle = ['borders' => ['allBorders' => $borderStyle]];
        $outlineStyle = [
            'borders' => [
                'outline' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ];

        $borderedRanges = array_merge(
            [$titleLeftRange, $titleRightRange, $tableRange, $footer1MergedRange, $footer1TotalsRange, $footer2MergedRange, $footer2TotalsRange],
            $metaRanges,
            $tableHeaderMergedRanges
        );
        foreach ($borderedRanges as $borderedRange) {
            $sheet->getStyle($borderedRange)->applyFromArray($allBordersStyle);
        }

        $reportRange = 'A1:' . $col($lastCol) . $lastReportRow;
        $sheet->getStyle($reportRange)->applyFromArray($outlineStyle);

        $tableTopBorder = $sheet->getStyle('A' . $headerRowTop . ':' . $col($lastCol) . $headerRowTop)->getBorders()->getTop();
        $tableTopBorder->setBorderStyle(Border::BORDER_THIN);
        $tableTopBorder->getColor()->setARGB('FF000000');

        $sheet->getColumnDimension('A')->setWidth(10);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(20);
        for ($c = $firstDayCol; $c <= $lastDayCol; $c++) {
            $sheet->getColumnDimension($col($c))->setWidth(3.5);
        }
        $sheet->getColumnDimension($col($totalStartCol))->setWidth(5);
        $sheet->getColumnDimension($col($totalStartCol + 1))->setWidth(5);
        $sheet->getColumnDimension($col($totalStartCol + 2))->setWidth(6);

        $sheet->getRowDimension(1)->setRowHeight(24);
        $sheet->getRowDimension(2)->setRowHeight(18);
        $sheet->getRowDimension(3)->setRowHeight(18);
        $sheet->getRowDimension(4)->setRowHeight(18);
        $sheet->getRowDimension($headerRowTop)->setRowHeight(18);
        $sheet->getRowDimension($headerRowBottom)->setRowHeight(18);

        $sheet->getRowDimension($footerRow1)->setRowHeight(18);
        $sheet->getRowDimension($footerRow2)->setRowHeight(18);

        $fileName = 'monthly-class-attendance-' . $year . '-' . str_pad((string) $month, 2, '0', STR_PAD_LEFT) . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    protected function countPunchesForDate($admissionId, string $date): int
    {
        return Attedance::where('admission_id', $admissionId)
            ->whereDate('date', $date)
            ->count();
    }
}
