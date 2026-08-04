<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User;
use App\Helpers\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\Facades\DataTables;

class TaskController extends Controller
{
    public $modules = [];

    public function __construct()
    {
        $this->modules = [
            'title' => 'Task Management',
            'folder_path' => 'software.module.task',
            'route' => 'task',
            'table_name' => (new Task())->getTable(),
            'permission_prefix' => 'task',
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
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            abort(403, 'User does not have the right permissions.');
        }

        View::share('modules', $modules);

        try {
            $isFaculty = Helper::getLoginUserRole() === 'Facility';
            if ($isFaculty) {
                $columns = [
                    (object)['data' => 'DT_RowIndex', 'name' => 'id', 'td_label' => 'No', 'orderable' => false, 'searchable' => false],
                    (object)['data' => 'title', 'name' => 'title', 'td_label' => 'Task', 'width' => '410px'],
                    (object)['data' => 'status', 'name' => 'status', 'td_label' => 'Status', 'className' => 'text-center'],
                    (object)['data' => 'response', 'name' => 'response', 'td_label' => 'Response', 'className' => 'text-center'],
                    (object)['data' => 'remarks', 'name' => 'remarks', 'td_label' => 'Remarks', 'className' => 'text-center'],
                    (object)['data' => 'action', 'name' => 'action', 'td_label' => 'Action', 'orderable' => false, 'searchable' => false, 'className' => 'text-center'],
                ];
            } else {
                $columns = [
                    (object)['data' => 'DT_RowIndex', 'name' => 'id', 'td_label' => 'No', 'orderable' => false, 'searchable' => false],
                    (object)['data' => 'title', 'name' => 'title', 'td_label' => 'Task', 'width' => '410px'],
                    (object)['data' => 'assigned_to', 'name' => 'assigned_to', 'td_label' => 'Staff'],
                    (object)['data' => 'status', 'name' => 'status', 'td_label' => 'Status', 'className' => 'text-center'],
                    (object)['data' => 'details', 'name' => 'details', 'td_label' => 'Reply', 'orderable' => false, 'searchable' => false, 'className' => 'text-center'],
                    (object)['data' => 'action', 'name' => 'action', 'td_label' => 'Action', 'orderable' => false, 'searchable' => false, 'className' => 'text-center'],
                ];
                if (!$modules['permission_edit'] && !$modules['permission_delete']) {
                    $columns = array_filter($columns, function ($col) {
                        return $col->name !== 'action';
                    });
                    $columns = array_values($columns);
                }
            }
            View::share('columns', $columns);

            if ($request->ajax()) {
                $data = Task::with(['assignedTo'])
                    ->select('tasks.*')
                    ->orderBy('id', 'desc');

                return Datatables::of($data)
                    ->addIndexColumn()
                    ->addColumn('assigned_to', function ($row) {
                        return $row->assignedTo ? $row->assignedTo->name : '-';
                    })
                    ->editColumn('date', function ($row) {
                        return $row->date ? \Carbon\Carbon::parse($row->date)->format('d-m-Y h:i A') : '';
                    })
                    ->editColumn('due_date', function ($row) {
                        return $row->due_date ? \Carbon\Carbon::parse($row->due_date)->format('d-m-Y h:i A') : '-';
                    })
                    ->editColumn('status', function ($row) use ($isFaculty) {
                        $statusText = 'Pending';
                        $class = 'danger';
                        if ($row->status == 1) {
                            $statusText = 'In Progress';
                            $class = 'warning';
                        } elseif ($row->status == 2) {
                            $statusText = 'Completed';
                            $class = 'success';
                        } elseif ($row->status == 3) {
                            $statusText = 'Done';
                            $class = 'info';
                        } elseif ($row->status == 4) {
                            $statusText = 'Repass';
                            $class = 'secondary';
                        } elseif ($row->status == 5) {
                            $statusText = 'Cancelled';
                            $class = 'dark';
                        }

                        if ($isFaculty) {
                            return '<span class="badge bg-' . $class . '">' . $statusText . '</span>';
                        }

                        return '<div class="btn-group">
                            <button type="button" class="btn btn-sm btn-' . $class . ' dropdown-toggle" data-bs-toggle="dropdown" data-bs-boundary="viewport" aria-expanded="false">
                                ' . $statusText . '
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item change-status" href="javascript:void(0);" data-id="' . $row->id . '" data-status="0">Pending</a></li>
                                <li><a class="dropdown-item change-status" href="javascript:void(0);" data-id="' . $row->id . '" data-status="1">In Progress</a></li>
                                <li><a class="dropdown-item change-status" href="javascript:void(0);" data-id="' . $row->id . '" data-status="2">Completed</a></li>
                                <li><a class="dropdown-item change-status" href="javascript:void(0);" data-id="' . $row->id . '" data-status="3">Done</a></li>
                                <li><a class="dropdown-item change-status" href="javascript:void(0);" data-id="' . $row->id . '" data-status="4">Repass</a></li>
                                <li><a class="dropdown-item change-status" href="javascript:void(0);" data-id="' . $row->id . '" data-status="5">Cancelled</a></li>
                            </ul>
                        </div>';
                    })
                    ->addColumn('details', function ($row) {
                        $staffName = $row->assignedTo ? e($row->assignedTo->name) : '-';
                        $workText = e($row->title);
                        $formattedDate = $row->date ? \Carbon\Carbon::parse($row->date)->format('d-m-Y h:i A') : '-';
                        $formattedDeadline = $row->due_date ? \Carbon\Carbon::parse($row->due_date)->format('d-m-Y h:i A') : '-';
                        return '<button type="button" class="btn btn-sm btn-info view-details" 
                            data-id="' . $row->id . '" 
                            data-date="' . $formattedDate . '" 
                            data-staff="' . $staffName . '" 
                            data-work="' . $workText . '" 
                            data-deadline="' . $formattedDeadline . '" 
                            data-remarks="' . e($row->remarks) . '" 
                            data-response="' . e($row->response) . '" 
                            data-status="' . $row->status . '">Reply</button>';
                    })
                    ->addColumn('response', function ($row) {
                        return e($row->response) ?: '-';
                    })
                    ->addColumn('action', function ($row) use ($modules, $isFaculty) {
                        $staffName = $row->assignedTo ? e($row->assignedTo->name) : '-';
                        $workText = e($row->title);
                        $formattedDate = $row->date ? \Carbon\Carbon::parse($row->date)->format('d-m-Y h:i A') : '-';
                        $formattedDeadline = $row->due_date ? \Carbon\Carbon::parse($row->due_date)->format('d-m-Y h:i A') : '-';

                        if ($isFaculty) {
                            return '<button type="button" class="btn btn-sm btn-info view-details mx-1" 
                                        data-id="' . $row->id . '" 
                                        data-date="' . $formattedDate . '" 
                                        data-staff="' . $staffName . '" 
                                        data-work="' . $workText . '" 
                                        data-deadline="' . $formattedDeadline . '" 
                                        data-remarks="' . e($row->remarks) . '" 
                                        data-response="' . e($row->response) . '" 
                                        data-status="' . $row->status . '">Done</button>' .
                                   '<button type="button" class="btn btn-sm btn-warning change-status mx-1" data-id="' . $row->id . '" data-status="5">Cancel</button>';
                        }
                        $btn = '';
                        if ($modules['permission_edit']) {
                            $btn .= '<a href="' . route($modules['route'] . '.edit', [$row->id]) . '" class="btn btn-light btn-icon mx-1" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>';
                        }
                        if ($modules['permission_delete']) {
                            $btn .= '<button type="button" class="btn btn-danger btn-icon delete-record mx-1" data-id="' . $row->id . '" title="Delete"><i class="fa-solid fa-trash"></i></button>';
                        }
                        return $btn;
                    })
                    ->rawColumns(['status', 'details', 'action'])
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
        $modules['login_user_role'] = Helper::getLoginUserRole();
        $modules['permission_add'] = Helper::directCan($modules['permission_prefix'] . '-create');

        if (!$modules['permission_add']) {
            abort(403, 'User does not have the right permissions.');
        }

        View::share('modules', $modules);

        $faculties = User::whereHas('roles', function ($query) {
            $query->where('name', 'Facility');
        })->get();

        return view($modules['folder_path'] . '.form', compact('faculties'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date_format:Y-m-d\TH:i',
            'title' => 'required|string|max:255',
            'due_date' => 'nullable|date_format:Y-m-d\TH:i',
            'assigned_to' => 'nullable|exists:users,id',
            'status' => 'required|in:0,1,2,3,4,5',
            'remarks' => 'nullable|string',
        ]);

        try {
            $data = $request->all();
            $data['created_by'] = Auth::id();
            Task::create($data);

            return redirect()->route('task.index')->with('success', 'Task created successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->withErrors($e->getMessage());
        }
    }

    public function edit($id)
    {
        $modules = $this->modules;
        $modules['login_user_role'] = Helper::getLoginUserRole();
        $modules['permission_edit'] = Helper::directCan($modules['permission_prefix'] . '-edit');

        if (!$modules['permission_edit']) {
            abort(403, 'User does not have the right permissions.');
        }

        View::share('modules', $modules);

        $task = Task::findOrFail($id);
        $faculties = User::whereHas('roles', function ($query) {
            $query->where('name', 'Facility');
        })->get();

        return view($modules['folder_path'] . '.form', compact('task', 'faculties'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'date' => 'required|date_format:Y-m-d\TH:i',
            'title' => 'required|string|max:255',
            'due_date' => 'nullable|date_format:Y-m-d\TH:i',
            'assigned_to' => 'nullable|exists:users,id',
            'status' => 'required|in:0,1,2,3,4,5',
            'remarks' => 'nullable|string',
        ]);

        try {
            $task = Task::findOrFail($id);
            $data = $request->all();
            $data['updated_by'] = Auth::id();
            $task->update($data);

            return redirect()->route('task.index')->with('success', 'Task updated successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->withErrors($e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $task = Task::findOrFail($id);
            $task->deleted_by = Auth::id();
            $task->save();
            $task->delete();

            return response()->json(['success' => true, 'message' => 'Task deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function changeStatus(Request $request)
    {
        try {
            $task = Task::findOrFail($request->id);
            $task->status = $request->status;
            $task->updated_by = Auth::id();
            $task->save();

            return response()->json(['success' => true, 'message' => 'Status updated successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function saveReply(Request $request)
    {
        try {
            $task = Task::findOrFail($request->id);
            if ($request->has('status')) {
                $task->status = $request->status;
            }
            if ($request->has('remarks')) {
                $task->remarks = $request->remarks;
            }
            if ($request->has('response')) {
                $task->response = $request->response;
            }
            if (Helper::getLoginUserRole() === 'Facility') {
                $task->status = 3; // Done
            }
            $task->updated_by = Auth::id();
            $task->save();

            return response()->json(['success' => true, 'message' => 'Task reply saved successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
