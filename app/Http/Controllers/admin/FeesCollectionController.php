<?php

namespace App\Http\Controllers\admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\FeesCollectionRequest;
use App\Models\Admission;
use App\Models\CourceRegistration;
use App\Models\FeesCollection;
use App\Models\Master\MasterCourse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;
use NumberFormatter;

use Illuminate\Support\Facades\Validator;

class FeesCollectionController extends Controller
{

    public $modules = [];
    public function __construct()
    {
        $this->modules = [
            'title' => 'Fees Collection',
            'folder_path' => 'software.module.fees-collection',
            'route' => 'fees-collection',
            'table_name' => (new FeesCollection())->getTable(),
            'permission_prefix' => 'fees-collection',
        ];

        // $this->middleware('permission:'.$this->modules['permission_prefix'].'-list', ['only' => ['index','show']]);
        // $this->middleware('permission:'.$this->modules['permission_prefix'].'-create', ['only' => ['create','store']]);
        // $this->middleware('permission:'.$this->modules['permission_prefix'].'-edit', ['only' => ['edit','update']]);
        // $this->middleware('permission:'.$this->modules['permission_prefix'].'-delete', ['only' => ['destroy']]);
    }
    /**
     * Display a listing of the resource.
     */
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
        View::share('modules', $modules);
        try {
            $columns = [
                (object)['data' => "student_id", 'name' => 'student_id', 'td_label' => 'Student Id'],
                (object)['data' => "admission_id", 'name' => 'admission_id', 'td_label' => 'Admission Id'],
                (object)['data' => 'course.course_name', 'name' => 'course_id', 'td_label' => 'Course Name'],
                (object)['data' => "student_name", 'name' => 'student_name', 'td_label' => 'Student  Name'],
                (object)['data' => "year_semester", 'name' => 'year_semester', 'td_label' => 'Year / Semester'],
                (object)['data' => "date", 'name' => 'date', 'td_label' => 'Date'],
                (object)['data' => "fees", 'name' => 'fees', 'td_label' => 'Course Fees'],
                (object)['data' => "mode", 'name' => 'mode', 'td_label' => 'Mode'],
                // (object)['data' => "status", 'name' => 'status', 'td_label' => 'Status', 'className' => 'w-5 text-center'],
                // (object)['data' => "action", 'name' => 'action', 'td_label' => 'Action', 'orderable' => false, 'searchable' => false, 'className' => 'w-10 text-center'],
            ];
            View::share("columns", $columns);

            $data = [];

            if ($request->ajax()) {
                // dd($request->all());
             if ($request->ajax()) {

    $search = trim($request->search);

    $data = FeesCollection::query()
        ->with(['course','admission','batch'])
        ->withTrashed();

    if ($search != '') {

        // 🔍 1. find matching admission IDs by name OR id
        $matchedAdmissions = Admission::where('id', $search)
            ->orWhere('first_name', 'LIKE', "%$search%")
            ->orWhere('last_name', 'LIKE', "%$search%")
            ->orWhere('father_name', 'LIKE', "%$search%")
            ->pluck('id');   // EX: [84, 92]

        // 🔍 2. Convert student ID → registration IDs
        $courseRegIds = CourceRegistration::whereIn('register_id', $matchedAdmissions)
            ->pluck('id');   // EX: [86, 119]

        // 🔍 3. Final FeesCollection Filter
        $data->whereIn('admission_id', $courseRegIds);
    } else {
        // No filter → show 0 records
        $data->whereRaw('1=0');
    }

    return DataTables::of($data)
        ->addIndexColumn()
        ->editColumn('student_name', function ($row) {
            return trim(($row->admission->last_name ?? '') . ' ' .
                        ($row->admission->first_name ?? '') . ' ' .
                        ($row->admission->father_name ?? ''));
        })
        ->editColumn('date', function ($row) {
            return $row->date ? \Carbon\Carbon::parse($row->date)->format('d-m-Y') : '-';
        })
        ->make(true);
}

                $data = $data->withTrashed();
                $data = $data->with(['course','admission']);
                // dd("L-75", $data->get());
                $returnData = Datatables::of($data)
                    ->addIndexColumn()
                    ->filter(function ($query) use ($request) {
                        if ($request->has('search') && $request->search) {
                            $query->where('admission_id', $request->search);
                            // $query->orWhere('course_fees', 'like',"%".$request->search."%");
                        }
                    })
                    ->editColumn('date', function ($row) use ($modules) {
                        if ($row?->date) {
                            return Helper::convert_date($row?->date, "Y-m-d", 'd-m-Y');
                        }
                        return "-";
                    })
                    ->editColumn('student_name', function ($row) {
                        $first  = $row?->admission?->first_name ?? '';
                         $last   = $row?->admission?->last_name ?? '';
                        $father = $row?->admission?->father_name ?? '';


                        return trim("$first $last  $father");   // Surname First Father
                    })
                    ->editColumn('status', function ($row) use ($modules) {
                        $btn = '';
                        // $btn = ucfirst($row->status);

                        // $btn .= '<ul class="dropdown-menu" style="">
                        // <li><a href="javascript:void(0)" class="dropdown-item waves-effect btn-label-success update-status" data-url="' . route('master-country.status-update') . '" data-id="' . $row["id"] . '"  data-update_status="inactive">Active</a></li>
                        // <li><a href="javascript:void(0)" class="dropdown-item waves-effect btn-label-danger update-status" data-url="' . route('master-country.status-update') . '" data-id="' . $row["id"] . '"  data-update_status="active">Inactive</a></li>
                        // </ul>';

                        $dropdown = "";
                        $dropdown .= '<ul class="dropdown-menu" style="">';

                        if ($row->status == "active") {
                            $btn .= '<button type="button" class="btn btn-success btn-sm w-100
                            dropdown-toggle waves-effect waves-light" data-bs-toggle="dropdown" aria-expanded="false">' . ucfirst($row->status) . '</button>';
                            $dropdown .= '<li><a href="javascript:void(0)" class="dropdown-item
                             waves-effect btn-label-danger update-status" data-url="' . route($modules['route'] . '.status-update') . '" data-id="' . $row["id"] . '"  data-update_status="inactive">Inactive</a></li>';
                            // $btn = '<span class="badge bg-success bg-glow">Active</span>';
                        } elseif ($row->status == "inactive") {
                            $btn .= '<button type="button" class="btn btn-danger btn-sm w-100
                            dropdown-toggle waves-effect waves-light" data-bs-toggle="dropdown"
                            aria-expanded="false">' . ucfirst($row->status) . '</button>';
                            $dropdown .= '<li><a href="javascript:void(0)" class="dropdown-item
                            waves-effect btn-label-success update-status" data-url="' . route($modules['route'] . '.status-update') . '" data-id="' . $row["id"] . '"  data-update_status="active">Active</a></li>';
                            // $btn = '<span class="badge bg-danger bg-glow">In-Active</span>';
                        } else {
                            return $btn;
                        }
                        $dropdown .= '</ul>';
                        $btn .= $dropdown;
                        return $btn;
                    })


                    ->addColumn('action', function ($row) use ($modules) {
                        $btn = '';
                        if (!$row?->deleted_at) {
                            // $btn .= '<a href="javascript:void(0)" class="edit btn btn-primary btn-sm">View</a>';
                            if ($modules['permission_edit']) {
                                $btn .= '<a href="' . route($modules["route"] . ".edit", [$row["id"]]) . '" class="btn btn-light btn-icon mx-1"><i class="fa-solid fa-pen-to-square"></i></a>';
                            }
                            if ($modules['permission_delete']) {
                                $btn .= '<a href="javascript:void(0)" data-id="' . $row->id . '" data-did="' . route($modules["route"] . ".destroy", [$row["id"]]) . '" class="btn btn-danger btn-icon deletebutton mx-1"><i class="fa-solid fa-trash"></i></i></a>';
                            }
                            // if ($modules) {
                            //     $btn .= '<a href="javascript:void(0)" data-id="' . $row->id . '" data-did="' . route($modules["route"] . ".destroy", [$row["id"]]) . '" class="btn btn-warning btn-icon mx-1"><i class="fa-solid fa-download"></i></i></a>';
                            // }
                        } else {
                            $btn .= '<a href="javascript:void(0)" data-restore="' . route($modules["route"] . ".restore", ['id' => $row["id"]]) . '" class="btn btn-light mx-1 record-restore" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Restore Data"><i class="ti ti-history"></i> Restore</a>';
                        }
                        // $btn .= '<a href="javascript:void(0)" class="btn btn-info btn-icon mr-2"><i class="fa-solid fa-key"></i></a>';
                        if ($btn == '') {
                            $btn = '-';
                        }

                        return $btn;
                    })
                    ->rawColumns(['status', 'action'])
                    ->make(true);
                return $returnData;
            }
            return view($modules['folder_path'] . '.index', compact('data'));
        } catch (\Exception $e) {
            return Redirect::route('dashboard')->withErrors($e->getMessage());
        }
    }


    // public function search_fees_collection_details(Request $request)
    // {
    //     $id = $request->id;

    //     try {
    //         // Get course registration with related course, admission, batch
    //         $getcource = CourceRegistration::where('id', $id)->with(['course', 'admission', 'batch'])->get();
    //         $getcourceSingle = CourceRegistration::where('id', $id)->with(['course', 'admission', 'batch'])->first();
    //         // dd($getcource);
    //         // Get semester fee from course
    //         $semester_fee = ($getcourceSingle->course->course_fees != '') ?  $getcourceSingle->course->course_fees / $getcourceSingle->course->semester : 0;
    //         // dd($semester_fee);


    //         $feesCollections = FeesCollection::where('admission_id', $id)->get();


    //         $semesterPaid = [];

    //         foreach ($feesCollections as $fee) {
    //             $sem = $fee->year_semester;
    //             $semesterPaid[$sem] = ($semesterPaid[$sem] ?? 0) + $fee->fees;
    //         }
    //         //dd($semester_fee);
    //         // Collect semesters where pending = 0
    //         $paid_semesters = [];
    //         $unpaid_semesters = [];

    //         foreach ($semesterPaid as $semester => $totalPaid) {
    //             $pending = $semester_fee - $totalPaid;

    //             if ($pending <= 0) {
    //                 $paid_semesters[] = $semester;
    //             } else {
    //                 $unpaid_semesters[] = [
    //                     'semester' => $semester,
    //                     'pending_fee' => $pending
    //                 ];
    //             }
    //         }

    //         return response()->json([
    //             'data' => $getcource,
    //             'fees_collection' => $feesCollections,
    //             'paid_semesters' => $paid_semesters,
    //             'unpaid_semesters' => $unpaid_semesters,
    //             'course_fee' => $semester_fee,
    //         ]);
    //     } catch (\Exception $e) {
    //         return Redirect::route('software.dashboard')->withErrors($e->getMessage());
    //     }
    // }
    public function search_fees_collection_details(Request $request)
    {
        $id = $request->id;

        try {
            // Get course registration with related course, admission, batch
            $getcource = CourceRegistration::where('register_id', $id)->with(['course', 'admission', 'batch'])->get();
            $getcourceSingle = CourceRegistration::where('register_id', $id)->with(['course', 'admission', 'batch'])->first();

            if (!$getcourceSingle || !$getcourceSingle->course) {
                return response()->json([
                    'data' => [],
                    'fees_collection' => [],
                    'paid_semesters' => [],
                    'unpaid_semesters' => [],
                    'course_fee' => 0,
                ]);
            }

            // Calculate semester fee
            $student_fee = ($getcourceSingle->fee != '' && $getcourceSingle->fee > 0) ? $getcourceSingle->fee : ($getcourceSingle->course->course_fees ?? 0);
            $active_semesters = ($getcourceSingle->is_lateral_entry == 1 && $getcourceSingle->joining_semester)
                ? ($getcourceSingle->course->semester - $getcourceSingle->joining_semester + 1)
                : $getcourceSingle->course->semester;
            $semester_fee = ($student_fee != '' && $active_semesters != 0)
                ? $student_fee / $active_semesters
                : 0;
            //dd($semester_fee);
            // Get fees collection entries
         $feesCollections = FeesCollection::where('admission_id', $getcourceSingle->id)
    ->orderBy('id', 'desc')
    ->get();


            // Track total fees paid per semester
            $semesterPaid = [];
            foreach ($feesCollections as $fee) {
                $sem = strtolower($fee->year_semester ?? '');
                $semesterPaid[$sem] = ($semesterPaid[$sem] ?? 0) + $fee->fees;
            }

            // Initialize semester tracking
            $total_semesters = $getcourceSingle->course->semester;
            $paid_semesters = [];
            $unpaid_semesters = [];

            $semesterInWords = [];
            $semesterInWords[1] = "first";
            $semesterInWords[2] = "second";
            $semesterInWords[3] = "third";
            $semesterInWords[4] = "four";
            $semesterInWords[5] = "five";
            $semesterInWords[6] = "six";
            $semesterInWords[7] = "saven";
            $semesterInWords[8] = "eight";
            // $semesterInWords = array_flip($semesterInWords);

            // Loop over all semesters to calculate pending fees
            $startSem = $getcourceSingle->joining_semester ?? 1;
            for ($sem = $startSem; $sem <= $total_semesters; $sem++) {
                $sem_name = $semesterInWords[$sem];
                $paid = $semesterPaid[$sem_name] ?? $semesterPaid[$sem] ?? 0;
                $pending = $semester_fee - $paid;

                if ($pending <= 0) {
                    $paid_semesters[] = $sem;
                } else {
                    $unpaid_semesters[] = [
                        'semester' => $sem,
                        'pending_fee' => $pending,
                    ];
                }
            }

            // Return result as JSON
            return response()->json([
                'data' => $getcource,
                'fees_collection' => $feesCollections,
                'paid_semesters' => $paid_semesters,
                'unpaid_semesters' => $unpaid_semesters,
                'course_fee' => $semester_fee,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }



    public function check_master_password(Request $request)
    {
        $user_id = Auth::user()?->id;
        try {
            $user = User::where('id', $user_id)->first(); // or find by ID/email if needed

            if ($user && Hash::check($request->password, $user->password)) {
                return response()->json(['status' => 'true']);
            } else {
                return response()->json(['status' => 'false']);
            }
        } catch (\Exception $e) {
            return Redirect::route('software.dashboard')->withErrors($e->getMessage());
        }
    }
    // public function getDetails($id)
    // {
    //     $details = Admission::find($id);

    //     if (!$details) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Details not found',
    //             'requested_id' => $id,
    //         ], 404);
    //     }

    //     return response()->json(['status' => 'success', 'data' => $details]);
    // }

    public function getDetails($id)
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
        return view($modules['folder_path'] . '.form', compact('modules', 'id'));
    }




    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $modules = $this->modules;
        $modules['login_user_role'] = Helper::getLoginUserRole();
        $modules['permission_add'] = Helper::directCan($modules['permission_prefix'] . '-create');

        if (!$modules['permission_add']) {
            abort(403, 'User does not have the right permissions.');
        }

        View::share('modules', $modules);

        $student_id = $request->student_id;
        $selectedSemester = $request->semester;
        // dd($selectedSemester);


        return view($modules['folder_path'] . '.form', compact('modules', 'student_id', 'selectedSemester'));
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(FeesCollectionRequest $request)
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

        try {
            $loginUserId = Auth::user()->id;
            $validated['created_by'] = $loginUserId;
            // return $validated;
            $feesCollection = FeesCollection::create($validated);
// dd($request->all(),$validated);
            return Redirect::route('software.fees-collection.print', $feesCollection->id);
        } catch (\Exception $e) {
            return Redirect::back()->withErrors($e->getMessage());
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
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

            $edit = FeesCollection::with(['course', 'admission'])->findOrFail($id);
            $admission_id = $edit->admission_id;
            $getfeescollection = FeesCollection::where('admission_id', $admission_id)->get();
            // dd($edit->toArray(), $getfeescollection->toArray());
            // return $edit;

            return view($modules['folder_path'] . '.form', compact('modules', 'edit', 'getfeescollection'));
        } catch (\Exception $e) {
            return Redirect::route('software.dashboard')->withErrors($e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(FeesCollectionRequest $request, string $id)
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
            $validated = $request->validated();
            $validated['updated_by'] = Auth::user()->id;

            $updateData = FeesCollection::findOrFail($id);
            $updateData->update($validated);

            return Redirect::route($modules['route'] . '.index')
                ->withSuccess($modules['title'] . ' updated successfully');
        } catch (\Exception $e) {
            return Redirect::route('dashboard')->withErrors($e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }


    public function print($id)
    {
        $modules = $this->modules;
        $modules['route'] = 'fees-collection';
        View::share('modules', $modules);

     $feesData = FeesCollection::with(['course','registration.batch'])
    ->find($id);


        if (!$feesData) {
            abort(404, 'Receipt not found');
        }

        $feesDetails = json_decode($feesData->fee_details, true);

        $totalFee = 0;
        if ($feesDetails && is_array($feesDetails)) {
            foreach ($feesDetails as $fee) {
                $totalFee += $fee['amount'];
            }
        } else {
            $totalFee = $feesData->fees;
        }
        $modules = [
            'route' => 'fees-collection',
        ];
        //  Convert to words
        // $totalInWords = $this->$totalFee;
        $feesDetails = $feesDetails ?: [];

        return view('software.module.fees-collection.print', compact('feesData', 'feesDetails', 'totalFee'));
    }
}
