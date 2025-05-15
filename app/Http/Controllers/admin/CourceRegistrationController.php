<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\CourceRegistration;
use App\Models\Master\MasterCourse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\Facades\DataTables;

class CourceRegistrationController extends Controller
{
    public $modules = [];
    public function __construct()
    {
        $this->modules = [
            'title' => 'cource Registration',
            'folder_path' => 'cource-registration',
            'route' => 'cource-registration',
            'table_name' => (new CourceRegistration())->getTable(),
            'permisstion_prefix' => 'cource-registration',
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
                (object)['data' => 'course_id', 'name' => 'course_id', 'td_label' => 'Course Name'],
                (object)['data' => 'fees', 'name' => 'fees', 'td_label' => 'Course Fees'],
                (object)['data' => 'date', 'name' => 'date', 'td_label' => 'Date'],
                (object)['data' => 'status', 'name' => 'status', 'td_label' => 'Status', 'className' => 'w-5 text-center', 'width' => '10%', 'orderable' => false],
                (object)['data' => 'action', 'name' => 'action', 'td_label' => 'Action', 'className' => 'w-10 text-center', 'orderable' => false, 'searchable' => false],
            ];
            View::share("columns", $columns);

            if ($request->ajax()) {
                $data = CourceRegistration::query()->withTrashed();
                $data = CourceRegistration::with(['course'])->withTrashed();


                // Filter logic
                $data->when($request->filled('search_course_name'), function ($query) use ($request) {
                    $query->where('course_name', 'like', '%' . $request->search_course_name . '%');
                });

                $data->when($request->filled('search_fees'), function ($query) use ($request) {
                    $query->where('course_fees', 'like', '%' . $request->search_fees . '%');
                });

                $data->when($request->filled('search_status'), function ($query) use ($request) {
                    $query->where('status', $request->search_status);
                });

                $editPermisstion = true;
                $deletePermisstion = true;

                return Datatables::of($data)
                    ->addIndexColumn()
                    ->editColumn('date', function ($row) {
                        return $row->date ? \Carbon\Carbon::parse($row->date)->format('d M Y') : '-';
                    })
                    ->editColumn('status', function ($row) use ($modules) {
                        $dropdown = '<ul class="dropdown-menu">';
                        $button = '';

                        if ($row->status === 'active') {
                            $button = '<button type="button" class="btn btn-success btn-sm w-100 dropdown-toggle" data-bs-toggle="dropdown">' . ucfirst($row->status) . '</button>';
                            $dropdown .= '<li><a href="javascript:void(0)" class="dropdown-item update-status" data-url="' . route($modules['route'] . '.status-update') . '" data-id="' . $row->id . '" data-update_status="inactive">Inactive</a></li>';
                        } elseif ($row->status === 'inactive') {
                            $button = '<button type="button" class="btn btn-danger btn-sm w-100 dropdown-toggle" data-bs-toggle="dropdown">' . ucfirst($row->status) . '</button>';
                            $dropdown .= '<li><a href="javascript:void(0)" class="dropdown-item update-status" data-url="' . route($modules['route'] . '.status-update') . '" data-id="' . $row->id . '" data-update_status="active">Active</a></li>';
                        }

                        $dropdown .= '</ul>';
                        return $button . $dropdown;
                    })
                    ->addColumn('action', function ($row) use ($modules, $editPermisstion, $deletePermisstion) {
                        $btn = '';

                        if (!$row->deleted_at) {
                            if ($editPermisstion) {
                                $btn .= '<a href="' . route($modules['route'] . '.edit', $row->id) . '" class="btn btn-light btn-icon mx-1"><i class="fa-solid fa-pen-to-square"></i></a>';
                            }
                            if ($deletePermisstion) {
                                $btn .= '<a href="javascript:void(0)" data-id="' . $row->id . '" data-did="' . route($modules['route'] . '.destroy', $row->id) . '" class="btn btn-danger btn-icon deletebutton mx-1"><i class="fa-solid fa-trash"></i></a>';
                            }
                        } else {
                            $btn .= '<a href="javascript:void(0)" data-restore="' . route($modules['route'] . '.restore', $row->id) . '" class="btn btn-light mx-1 record-restore"><i class="ti ti-history"></i> Restore</a>';
                        }

                        return $btn ?: '-';
                    })
                    ->rawColumns(['status', 'action'])
                    ->make(true);
            }

            return view('admin.module.' . $modules['folder_path'] . '.index');
        } catch (\Exception $e) {
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
        $courses = MasterCourse::all();
        
        return view('admin.module.' . $modules['folder_path'] . '.form', compact('modules', 'courses'));
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
