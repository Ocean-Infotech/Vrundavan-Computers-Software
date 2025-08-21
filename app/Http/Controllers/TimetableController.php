<?php

namespace App\Http\Controllers;

use App\Http\Requests\TimetableRequest;
use App\Models\Timetable;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use App\Http\Controllers\Controller;
use App\Models\NotificationSetting;
use App\Models\Master\MasterCourse;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class TimetableController extends Controller
{

    public $modules = [];
    public function __construct()
    {
        $this->modules = [
            'title' => 'Timetable',
            'folder_path' => 'software.module.timetable',
            'route' => 'timetable',
            'table_name' => (new Timetable())->getTable(),
            'permission_prefix' => 'timetable',
        ];
    }
   public function index(Request $request)
{
    $modules = $this->modules;
    $modules['login_user_role'] = Helper::getLoginUserRole();
    $modules['permission_list'] = Helper::directCan($modules['permission_prefix'] . '-list');
    $modules['permission_add'] = Helper::directCan($modules['permission_prefix'] . '-create');
    $modules['permission_edit'] = Helper::directCan($modules['permission_prefix'] . '-edit');
    $modules['permission_delete'] = Helper::directCan($modules['permission_prefix'] . '-delete');

    // Permission check
    if (!$modules['permission_list']) {
        if ($request->ajax()) {
            return response()->json(['error' => 'User does not have the right permissions.'], 403);
        }
        abort(403, 'User does not have the right permissions.');
    }

    View::share('modules', $modules);

    // Define columns for DataTable
    $columns = [
        (object)['data' => "course_name", 'name' => 'course.course_name', 'td_label' => 'Course'],
        (object)['data' => "batch_name", 'name' => 'batch.batch_name', 'td_label' => 'Batch'],
        (object)['data' => "class_id", 'name' => 'class_id', 'td_label' => 'Class'],
        (object)['data' => "semester", 'name' => 'semester', 'td_label' => 'Semester'],
        (object)['data' => "attachment", 'name' => 'attachment', 'td_label' => 'Attachment'],
        (object)[
            'data' => "action",
            'name' => 'action',
            'td_label' => 'Action',
            'orderable' => false,
            'searchable' => false,
            'className' => 'w-10 text-center'
        ],
    ];

    View::share("columns", $columns);

    try {
        if ($request->ajax()) {
            $data = Timetable::with(['course', 'batch']);

            return Datatables::of($data)
                ->addIndexColumn()
                ->filter(function ($query) use ($request) {
                    if ($request->has('search') && $request->search['value'] != '') {
                        $search = $request->search['value'];
                        $query->whereHas('course', function($q) use ($search) {
                            $q->where('course_name', 'like', "%$search%");
                        })
                        ->orWhereHas('batch', function($q) use ($search) {
                            $q->where('batch_name', 'like', "%$search%");
                        })
                        ->orWhere('class_id', 'like', "%$search%")
                        ->orWhere('semester', 'like', "%$search%");
                    }
                })
                ->addColumn('course_name', function ($row) {
                    return $row->course ? $row->course->course_name : '-';
                })
                ->addColumn('batch_name', function ($row) {
                    return $row->batch ? $row->batch->batch_name : '-';
                })
                ->editColumn('attachment', function ($row) {
                    if (!$row->attachment) return '-';
                    $fileUrl = asset($row->attachment);
                    return '<a href="' . $fileUrl . '" target="_blank" class="text-primary">View Attachment</a>';
                })
                ->addColumn('action', function ($row) use ($modules) {
                    $btn = '';
                    if ($modules['permission_edit']) {
                        $btn .= '<a href="' . route($modules["route"] . ".edit", [$row->id]) . '" class="btn btn-light btn-icon mx-1"><i class="fa-solid fa-pen-to-square"></i></a>';
                    }
                    if ($modules['permission_delete']) {
                        $btn .= '<a href="javascript:void(0)" data-id="' . $row->id . '" data-did="' . route($modules["route"] . ".destroy", [$row->id]) . '" class="btn btn-danger btn-icon deletebutton mx-1"><i class="fa-solid fa-trash"></i></a>';
                    }
                    return $btn ?: '-';
                })
                ->rawColumns(['action', 'attachment'])
                ->make(true);
        }

        return view($modules['folder_path'] . '.index');
    } catch (\Exception $e) {
        return Redirect::route('dashboard')->withErrors($e->getMessage());
    }
}

    public function create()
    {
        $modules = $this->modules;
        View::share('modules', $modules);

        return view($modules['folder_path'] . '.form');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TimetableRequest $request)
    {
        $validated = $request->validated();

        try {
            // Handle file upload
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $filename = time() . '_' . $file->getClientOriginalName();
                $destinationPath = public_path('upload/timetable');

                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }

                $file->move($destinationPath, $filename);
                $validated['attachment'] = 'upload/timetable/' . $filename;
            }

            $validated['created_by'] = Auth::id();

            Timetable::create($validated);

            return Redirect::route($this->modules['route'] . '.index')
                ->withSuccess($this->modules['title'] . ' created successfully');
        } catch (\Exception $e) {
            return Redirect::route('dashboard')->withErrors($e->getMessage());
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }


    public function edit(string $id)
    {
        $modules = $this->modules;
        View::share('modules', $modules);

        try {
            // dd($id);
            $edit = Timetable::findOrFail($id);
            // dd($edit);
            View::share('edit', $edit);

            return view($modules['folder_path'] . '.form');
        } catch (\Exception $e) {
            return Redirect::route('dashboard')->withErrors($e->getMessage());
        }
    }



    /**
     * Update the specified resource in storage.
     */
    public function update(TimetableRequest $request, $id)
    {
        $validated = $request->validated();

        try {
            $timetable = Timetable::findOrFail($id);

            // Handle file upload if new file is uploaded
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $filename = time() . '_' . $file->getClientOriginalName();
                $destinationPath = public_path('upload/timetable');

                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }

                // Delete old file if exists
                if (!empty($timetable->attachment) && file_exists(public_path($timetable->attachment))) {
                    unlink(public_path($timetable->attachment));
                }

                // Move new file
                $file->move($destinationPath, $filename);
                $validated['attachment'] = 'upload/timetable/' . $filename;
            }

            $validated['updated_by'] = Auth::id();

            $timetable->update($validated);

            return Redirect::route($this->modules['route'] . '.index')
                ->withSuccess($this->modules['title'] . ' updated successfully');
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
}
