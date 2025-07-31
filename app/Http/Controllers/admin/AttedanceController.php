<?php

namespace App\Http\Controllers\admin;

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

class AttedanceController extends Controller
{
    public $modules = [];
    public function __construct()
    {
        $this->modules = [
            'title' => 'Attedance',
            'folder_path' => 'software.module.attedance',
            'route' => 'attedance',
            'table_name' => (new Attedance())->getTable(),
            'permisstion_prefix' => 'attedance',
            'api_ip' => 'http://192.168.31.5:98/',
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
                (object)['data' => "admission_id", 'name' => 'admission_id', 'td_label' => 'Admission_id'],
                (object)['data' => 'UserName', 'name' => 'UserName', 'td_label' => 'Student Name'],
                (object)['data' => 'course_name', 'name' => 'course_id', 'td_label' => 'Course Name'],
                (object)['data' => "batch_name", 'name' => 'batch_id', 'td_label' => 'Batch Name'],
                (object)['data' => "date", 'name' => 'date', 'td_label' => 'Date'],
                (object)['data' => 'in_time', 'name' => 'in_time', 'td_label' => 'In Time', 'orderable' => false, 'searchable' => false],
                (object)['data' => 'out_time', 'name' => 'out_time', 'td_label' => 'Out Time', 'orderable' => false, 'searchable' => false],
                (object)['data' => 'leve_status', 'name' => 'leve_status', 'td_label' => 'Leave Status', 'orderable' => false, 'searchable' => false],
                // (object)['data' => 'in_and_out_and_leve_status', 'name' => 'in_and_out_and_leve_status', 'td_label' => 'In / Out / Leave Status'],
            ];
            View::share("columns", $columns);

            $data = [];

            if ($request->ajax()) {
                // dd($request->all());
                $data = Attedance::select('*');
                $data = $data->withTrashed();
                $data = $data->orderBy('created_at', 'desc');
                $data = $data->with(['course', 'admission', 'batch']);

                $editPermission = true;
                $deletePermission = true;

                $returnData = Datatables::of($data)
                    ->addIndexColumn()
                    ->filter(function ($query) use ($request) {
                        if ($request->has('search')) {
                            $query->where('admission_id', 'like', "%" . $request->search . "%");
                            // $query->orWhere('subject_name', 'like',"%".$request->search."%");
                        }
                    })
                    ->editColumn('student_name', function ($row) {
                        $first = $row->admission->first_name ?? '';
                        $father = $row->admission->father_name ?? '';
                        $last = $row->admission->last_name ?? '';
                        return trim("{$first} {$father} {$last}");
                    })
                    ->addColumn('course_name', function ($row) {
                        return $row?->course?->course_name ?? '-';
                    })
                    ->addColumn('batch_name', function ($row) {
                        return $row?->batch?->batch_name ?? '-';
                    })
                    ->editColumn('in_time', function ($row) {
                        if ($row?->in_time) {
                            return \Carbon\Carbon::parse($row?->in_time)->format('h:i A');
                        }
                        return "-";
                    })
                    ->editColumn('out_time', function ($row) {
                        if ($row?->out_time) {
                            return \Carbon\Carbon::parse($row?->out_time)->format('h:i A');
                        }
                        return "-";
                    })
                    ->editColumn('leve_status', function ($row) {
                        $in_time = $row->in_time ?? '';
                        $out_time = $row->out_time ?? '';
                        $leave = $row->leave ?? "";
                        // $from_time = Carbon::parse($row->shift->from_time)->format('h:i A');
                        // $to_time = Carbon::parse($row->shift->to_time)->format('h:i A');

                        if ($in_time == "" && $out_time == "" && $leave != "") {
                            $status = '<span class="badge badge-center bg-info bg-glow" style="width:4.5rem;">Leave</span>';
                        } else {
                            $status = '-';
                        }

                        return $status;
                    })
                    ->editColumn('in_and_out_and_leve_status', function ($row) {
                        $in_time = $row->in_time ?? '';
                        $out_time = $row->out_time ?? '';
                        $leave = $row->leave ?? "";
                        // $from_time = Carbon::parse($row->shift->from_time)->format('h:i A');
                        // $to_time = Carbon::parse($row->shift->to_time)->format('h:i A');

                        if ($in_time != "" && $out_time == "") {
                            $status = '<span class="badge badge-center bg-warning bg-glow" style="width:4.5rem;">On Duty</span>';
                        } else if ($in_time != "" && $out_time != "") {
                            $status = '<span class="badge badge-center bg-success bg-glow" style="width:4.5rem;">Present</span>';
                        } else if ($in_time == "" && $out_time == "") {
                            $status = '<span class="badge badge-center bg-danger bg-glow" style="width:4.5rem;">Absent</span>';
                        } else if ($in_time == "" && $out_time == "" && $leave != "") {
                            $status = '<span class="badge badge-center bg-info bg-glow" style="width:4.5rem;">Leave</span>';
                        }

                        return $status;
                    })
                    ->editColumn('date', function ($row) {
                        return Carbon::parse($row->date)->format('d-m-Y');
                    })
                    ->editColumn('in_time', function ($row) {
                        if ($row->in_time == '') {
                            $in_time = '<p class="text-center">-</p>';
                        } else {
                            $in_time = Carbon::parse($row->in_time)->format('h:i A');
                        }
                        return $in_time;
                    })
                    ->editColumn('out_time', function ($row) {
                        if ($row->out_time == '') {
                            $out_time = '<p class="text-center">-</p>';
                        } else {
                            $out_time = Carbon::parse($row->out_time)->format('h:i A');
                        }

                        return $out_time;
                    })
                    ->editColumn('leave', function ($row) {
                        if ($row->leave == '') {
                            $leave = '<p class="text-center">-</p>';
                        } else {
                            $leave = '<p class="text-center text-danger"><strong>Leave</strong></p>';
                        }
                        return $leave;
                    })


                    ->rawColumns(['student_name', 'date', 'in_time', 'out_time', 'leave'])
                    ->make(true);
                return $returnData;
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
                $data->where('register_id', $request->input('filter_admission'));
            }

            if ($request->filled('filter_course') && $request->filter_course) {
                $data->where('course_id', $request->input('filter_course'));
            }

            $registrations = $data->get();

            $attedances = Attedance::where('date', Carbon::now()->toDateString())->get();
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
        View::share('modules', $modules);
        // return $request->all();
        $validated = $request->validated();

        try {
            $validated['created_by'] = Auth::user()->id;
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
                    $studenCurceRegistration = CourceRegistration::where('id', $request->admissionId)->first();
                    if (!$studenCurceRegistration) {
                        return $this->sendError("Student course not found.");
                    }
                    $admission_id = $studenCurceRegistration?->register_id;
                    $course_id = $studenCurceRegistration?->course_id;
                    $batch_id = $studenCurceRegistration?->batch_id;
                    $attedanceDate = Carbon::now()->toDateString();

                    $checkAttedance = Attedance::where('admission_id', $admission_id)->where('course_id', $course_id)->where('batch_id', $batch_id)->whereDate('date', $attedanceDate)->whereNotNull('in_time')->whereNull('out_time')->orderBy('id', 'DESC')->first();

                    /** If Record found return message */
                    if ($checkAttedance) {
                        return $this->sendError("This student Attedance all ready In.");
                    }
                    $coursereg = [
                        'admission_id' => $admission_id,
                        'course_id'    => $course_id,
                        'batch_id'     => $batch_id,
                        'date'         => Carbon::now()->toDateString(),
                        'in_time'      => Carbon::now()->toTimeString(),
                        'created_by'   => Auth::id(),
                    ];

                    Attedance::create($coursereg);
                    return $this->sendResponse([], 'Attendance marked successfully.');
                } else if ($request?->studentid) {
                    $studentIds = explode(',', $request->studentid);
                    $coursereg = [];
                    foreach ($studentIds as $value) {
                        $CourceRegistr = CourceRegistration::findOrFail($value);
                        $BatchRegistr = CourceRegistration::findOrFail($value);

                        $admission_id = $CourceRegistr?->register_id;
                        $course_id = $CourceRegistr?->course_id;
                        $batch_id = $CourceRegistr?->batch_id;
                        $attedanceDate = Carbon::now()->toDateString();

                        $checkAttedance = Attedance::where('admission_id', $admission_id)->where('course_id', $course_id)->where('batch_id', $batch_id)->whereDate('date', $attedanceDate)->whereNotNull('in_time')->whereNull('out_time')->orderBy('id', 'DESC')->first();

                        if (!$checkAttedance) {
                            $coursereg = [
                                'admission_id' => $CourceRegistr->register_id,
                                'course_id'    => $CourceRegistr->course_id,
                                'batch_id'     => $BatchRegistr->batch_id,
                                'date'         => Carbon::now()->toDateString(),
                                'in_time'      => Carbon::now()->toTimeString(),
                                'created_by'   => Auth::id(),
                            ];

                            Attedance::create($coursereg);
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
                    $studenCurceRegistration = CourceRegistration::where('id', $request->admissionId)->first();
                    if (!$studenCurceRegistration) {
                        return $this->sendError("Student course not found.");
                    }
                    $admission_id = $studenCurceRegistration?->register_id;
                    $course_id = $studenCurceRegistration?->course_id;
                    $batch_id = $studenCurceRegistration?->batch_id;
                    $attedanceDate = Carbon::now()->toDateString();

                    $checkAttedance = Attedance::where('admission_id', $admission_id)->where('course_id', $course_id)->where('batch_id', $batch_id)->whereDate('date', $attedanceDate)->whereNotNull('in_time')->whereNull('out_time')->orderBy('id', 'DESC')->first();

                    if (!$checkAttedance) {
                        return $this->sendError("This student attedance not in.");
                    }

                    $checkAttedance->out_time = Carbon::now()->toTimeString();
                    $checkAttedance->save();

                    return $this->sendResponse([], 'Attendance Out Marked successfully.');
                } else if ($request?->studentid) {

                    $studentIds = explode(',', $request->studentid);
                    $attendanceIds = explode(',', $request->attendanceid);

                    foreach ($studentIds as $index => $studentId) {
                        // Get the corresponding attendance ID
                        $attId = $attendanceIds[$index] ?? null;

                        if ($attId) {
                            // Find the attendance record by ID and update it
                            $attendance = Attedance::where('id', $attId)
                                ->whereDate('date', Carbon::today())
                                ->first();

                            if ($attendance) {
                                $attendance->update([
                                    'out_time' => Carbon::now()->toTimeString(),
                                    'updated_by' => Auth::id(),
                                ]);
                            }
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
        try {
            $modules = $this->modules;
            View::share('modules', $modules);

            $edit = Attedance::findOrFail($id);
            return view($modules['folder_path'] . '.form', compact('modules', 'edit'));
        } catch (\Exception $e) {
            return Redirect::route('dashboard')->withErrors($e->getMessage());
        }
    }

    public function update(AttedanceRequest $request, string $id)
    {
        $modules = $this->modules;
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
        $validator = Validator::make($request->all(), [
            'id' => ['required', Rule::exists($modules['table_name'], 'id')],
            'update_status' => ['required', 'in:active,inactive']
        ]);

        if ($validator->fails() && $isAjax) {
            return $this->sendError($validator->messages()->first(), $validator->messages(), [], 401);
        }

        try {
            $designation = Attedance::withTrashed()->findOrFail($request?->id);

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

            $restore_data = Attedance::withTrashed()->findOrFail($id);
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

            // Fetch all employees
            $employeeResponse = Http::withToken($token)->get($modules['api_ip'] . "api/Employees");

            if (!$employeeResponse->successful()) {
                return response()->json(['message' => 'Failed to fetch employee data.'], 500);
            }

            $employees = collect($employeeResponse->json());

            // Fetch device logs
            $logResponse = Http::withToken($token)->get($modules['api_ip'] . "api/DeviceLog/GetAllLogsByDate", [
                'FromDate' => $fromDate,
                'ToDate' => $toDate,
            ]);

            if (!$logResponse->successful()) {
                return response()->json(['message' => 'Failed to fetch logs from API.'], 500);
            }

            $logs = $logResponse->json();

            foreach ($logs as $log) {
                $attendance = Attedance::where('biometric_id', $log['Id'])->first();

                $date = \Carbon\Carbon::parse($log['IOTime'])->toDateString();
                $time = \Carbon\Carbon::parse($log['IOTime'])->format('H:i:s');
                $ioTime = \Carbon\Carbon::parse($log['IOTime'])->format('Y-m-d H:i:s');

                // Get employee name
                $employee = $employees->firstWhere('id', $log['UserId']);
                $employeeName = $employee['name'] ?? $log['UserName'] ?? null;

                $courseId = null;
                $batchId = null;
                $admissionId = null;


                if ($employeeName) {
                    $nameParts = explode(" ", $employeeName);
                    $firstName = $nameParts[0] ?? '';
                    $lastName = $nameParts[1] ?? '';

                    $admission = DB::table('admission')
                        ->whereRaw('LOWER(first_name) = ?', [strtolower($firstName)])
                        ->whereRaw('LOWER(last_name) = ?', [strtolower($lastName)])
                        ->first();

                    if ($admission) {
                        $admissionId = $admission->id;

                        $course = DB::table('cource_registration')
                            ->where('register_id', $admissionId)
                            ->orderByDesc('id') // In case multiple, get latest
                            ->first();

                        if ($course) {
                            $courseId = $course->course_id;
                            $batchId = $course->batch_id;

                            // dd($admissionId, $courseId, $batchId);
                        }
                    }
                }

                if ($attendance) {
                    if ($log['IOMode'] === 'in') {
                        $attendance->update([
                            'in_time' => $time,
                        ]);
                    } elseif ($log['IOMode'] === 'out') {
                        $attendance->update([
                            'out_time' => $time,
                        ]);
                    }
                } else {
                    if ($admissionId) {
                        Attedance::create([
                            'biometric_id' => $log['Id'],
                            'admission_id' => $admissionId,
                            'course_id' => $courseId,
                            'batch_id' => $batchId,
                            'date' => $date,
                            'in_time' => $log['IOMode'] === 'in' ? $time : null,
                            'out_time' => $log['IOMode'] === 'out' ? $time : null,
                            'DeviceKey' => $log['DeviceKey'] ?? null,
                            'DeviceName' => $log['DeviceName'] ?? null,
                            'UserId' => $log['UserId'] ?? null,
                            'EmpCode' => $log['EmpCode'] ?? null,
                            'UserName' => $employeeName,
                            'IOTime' => $ioTime ?? null,
                            'IOMode' => $log['IOMode'] ?? null,
                            'VerifyMode' => $log['VerifyMode'] ?? null,
                            'WorkCode' => $log['WorkCode'] ?? null,
                            'ImagePath' => $log['ImagePath'] ?? null,
                        ]);
                    }
                }
            }

            return response()->json(['message' => 'Attendance synced successfully.']);
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






}
