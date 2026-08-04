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
            (object)['data' => "course_name", 'name' => 'course_id', 'td_label' => 'Course'],
            (object)['data' => "batch_name", 'name' => 'batch_id', 'td_label' => 'Batch'],
            (object)['data' => "class", 'name' => 'class', 'td_label' => 'Class'],
            (object)['data' => "student_count", 'name' => 'student_count', 'td_label' => 'Students', 'orderable' => false, 'searchable' => false],
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
                $data = Timetable::with(['course', 'batch', 'class']);

                return Datatables::of($data)
                    ->filter(function ($query) use ($request) {
                        if ($request->filled('course_id')) {
                            $query->where('course_id', $request->course_id);
                        }
                        if ($request->filled('batch_id')) {
                            $query->where('batch_id', $request->batch_id);
                        }
                        if ($request->filled('class_id')) {
                            $query->where('class_id', $request->class_id);
                        }
                        if ($request->has('search') && $request->search['value'] != '') {
                            $search = $request->search['value'];
                            $query->where(function ($q) use ($search) {
                                $q->whereHas('course', function ($q2) use ($search) {
                                    $q2->where('course_name', 'like', "%$search%");
                                })
                                ->orWhereHas('batch', function ($q2) use ($search) {
                                    $q2->where('batch_name', 'like', "%$search%");
                                })
                                ->orWhereHas('class', function ($q2) use ($search) {
                                    $q2->where('class', 'like', "%$search%");
                                });
                            });
                        }
                    })
                    ->addColumn('course_name', function ($row) {
                        return $row->course ? $row->course->course_name : '-';
                    })
                    ->addColumn('class', function ($row) {
                        return $row->class_id ? $row->class->class : '-';
                    })
                    ->addColumn('batch_name', function ($row) {
                        return $row->batch ? $row->batch->batch_name : '-';
                    })
                    ->addColumn('student_count', function ($row) {
                        $count = \App\Models\CourceRegistration::where('course_id', $row->course_id)
                            ->where('batch_id', $row->batch_id)
                            ->where('class_id', $row->class_id)
                            ->where('status', 'active')
                            ->count();
                        return '<button type="button" class="btn btn-sm btn-outline-primary view-students-btn" data-id="' . $row->id . '">
                                    <i class="fa-solid fa-users me-1"></i> ' . $count . ' Students
                                </button>';
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
                          $btn .= '<a href="javascript:void(0)"
                    data-did="' . route($modules["route"] . ".permanent-delete", [$row->id]) . '"
                    class="btn btn-danger btn-icon permanentDeleteButton mx-1">
                    <i class="fa-solid fa-ban"></i>
                 </a>';
                        return $btn ?: '-';
                    })
                    ->rawColumns(['action', 'attachment', 'student_count'])
                    ->make(true);
            }

            return view($modules['folder_path'] . '.index');
        } catch (\Exception $e) {
            return Redirect::route('dashboard')->withErrors($e->getMessage());
        }
    }

    public function getStudents($id)
    {
        try {
            $timetable = Timetable::findOrFail($id);

            $registrations = \App\Models\CourceRegistration::with('admission')
                ->where('course_id', $timetable->course_id)
                ->where('batch_id', $timetable->batch_id)
                ->where('class_id', $timetable->class_id)
                ->where('status', 'active')
                ->get();

            $students = $registrations->map(function ($reg) {
                $adm = $reg->admission;
                $name = $adm ? trim($adm->first_name . ' ' . $adm->father_name . ' ' . $adm->last_name) : '-';
                return [
                    'gr_no'  => $adm->gr_no ?? '-',
                    'name'   => $name,
                    'mobile' => $adm->student_mobile_no ?? ($adm->parent_mobile_no ?? '-'),
                ];
            });

            return response()->json([
                'status'   => true,
                'students' => $students,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
            ], 500);
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

            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $filename = time() . '_' . $file->getClientOriginalName();
                $destinationPath = public_path('uploads/timetable');

                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }

                $file->move($destinationPath, $filename);
                $validated['attachment'] = 'uploads/timetable/' . $filename;
            }

            $validated['created_by'] = Auth::id();

            Timetable::create($validated);

            return Redirect::route($this->modules['route'] . '.index')
                ->withSuccess($this->modules['title'] . ' created successfully');
        } catch (\Exception $e) {
            return $e->getMessage();
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
                $destinationPath = public_path('uploads/timetable');

                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }

                // Delete old file if exists
                if (!empty($timetable->attachment) && file_exists(public_path($timetable->attachment))) {
                    unlink(public_path($timetable->attachment));
                }

                // Move new file
                $file->move($destinationPath, $filename);
                $validated['attachment'] = 'uploads/timetable/' . $filename;
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
     */ public function destroy(Request $request, string $id)
    {
        $isAjax = $request->ajax();

        try {
            $timetable = Timetable::findOrFail($id);

            // Soft delete: if you want soft delete, comment out the next line and use $timetable->delete()
            // Permanently delete attachment if exists
            if (!empty($timetable->attachment) && file_exists(public_path($timetable->attachment))) {
                unlink(public_path($timetable->attachment));
            }

            // Optional: track who deleted
            $timetable->deleted_by = Auth::user()?->id;
            $timetable->save();

            // Soft delete or permanent delete
            $timetable->delete();

            if ($isAjax) {
                return response()->json([
                    'success' => true,
                    'message' => 'Timetable deleted successfully.'
                ]);
            }

            return redirect()->back()->withSuccess('Timetable deleted successfully.');
        } catch (\Exception $e) {
            if ($isAjax) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }

            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function restore($id)
    {
        try {
            $timetable = Timetable::withTrashed()->findOrFail($id);
            $timetable->restore();

            return response()->json([
                'success' => true,
                'message' => 'Timetable restored successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function permanentDelete($id)
{
    try {
        $timetable = Timetable::withTrashed()->findOrFail($id);

        // Delete attachment file permanently
        if (!empty($timetable->attachment) && file_exists(public_path($timetable->attachment))) {
            unlink(public_path($timetable->attachment));
        }

        // Permanently delete from DB
        $timetable->forceDelete();

        return response()->json([
            'success' => true,
            'message' => 'Timetable permanently deleted successfully.'
        ]);

    } catch (\Exception $e) {

        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}

}
