<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Http\Requests\AssignmentRequest;
use App\Helpers\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Redirect;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\File;

class AssignmentController extends Controller
{
    public $modules = [];

    public function __construct()
    {
        $this->modules = [
            'title' => 'Assignment',
            'folder_path' => 'software.module.assignment',
            'route' => 'assignment',
            'table_name' => (new Assignment())->getTable(),
            'permission_prefix' => 'assignment',
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

        if (!$modules['permission_list']) {
            if ($request->ajax()) {
                return response()->json(['error' => 'User does not have the right permissions.'], 403);
            }
            abort(403, 'User does not have the right permissions.');
        }

        View::share('modules', $modules);

        try {
            $columns = [
                (object)['data' => 'DT_RowIndex', 'name' => 'id', 'td_label' => 'No', 'orderable' => false, 'searchable' => false],
                (object)['data' => 'course', 'name' => 'course_id', 'td_label' => 'Course'],
                (object)['data' => 'batch', 'name' => 'batch_id', 'td_label' => 'Batch'],
                (object)['data' => 'semester', 'name' => 'semester', 'td_label' => 'Semester'],
                (object)['data' => 'subject', 'name' => 'subject', 'td_label' => 'Subject'],
                (object)['data' => 'unit', 'name' => 'unit', 'td_label' => 'Unit'],
                (object)['data' => 'date', 'name' => 'date', 'td_label' => 'Date', 'className' => 'text-center'],
                (object)['data' => 'status', 'name' => 'status', 'td_label' => 'Status', 'className' => 'text-center'],
                (object)['data' => 'action', 'name' => 'action', 'td_label' => 'Action', 'orderable' => false, 'searchable' => false, 'className' => 'text-center'],
            ];
            View::share('columns', $columns);

            if ($request->ajax()) {
                $data = Assignment::with(['course', 'batch'])
                    ->select('assignments.*')
                    ->orderBy('id', 'desc');

                return Datatables::of($data)
                    ->addIndexColumn()
                    ->addColumn('course', function ($row) {
                        return $row->course ? $row->course->course_name : '-';
                    })
                    ->addColumn('batch', function ($row) {
                        return $row->batch ? $row->batch->batch_name : '-';
                    })
                    ->editColumn('unit', function ($row) {
                        return $row->unit ?: '-';
                    })
                    ->editColumn('date', function ($row) {
                        return $row->date ? \Carbon\Carbon::parse($row->date)->format('d-m-Y') : '';
                    })
                    ->editColumn('status', function ($row) use ($modules) {
                        $active = $row->status ? 'Active' : 'Inactive';
                        $class = $row->status ? 'success' : 'danger';

                        if ($modules['permission_edit']) {
                            return '<div class="btn-group">
                                <button type="button" class="btn btn-sm btn-' . $class . ' dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                    ' . $active . '
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item change-status" href="javascript:void(0);" data-id="' . $row->id . '" data-status="1">Active</a></li>
                                    <li><a class="dropdown-item change-status" href="javascript:void(0);" data-id="' . $row->id . '" data-status="0">Inactive</a></li>
                                </ul>
                            </div>';
                        } else {
                            return '<span class="badge bg-' . $class . '">' . $active . '</span>';
                        }
                    })
                    ->addColumn('action', function ($row) use ($modules) {
                        $btn = '';
                        if ($modules['permission_add']) {
                            $btn .= '<a href="' . route('assignment.report', [$row->id]) . '" class="btn btn-warning btn-icon mx-1" data-bs-toggle="tooltip" title="Assignment Report"><i class="fa-solid fa-file-circle-check"></i></a>';
                        }
                        if ($modules['permission_edit']) {
                            $btn .= '<a href="' . route($modules['route'] . '.edit', [$row->id]) . '" class="btn btn-light btn-icon mx-1" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>';
                        }
                        if ($modules['permission_delete']) {
                            $btn .= '<button type="button" class="btn btn-danger btn-icon deletebutton mx-1" data-did="' . route($modules['route'] . '.destroy', [$row->id]) . '" title="Delete"><i class="fa-solid fa-trash"></i></button>';
                        }
                        return $btn;
                    })
                    ->rawColumns(['status', 'action'])
                    ->make(true);
            }

            return view($modules['folder_path'] . '.index');
        } catch (\Exception $e) {
            return back()->withErrors($e->getMessage());
        }
    }

    public function create()
    {
        $modules = $this->modules;
        $modules['permission_add'] = Helper::directCan($modules['permission_prefix'] . '-create');
        if (!$modules['permission_add']) {
            abort(403, 'User does not have the right permissions.');
        }
        View::share('modules', $modules);
        return view($modules['folder_path'] . '.form');
    }

    public function store(AssignmentRequest $request)
    {
        $modules = $this->modules;
        $canCreate = Helper::directCan($modules['permission_prefix'] . '-create');
        if (!$canCreate) {
            abort(403, 'User does not have the right permissions.');
        }
        $validated = $request->validated();

        try {
            $validated['created_by'] = Auth::id();
            Assignment::create($validated);

            return Redirect::route($modules['route'] . '.index')->withSuccess($modules['title'] . ' Created Successfully');
        } catch (\Exception $e) {
            return back()->withInput()->withErrors($e->getMessage());
        }
    }

    public function edit($id)
    {
        $modules = $this->modules;
        $modules['permission_edit'] = Helper::directCan($modules['permission_prefix'] . '-edit');
        if (!$modules['permission_edit']) {
            abort(403, 'User does not have the right permissions.');
        }
        View::share('modules', $modules);

        $edit = Assignment::findOrFail($id);
        return view($modules['folder_path'] . '.form', compact('edit'));
    }

    public function update(AssignmentRequest $request, $id)
    {
        $modules = $this->modules;
        $canEdit = Helper::directCan($modules['permission_prefix'] . '-edit');
        if (!$canEdit) {
            abort(403, 'User does not have the right permissions.');
        }
        $validated = $request->validated();
        $assignment = Assignment::findOrFail($id);

        try {
            $validated['updated_by'] = Auth::id();
            $assignment->update($validated);

            return Redirect::route($modules['route'] . '.index')->withSuccess($modules['title'] . ' Updated Successfully');
        } catch (\Exception $e) {
            return back()->withInput()->withErrors($e->getMessage());
        }
    }

    public function destroy($id)
    {
        $canDelete = Helper::directCan($this->modules['permission_prefix'] . '-delete');
        if (!$canDelete) {
            return response()->json(['success' => false, 'message' => 'User does not have the right permissions.'], 403);
        }
        try {
            $assignment = Assignment::findOrFail($id);
            $assignment->delete();
            return response()->json(['success' => true, 'message' => 'Assignment deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function changeStatus(Request $request)
    {
        $canEdit = Helper::directCan($this->modules['permission_prefix'] . '-edit');
        if (!$canEdit) {
            return response()->json(['success' => false, 'message' => 'User does not have the right permissions.'], 403);
        }
        try {
            $assignment = Assignment::findOrFail($request->id);
            $assignment->status = $request->status;
            $assignment->save();
            return response()->json(['success' => true, 'message' => 'Status changed successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function report($id)
    {
        $modules = $this->modules;
        $canCreate = Helper::directCan($modules['permission_prefix'] . '-create');
        if (!$canCreate) {
            abort(403, 'User does not have the right permissions.');
        }
        View::share('modules', $modules);

        try {
            $assignment = Assignment::with(['course', 'batch'])->findOrFail($id);

            $students = \App\Models\CourceRegistration::with(['admission'])
                ->where('course_id', $assignment->course_id)
                ->where('batch_id', $assignment->batch_id)
                ->whereIn('status', ['active', 'Running'])
                ->get();

            foreach ($students as $student) {
                $grNo = $student->admission->gr_no ?? 0;
                $student->report = \App\Models\StudentAssignmentReport::with(['createdByUser', 'updatedByUser'])
                    ->where('assignment_id', $id)
                    ->where('gr_no', $grNo)
                    ->first();
            }

            return view($modules['folder_path'] . '.report', compact('assignment', 'students', 'modules'));
        } catch (\Exception $e) {
            return back()->withErrors($e->getMessage());
        }
    }

    public function saveReportAjax(Request $request)
    {
        $canCreate = Helper::directCan($this->modules['permission_prefix'] . '-create');
        if (!$canCreate) {
            return response()->json(['success' => false, 'message' => 'User does not have the right permissions.'], 403);
        }
        try {
            $validated = $request->validate([
                'assignment_id' => 'required|integer',
                'student_id' => 'required|integer',
                'status' => 'nullable|string|in:complete,pending,incomplete',
                'remarks' => 'nullable|string',
            ]);

            $userId = Auth::id();

            // Find existing or create new
            $reportRecord = \App\Models\StudentAssignmentReport::where([
                'assignment_id' => $validated['assignment_id'],
                'gr_no' => $validated['student_id'],
            ])->first();

            if ($reportRecord) {
                $reportRecord->update([
                    'status' => (!empty($validated['status'])) ? $validated['status'] : 'pending',
                    'remarks' => $validated['remarks'] ?? '',
                    'updated_by' => $userId,
                ]);
                $reportRecord->load(['updatedByUser', 'createdByUser']);
            } else {
                $reportRecord = \App\Models\StudentAssignmentReport::create([
                    'assignment_id' => $validated['assignment_id'],
                    'gr_no' => $validated['student_id'],
                    'status' => (!empty($validated['status'])) ? $validated['status'] : 'pending',
                    'remarks' => $validated['remarks'] ?? '',
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]);
                $reportRecord->load(['updatedByUser', 'createdByUser']);
            }

            return response()->json([
                'success' => true,
                'faculty_name' => $reportRecord->updatedByUser?->name ?? $reportRecord->createdByUser?->name ?? 'N/A',
                'time' => $reportRecord->updated_at ? $reportRecord->updated_at->format('d-m-Y h:i A') : '',
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
