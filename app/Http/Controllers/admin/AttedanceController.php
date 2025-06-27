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
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

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
                (object)['data' => "student_name", 'name' => 'admission_id', 'td_label' => 'Student Name'],
                (object)['data' => "course.course_name", 'name' => 'course_id', 'td_label' => 'Course Name'],
                (object)['data' => "batch.batch_name", 'name' => 'batch_id', 'td_label' => 'Batch Name'],
                (object)['data' => "date", 'name' => 'date', 'td_label' => 'Date'],
                (object)['data' => "in_time", 'name' => 'in_time', 'td_label' => 'In Time'],
                (object)['data' => "out_time", 'name' => 'out_time', 'td_label' => 'Out Time'],
                (object)['data' => "leave", 'name' => 'leave', 'td_label' => 'Leave'],
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
                    ->editColumn('date', function ($row) {
                        return Carbon::parse($row->date)->format('d-m-Y');
                    })
                    ->editColumn('in_time', function ($row) {
                        if($row->in_time==''){
                            $in_time = '<p class="text-center">-</p>';
                        }else{
                            $in_time = Carbon::parse($row->in_time)->format('h:i A');
                        }
                        return $in_time;
                    })
                    ->editColumn('out_time', function ($row) {
                        if($row->out_time==''){
                            $out_time = '<p class="text-center">-</p>';
                        }else{
                            $out_time = Carbon::parse($row->out_time)->format('h:i A');
                        }

                        return $out_time;
                    })
                    ->editColumn('leave', function ($row) {
                        if($row->leave==''){
                            $leave = '<p class="text-center">-</p>';
                        }else{
                            $leave = '<p class="text-center text-danger"><strong>Leave</strong></p>';
                        }
                        return $leave;
                    })


                    ->rawColumns(['student_name','date','in_time','out_time','leave'])
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
}
