<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Admission;
use App\Models\CourceRegistration;
use App\Models\FeesCollection;
use App\Models\Master\MasterCourse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

use Illuminate\Support\Facades\Validator;

class FeesCollectionController extends Controller
{

     public $modules = [];
    public function __construct()
    {
        $this->modules = [
            'title' => 'Fees Collection',
            'folder_path' => 'fees-collection',
            'route' => 'fees-collection',
            'table_name' => (new FeesCollection())->getTable(),
            'permisstion_prefix' => 'fees-collection',
        ];

        // $this->middleware('permission:'.$this->modules['permisstion_prefix'].'-list', ['only' => ['index','show']]);
        // $this->middleware('permission:'.$this->modules['permisstion_prefix'].'-create', ['only' => ['create','store']]);
        // $this->middleware('permission:'.$this->modules['permisstion_prefix'].'-edit', ['only' => ['edit','update']]);
        // $this->middleware('permission:'.$this->modules['permisstion_prefix'].'-delete', ['only' => ['destroy']]);
    } /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $modules = $this->modules;
        View::share('modules', $modules);
        try{
        $columns = [
            (object)['data' => "student_id ", 'name' => 'student_id', 'td_label' => 'Student Id '],
            (object)['data' => "admission_id ", 'name' => 'admission_id', 'td_label' => 'Addmission Id '],
            (object)['data' => "course_name", 'name' => 'course_name', 'td_label' => 'Course Name'],
            (object)['data' => "student_name", 'name' => 'student_name', 'td_label' => 'Student  Name'],
            (object)['data' => "year_semester", 'name' => 'year_semester', 'td_label' => 'Year / Semester'],
            (object)['data' => "date", 'name' => 'date', 'td_label' => 'Date'],
            (object)['data' => "course_fees", 'name' => 'course_fees', 'td_label' => 'Course Fees'],
            (object)['data' => "mode", 'name' => 'mode', 'td_label' => 'Mode '],
            (object)['data' => "status", 'name' => 'status', 'td_label' => 'Status', 'className' => 'w-5 text-center'],
            (object)['data' => "action", 'name' => 'action', 'td_label' => 'Action', 'orderable' => false, 'searchable' => false, 'className' => 'w-10 text-center'],
        ];
            View::share("columns", $columns);

            $data = [];

            if($request->ajax()){
                // dd($request->all());
                $data = FeesCollection::select('*');
                $data = $data->withTrashed();
                // $editPermisstion = (Auth::user()->can($modules["table_name"].'-edit')) ? true : false;
                // $deletePermisstion = (Auth::user()->can($modules["table_name"].'-delete')) ? true : false;
                $editPermisstion = true;
                $deletePermisstion = true;

                $returnData = Datatables::of($data)
                ->addIndexColumn()
                ->filter(function ($query) use ($request) {
                    // if ($request->has('search')) {
                    //     $query->where('course_name', 'like',"%".$request->search."%");
                    //     $query->orWhere('course_fees', 'like',"%".$request->search."%");
                    // }
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
                ->addColumn('action', function($row) use ($modules,$editPermisstion, $deletePermisstion){
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
                ->rawColumns(['status','action'])
                ->make(true);
                return $returnData;
            }
            return view('admin.module.'.$modules['folder_path'].'.index',compact('data'));
        } catch(\Exception $e) {
            return Redirect::route('dashboard')->withErrors($e->getMessage());
        }
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
       $modules = $this->modules;
        View::share('modules', $modules);
         $admissions = Admission::all();
          $courses = MasterCourse::all();
        return view('admin.module.'.$modules['folder_path'].'.form',compact('modules','admissions','courses'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
