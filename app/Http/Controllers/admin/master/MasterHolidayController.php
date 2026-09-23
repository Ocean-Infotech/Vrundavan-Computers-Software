<?php

namespace App\Http\Controllers\admin\master;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\MasterHolidayRequest;
use App\Models\Master\MasterHoliday;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class MasterHolidayController extends Controller
{
    public $modules = [];

    public function __construct()
    {
        $this->modules = [
            'title' => 'Holiday',
            'folder_path' => 'software.module.master.holiday',
            'route' => 'holiday',
            'table_name' => (new MasterHoliday())->getTable(),
            'permission_prefix' => 'holiday',
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
                return $this->sendError('User does not have the right permissions.', [], [], 403);
            }
            abort(403, 'User does not have the right permissions.');
        }

        View::share('modules', $modules);

        try {
            $columns = [
                (object)['data' => "name", 'name' => 'name', 'td_label' => 'Holiday Name'],
                (object)['data' => "from_date", 'name' => 'from_date', 'td_label' => 'From Date'],
                (object)['data' => "to_date", 'name' => 'to_date', 'td_label' => 'To Date'],
                (object)['data' => "total_days", 'name' => 'total_days', 'td_label' => 'Duration', 'orderable' => false, 'searchable' => false],
                (object)['data' => "type", 'name' => 'type', 'td_label' => 'Type'],
                (object)['data' => "status", 'name' => 'status', 'td_label' => 'Status', 'className' => 'w-5 text-center', 'width' => '10%'],
                (object)['data' => "action", 'name' => 'action', 'td_label' => 'Action', 'orderable' => false, 'searchable' => false, 'className' => 'w-10 text-center'],
            ];
            View::share("columns", $columns);

            if ($request->ajax()) {
                $data = MasterHoliday::withTrashed()->orderBy('from_date', 'desc')->orderBy('id', 'desc');

                return Datatables::of($data)
                    ->addIndexColumn()
                    ->filter(function ($query) use ($request) {
                        if ($request->has('search') && !empty($request->search)) {
                            $search = $request->search;
                            $query->where(function ($q) use ($search) {
                                $q->where('name', 'like', "%{$search}%")
                                    ->orWhere('type', 'like', "%{$search}%")
                                    ->orWhere('description', 'like', "%{$search}%")
                                    ->orWhere('from_date', 'like', "%{$search}%")
                                    ->orWhere('to_date', 'like', "%{$search}%");
                            });
                        }
                    })
                    ->editColumn('from_date', function ($row) {
                        return $row->from_date ? Carbon::parse($row->from_date)->format('d-m-Y (D)') : '-';
                    })
                    ->editColumn('to_date', function ($row) {
                        return $row->to_date ? Carbon::parse($row->to_date)->format('d-m-Y (D)') : '-';
                    })
                    ->addColumn('total_days', function ($row) {
                        if (!$row->from_date || !$row->to_date) {
                            return '-';
                        }
                        $from = Carbon::parse($row->from_date);
                        $to = Carbon::parse($row->to_date);
                        $days = $from->diffInDays($to) + 1;
                        return '<span class="badge bg-label-info">' . $days . ($days > 1 ? ' Days' : ' Day') . '</span>';
                    })
                    ->editColumn('type', function ($row) {
                        return $row->type ? '<span class="badge bg-label-primary">' . htmlspecialchars($row->type) . '</span>' : '-';
                    })
                    ->editColumn('status', function ($row) use ($modules) {
                        if ($row->status === "active") {
                            return '<button class="btn btn-success btn-sm dropdown-toggle" data-bs-toggle="dropdown">' . ucfirst($row->status) . '</button>
                                <ul class="dropdown-menu">
                                    <li><a href="javascript:void(0)" class="dropdown-item update-status" data-url="' . route($modules["route"] . '.status-update') . '" data-id="' . $row->id . '" data-update_status="inactive">Inactive</a></li>
                                </ul>';
                        } elseif ($row->status === "inactive") {
                            return '<button class="btn btn-danger btn-sm dropdown-toggle" data-bs-toggle="dropdown">' . ucfirst($row->status) . '</button>
                                <ul class="dropdown-menu">
                                    <li><a href="javascript:void(0)" class="dropdown-item update-status" data-url="' . route($modules["route"] . '.status-update') . '" data-id="' . $row->id . '" data-update_status="active">Active</a></li>
                                </ul>';
                        }
                        return '';
                    })
                    ->addColumn('action', function ($row) use ($modules) {
                        $btn = '';
                        if (!$row->deleted_at) {
                            if ($modules['permission_edit']) {
                                $btn .= '<a href="' . route($modules["route"] . ".edit", [$row->id]) . '" class="btn btn-light btn-icon mx-1" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>';
                            }
                            if ($modules['permission_delete']) {
                                $btn .= '<a href="javascript:void(0)" data-id="' . $row->id . '" data-did="' . route($modules["route"] . ".destroy", [$row->id]) . '" class="btn btn-danger btn-icon deletebutton mx-1" title="Delete"><i class="fa-solid fa-trash"></i></a>';
                            }
                            $btn .= '<a href="javascript:void(0)" data-did="' . route($modules["route"] . ".permanent-delete", [$row->id]) . '" class="btn btn-danger btn-icon permanentDeleteButton mx-1" title="Permanent Delete"><i class="fa-solid fa-ban"></i></a>';
                        } else {
                            $btn .= '<a href="javascript:void(0)" data-restore="' . route($modules["route"] . ".restore", ['id' => $row->id]) . '" class="btn btn-light mx-1 record-restore" title="Restore Data"><i class="ti ti-history"></i> Restore</a>';
                        }
                        return $btn ?: '-';
                    })
                    ->rawColumns(['total_days', 'type', 'status', 'action'])
                    ->make(true);
            }

            return view($modules['folder_path'] . '.index');
        } catch (\Exception $e) {
            return Redirect::route('software.dashboard')->withErrors($e->getMessage());
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
        return view($modules['folder_path'] . '.form', compact('modules'));
    }

    public function store(MasterHolidayRequest $request)
    {
        $modules = $this->modules;
        $modules['login_user_role'] = Helper::getLoginUserRole();
        $modules['permission_add'] = Helper::directCan($modules['permission_prefix'] . '-create');
        if (!$modules['permission_add']) {
            if ($request->ajax()) {
                return $this->sendError('User does not have the right permissions.', [], [], 403);
            }
            abort(403, 'User does not have the right permissions.');
        }
        View::share('modules', $modules);
        $validated = $request->validated();

        try {
            $validated['created_by'] = Auth::user()->id;
            MasterHoliday::create($validated);

            return Redirect::route($modules['route'] . '.index')->withSuccess($modules['title'] . ' created successfully');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
            }
            return Redirect::route('software.dashboard')->withErrors($e->getMessage());
        }
    }

    public function edit(string $id)
    {
        $modules = $this->modules;
        $modules['login_user_role'] = Helper::getLoginUserRole();
        $modules['permission_edit'] = Helper::directCan($modules['permission_prefix'] . '-edit');
        if (!$modules['permission_edit']) {
            abort(403, 'User does not have the right permissions.');
        }
        View::share('modules', $modules);

        try {
            $edit = MasterHoliday::findOrFail($id);
            return view($modules['folder_path'] . '.form', compact('modules', 'edit'));
        } catch (\Exception $e) {
            return Redirect::route('software.dashboard')->withErrors($e->getMessage());
        }
    }

    public function update(MasterHolidayRequest $request, string $id)
    {
        $modules = $this->modules;
        $modules['login_user_role'] = Helper::getLoginUserRole();
        $modules['permission_edit'] = Helper::directCan($modules['permission_prefix'] . '-edit');
        if (!$modules['permission_edit']) {
            if ($request->ajax()) {
                return $this->sendError('User does not have the right permissions.', [], [], 403);
            }
            abort(403, 'User does not have the right permissions.');
        }
        View::share('modules', $modules);

        $validated = $request->validated();

        try {
            $validated['updated_by'] = Auth::user()->id;
            $updateData = MasterHoliday::findOrFail($id);
            if ($updateData) {
                $updateData->update($validated);

                return Redirect::route($modules['route'] . '.index')->withSuccess($modules['title'] . ' updated successfully');
            }
            return Redirect::back()->withErrors('Something went wrong, please try again later')->withInput();
        } catch (\Exception $e) {
            return Redirect::route('software.dashboard')->withErrors($e->getMessage());
        }
    }

    public function update_status(Request $request)
    {
        $isAjax = ($request->ajax()) ? true : false;
        $modules = $this->modules;
        $modules['permission_edit'] = Helper::directCan($modules['permission_prefix'] . '-edit');
        if (!$modules['permission_edit']) {
            if ($isAjax) {
                return $this->sendError('User does not have the right permissions.', [], [], 403);
            }
            abort(403, 'User does not have the right permissions.');
        }

        $validator = Validator::make($request->all(), [
            'id' => ['required', Rule::exists($modules['table_name'], 'id')],
            'update_status' => ['required', 'in:active,inactive']
        ]);

        if ($validator->fails() && $isAjax) {
            return $this->sendError($validator->messages()->first(), $validator->messages(), [], 401);
        }

        try {
            $holiday = MasterHoliday::withTrashed()->findOrFail($request->id);

            if ($holiday) {
                $holiday->status = $request->update_status;
                $holiday->save();
                if ($isAjax) {
                    return $this->sendResponse($holiday, $modules['title'] . ' status updated successfully.');
                }
                return Redirect::route($modules['route'] . '.index')->withSuccess($modules['title'] . ' status updated successfully.');
            }
            if ($isAjax) {
                return $this->sendError('Something went wrong, please try again later.');
            }
            return Redirect::back()->withErrors('Something went wrong, please try again later.')->withInput();
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
        $modules['permission_delete'] = Helper::directCan($modules['permission_prefix'] . '-delete');
        if (!$modules['permission_delete']) {
            if (isset($request) && $request->ajax()) {
                return $this->sendError('User does not have the right permissions.', [], [], 403);
            }
            abort(403, 'User does not have the right permissions.');
        }
        $isAjax = $request->ajax();

        try {
            $dataDelete = MasterHoliday::findOrFail($id);

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
        $modules['permission_delete'] = Helper::directCan($modules['permission_prefix'] . '-delete');
        if (!$modules['permission_delete']) {
            return response()->json([
                'status' => 'error',
                'message' => 'User does not have the right permissions.'
            ], 403);
        }

        try {
            $holiday = MasterHoliday::withTrashed()->findOrFail($id);
            $holiday->restore();

            return response()->json([
                'status' => 'success',
                'message' => 'Holiday restored successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function permanentDelete(Request $request, $id)
    {
        $modules = $this->modules;
        $modules['permission_delete'] = Helper::directCan($modules['permission_prefix'] . '-delete');

        if (!$modules['permission_delete']) {
            return response()->json([
                'success' => false,
                'message' => 'User does not have the right permissions.'
            ], 403);
        }

        try {
            $data = MasterHoliday::withTrashed()->findOrFail($id);
            $data->forceDelete();

            return response()->json([
                'success' => true,
                'message' => $modules['title'] . ' permanently deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
