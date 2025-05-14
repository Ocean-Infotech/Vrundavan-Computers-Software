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
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class AdmissionController extends Controller
{
    public $modules = [];
    public function __construct()
    {
        $this->modules = [
            'title' => 'Admission',
            'folder_path' => 'admission',
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
        return view('admin.module.'.$modules['folder_path'].'.index');
        // try{
        //     $columns = [
        //         (object)[ 'data' => "course_name", 'name' => 'course_name', 'td_label' => 'Course Name' ],
        //         (object)[ 'data' => "course_fees", 'name' => 'course_fees', 'td_label' => 'Course Fees' ],
        //         (object)[ 'data' => "status", 'name' => 'status', 'td_label' => 'Status','className' =>  'w-5 text-center', 'width' => '10%' ],
        //         (object)[ 'data' => "action", 'name' => 'action', 'td_label' => 'Action', 'orderable' => false, 'searchable' => false, 'className' =>  'w-10 text-center' ],
		// 	];
        //     View::share("columns", $columns);

        //     $data = [];

        //     if($request->ajax()){
        //         // dd($request->all());
        //         $data = Admission::select('*');
        //         $data = $data->withTrashed();
        //         // $editPermisstion = (Auth::user()->can($modules["table_name"].'-edit')) ? true : false;
        //         // $deletePermisstion = (Auth::user()->can($modules["table_name"].'-delete')) ? true : false;
        //         $editPermisstion = true;
        //         $deletePermisstion = true;

        //         $returnData = Datatables::of($data)
        //         ->addIndexColumn()
        //         ->filter(function ($query) use ($request) {
        //             if ($request->has('search')) {
        //                 $query->where('course_name', 'like',"%".$request->search."%");
        //                 $query->orWhere('course_fees', 'like',"%".$request->search."%");
        //             }
        //         })
        //         ->editColumn('status', function ($row) use($modules) {
        //             $status = '';
        //             $dropdown = "";
        //             $dropdown .= '<ul class="dropdown-menu" style="">';
                    
        //             if ($row->status == "active") {
        //                 $status .= '<button type="button" class="btn btn-success btn-sm w-100 dropdown-toggle waves-effect waves-light" data-bs-toggle="dropdown" aria-expanded="false">' . ucfirst($row->status) . '</button>';
        //                 $dropdown .= '<li><a href="javascript:void(0)" class="dropdown-item waves-effect btn-label-danger update-status" data-url="' . route($modules["route"].'.status-update') . '" data-id="' . $row["id"] . '"  data-update_status="inactive">Inactive</a></li>';
        //                 // $btn = '<span class="badge bg-success bg-glow">Active</span>';
        //             } elseif ($row->status == "inactive") {
        //                 $status .= '<button type="button" class="btn btn-danger btn-sm w-100 dropdown-toggle waves-effect waves-light" data-bs-toggle="dropdown" aria-expanded="false">' . ucfirst($row->status) . '</button>';
        //                 $dropdown .= '<li><a href="javascript:void(0)" class="dropdown-item waves-effect btn-label-success update-status" data-url="' . route($modules["route"].'.status-update') . '" data-id="' . $row["id"] . '"  data-update_status="active">Active</a></li>';
        //                 // $btn = '<span class="badge bg-danger bg-glow">In-Active</span>';
        //             } else {
        //                 return $status;
        //             }
        //             $dropdown .= '</ul>';
        //             $status .= $dropdown;
        //             return $status;
        //         })
        //         ->addColumn('action', function($row) use ($modules,$editPermisstion, $deletePermisstion){
        //             $btn = '';
        //             if (!$row?->deleted_at) {
        //                 // $btn .= '<a href="javascript:void(0)" class="edit btn btn-primary btn-sm">View</a>';
        //                 if ($editPermisstion) {
        //                     $btn .= '<a href="' . route($modules["route"] . ".edit", [$row["id"]]) . '" class="btn btn-light btn-icon mx-1"><i class="fa-solid fa-pen-to-square"></i></a>';
        //                 }
        //                 if ($deletePermisstion) {
        //                     $btn .= '<a href="javascript:void(0)" data-id="' . $row->id . '" data-did="' . route($modules["route"] . ".destroy", [$row["id"]]) . '" class="btn btn-danger btn-icon deletebutton mx-1"><i class="fa-solid fa-trash"></i></i></a>';
        //                 }
        //             } else {
        //                 $btn .= '<a href="javascript:void(0)" data-restore="' . route($modules["route"] . ".restore", ['id' => $row["id"]]) . '" class="btn btn-light mx-1 record-restore" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Restore Data"><i class="ti ti-history"></i> Restore</a>';
        //             }
        //             // $btn .= '<a href="javascript:void(0)" class="btn btn-info btn-icon mr-2"><i class="fa-solid fa-key"></i></a>';
        //             if ($btn == '') {
        //                 $btn = '-';
        //             }
                        
        //             return $btn;
        //         })
        //         ->rawColumns(['status','action'])
        //         ->make(true);
        //         return $returnData;
        //     }
        //     return view('admin.module.'.$modules['folder_path'].'.index',compact('data'));
        // } catch(\Exception $e) {
        //     return Redirect::route('dashboard')->withErrors($e->getMessage());
        // }
    }

    public function create()
    {
        $modules = $this->modules;
        View::share('modules', $modules);
        return view('admin.module.'.$modules['folder_path'].'.form',compact('modules'));
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

            return Redirect::route($modules['route'] . '.index')->withSuccess($modules['title'] . ' create successfully');
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }
}
