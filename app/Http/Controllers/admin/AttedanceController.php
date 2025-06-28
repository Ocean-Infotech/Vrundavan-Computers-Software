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
use App\Models\master\MasterClass;
use Carbon\Carbon;
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
                (object)['data' => 'student_name', 'name' => 'admission_id', 'td_label' => 'Student Name'],
                (object)['data' => 'course.course_name', 'name' => 'course_id', 'td_label' => 'Course Name'],
                (object)['data' => "batch.batch_name", 'name' => 'batch_id', 'td_label' => 'Batch Name'],
                (object)['data' => "date", 'name' => 'date', 'td_label' => 'Date'],
                (object)['data' => 'in_and_out_and_leve_status', 'name' => 'in_and_out_and_leve_status', 'td_label' => 'In / Out / Leave Status'],
            ];
            View::share("columns", $columns);

            $admissions = Admission::whereIn('status', ['created', 'completed'])->get();
            $classes = MasterBatch::where('status', 'active')->get();

            if ($request->ajax()) {
                $data = Attedance::select('*');
                $data = $data->withTrashed();
                $data = $data->orderBy('created_at', 'desc');
                $data = $data->with(['course', 'admission', 'batch']);

                $editPermission = true;
                $deletePermission = true;

                return Datatables::of($data)
                    ->filter(function ($query) use ($request) {
                        if ($request->has('filter_registers_id') && !empty($request->filter_registers_id)) {
                            $query->where('admission_id', $request->filter_registers_id);
                        }
                        if ($request->has('filter_batch_id') && !empty($request->filter_batch_id)) {
                            $query->where('batch_id', $request->filter_batch_id);
                        }

                        if ($request->has('selectedStartDate') && $request->selectedStartDate != null) {
                            $query->whereDate('date', '>=', $request->selectedStartDate);
                        }

                        if ($request->has('selectedEndDate') && $request->selectedEndDate != null) {
                            $query->whereDate('date', '<=', $request->selectedEndDate);
                        }
                    })
                    ->addIndexColumn()
                    ->editColumn('student_name', function ($row) {
                        $first = $row->admission->first_name ?? '';
                        $father = $row->admission->father_name ?? '';
                        $last = $row->admission->last_name ?? '';
                        return trim("{$first} {$father} {$last}");
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
                    ->rawColumns(['student_name', 'in_and_out_and_leve_status'])
                    ->make(true);
            }

            // return view($modules['folder_path'] . '.index');
            return view($modules['folder_path'] . '.index',  compact('modules', 'admissions', 'classes'));
        } catch (\Exception $e) {
            return redirect()->route('software.dashboard')->withErrors($e->getMessage());
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

        if ($request->filled('filter_batch') && $request->filter_batch) {
            $data->where('batch_id', $request->input('filter_batch'));
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
                        'in_time'      => Carbon::now()->toTimeString(),
                        'created_by'   => Auth::id(),
                    ];

                    Attedance::create($coursereg);
                }
                //dd('L-176', $coursereg); // Now you’ll see all values
                return response()->json([
                    'status' => true,
                    'message' => 'Attendance marked successfully.',
                ]);
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
        try {
            $fromDate = now()->toDateString();
            $toDate = now()->toDateString();

            $token = Session::get('external_api_token');

            $response = Http::withToken($token)
                ->get("http://192.168.1.88:88/api/DeviceLog/GetAllLogsByDate", [
                    'FromDate' => $fromDate,
                    'ToDate' => $toDate,
                ]);

            if ($response->successful()) {

                $logs = $response->json();

                // dd($logs);
                return response()->json([
                    'message' => 'Raw API response:',
                    'data' => $logs
                ]);

                // foreach ($logs as $log) {
                //     Attedance::updateOrCreate(
                //         [
                //             'biometric_id' => $log['Id'],
                //         ],
                //         [
                //             'admission_id' => $log['UserId'] ?? null,
                //             'date' => $log['DateTime'] ?? now(),
                //             'status' => 'Present',
                //         ]
                //     );
                // }

                return response()->json(['message' => 'Attendance synced successfully.']);
            }

            return response()->json(['message' => 'Failed to fetch logs from API.'], 500);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // public function syncFromDeviceLogs()
    // {
    //     try {
    //         $fromDate = Carbon::now()->format('Y-m-d');
    //         $toDate = $fromDate;

    //         $url = "http://192.168.1.88:88/api/DeviceLog/GetAllLogsByDate?FromDate={$fromDate}&ToDate={$toDate}";

    //         $token = Session::get('external_api_token');

    //         $response = Http::withHeaders([
    //             'Authorization' => $token,
    //         ])->get($url);

    //         if (!$response->successful()) {
    //             return response()->json(['error' => 'Failed to fetch data from device API.'], 500);
    //         }

    //         $logs = $response->json();
    //         dd($logs);
    //         $newCount = 0;

    //         foreach ($logs as $log) {
    //             $admissionId = $log['AdmissionId'] ?? null; // update if key is different
    //             $logDateTime = Carbon::parse($log['LogDate']);

    //             $date = $logDateTime->toDateString();
    //             $time = $logDateTime->toTimeString();

    //             if (!$admissionId) {
    //                 continue; // skip if ID is missing
    //             }

    //             // Check for duplicates
    //             $alreadyExists = Attedance::where('admission_id', $admissionId)
    //                 ->where('date', $date)
    //                 ->where(function ($q) use ($time) {
    //                     $q->where('in_time', $time)
    //                         ->orWhere('out_time', $time);
    //                 })
    //                 ->exists();

    //             if (!$alreadyExists) {
    //                 // Logic to determine in/out time
    //                 $lastAttendance = Attedance::where('admission_id', $admissionId)
    //                     ->where('date', $date)
    //                     ->first();

    //                 if (!$lastAttendance) {
    //                     Attedance::create([
    //                         'admission_id' => $admissionId,
    //                         'date' => $date,
    //                         'in_time' => $time,
    //                         'created_by' => Auth::user()?->id ?? 1,
    //                         'updated_by' => Auth::user()?->id ?? 1,
    //                     ]);
    //                 } else if (!$lastAttendance->out_time) {
    //                     $lastAttendance->update([
    //                         'out_time' => $time,
    //                         'updated_by' => Auth::user()?->id ?? 1,
    //                     ]);
    //                 }

    //                 $newCount++;
    //             }
    //         }

    //         return response()->json([
    //             'message' => "Synced successfully. {$newCount} new records added.",
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json(['error' => $e->getMessage()], 500);
    //     }
    // }
}
