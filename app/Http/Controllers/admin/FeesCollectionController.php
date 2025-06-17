<?php

namespace App\Http\Controllers\admin;

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
            'permisstion_prefix' => 'fees-collection',
        ];

        // $this->middleware('permission:'.$this->modules['permisstion_prefix'].'-list', ['only' => ['index','show']]);
        // $this->middleware('permission:'.$this->modules['permisstion_prefix'].'-create', ['only' => ['create','store']]);
        // $this->middleware('permission:'.$this->modules['permisstion_prefix'].'-edit', ['only' => ['edit','update']]);
        // $this->middleware('permission:'.$this->modules['permisstion_prefix'].'-delete', ['only' => ['destroy']]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $modules = $this->modules;
        View::share('modules', $modules);
        try {
            $columns = [
                (object)['data' => "student_id", 'name' => 'student_id', 'td_label' => 'Student Id'],
                (object)['data' => "admission_id", 'name' => 'admission_id', 'td_label' => 'Addmission Id'],
                (object)['data' => 'course.course_name', 'name' => 'course_id', 'td_label' => 'Course Name'],
                (object)['data' => "student_name", 'name' => 'student_name', 'td_label' => 'Student  Name'],
                (object)['data' => "year_semester", 'name' => 'year_semester', 'td_label' => 'Year / Semester'],
                (object)['data' => "date", 'name' => 'date', 'td_label' => 'Date'],
                (object)['data' => "fees", 'name' => 'fees', 'td_label' => 'Course Fees'],
                (object)['data' => "mode", 'name' => 'mode', 'td_label' => 'Mode'],
                (object)['data' => "status", 'name' => 'status', 'td_label' => 'Status', 'className' => 'w-5 text-center'],
                (object)['data' => "action", 'name' => 'action', 'td_label' => 'Action', 'orderable' => false, 'searchable' => false, 'className' => 'w-10 text-center'],
            ];
            View::share("columns", $columns);

            $data = [];

            if ($request->ajax()) {
                // dd($request->all());
                $data = FeesCollection::select('*')->where('student_id', $request->search);
                $data = $data->withTrashed();
                $data = $data->with(['course']);
                // $editPermisstion = (Auth::user()->can($modules["table_name"].'-edit')) ? true : false;
                // $deletePermisstion = (Auth::user()->can($modules["table_name"].'-delete')) ? true : false;
                $editPermisstion = true;
                $deletePermisstion = true;

                $returnData = Datatables::of($data)
                    ->addIndexColumn()
                    ->filter(function ($query) use ($request) {
                        if ($request->has('search') && $request->search) {
                            $query->where('student_id', $request->search);
                            // $query->orWhere('course_fees', 'like',"%".$request->search."%");
                        }
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
                    ->addColumn('action', function ($row) use ($modules, $editPermisstion, $deletePermisstion) {
                        $btn = '';
                        if (!$row?->deleted_at) {
                            // $btn .= '<a href="javascript:void(0)" class="edit btn btn-primary btn-sm">View</a>';
                            if ($editPermisstion) {
                                $btn .= '<a href="' . route($modules["route"] . ".edit", [$row["id"]]) . '" class="btn btn-light btn-icon mx-1"><i class="fa-solid fa-pen-to-square"></i></a>';
                            }
                            if ($deletePermisstion) {
                                $btn .= '<a href="javascript:void(0)" data-id="' . $row->id . '" data-did="' . route($modules["route"] . ".destroy", [$row["id"]]) . '" class="btn btn-danger btn-icon deletebutton mx-1"><i class="fa-solid fa-trash"></i></i></a>';
                            }
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


    public function search_fees_collection_details(Request $request)
    {
        $id = $request->id;

        try {
            $getcource = CourceRegistration::where('id', $id)->with(['course', 'admission', 'batch'])->get();

            $getfeescollection = FeesCollection::where('admission_id', $id)->get();

            return response()->json([
                'data' => $getcource,
                'fees_collection'=> $getfeescollection,
            ]);
        } catch (\Exception $e) {
            return Redirect::route('software.dashboard')->withErrors($e->getMessage());
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
        View::share('modules', $modules);
        return view($modules['folder_path'] . '.form', compact('modules', 'id'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $modules = $this->modules;
        View::share('modules', $modules);
        return view($modules['folder_path'] . '.form', compact('modules'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(FeesCollectionRequest $request)
    {
        $modules = $this->modules;
        View::share('modules', $modules);

        $validated = $request->validated();
        try {

            $loginUserId = Auth::user()->id;

            // return $this->adminLoginUser;
            $validated['created_by'] = $loginUserId;
            // return $validated;
            FeesCollection::create($validated);

            return Redirect::route($modules['route'] . '.index')->withSuccess($modules['title'] . ' create successfully');
        } catch (\Exception $e) {
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
        try{
            $modules = $this->modules;
            View::share('modules', $modules);

            $edit = FeesCollection::with(['course', 'admission'])->findOrFail($id);
            $admission_id = $edit->admission_id;
            $getfeescollection = FeesCollection::where('admission_id', $admission_id)->get();
            //return $edit;

            return view($modules['folder_path'].'.form',compact('modules','edit','getfeescollection'));
        } catch(\Exception $e) {
            return Redirect::route('software.dashboard')->withErrors($e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(FeesCollectionRequest $request, string $id)
    {
        $modules = $this->modules;
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
        $feesData = FeesCollection::find($id);

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

        // Convert to words
        // $totalInWords = $this->$totalFee;
        $feesDetails = $feesDetails ?: [];

        return view('software.module.fees-collection.print', compact('feesData', 'feesDetails', 'totalFee'));
    }
}
