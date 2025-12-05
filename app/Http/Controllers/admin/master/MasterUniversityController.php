<?php

namespace App\Http\Controllers\admin\master;

use App\Http\Controllers\Controller;

use App\Http\Requests\MasterUniversityRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\Helper;
use App\Models\CourceRegistration;
use App\Models\Master\MasterUniversity;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;

class MasterUniversityController extends Controller
{
    public $modules = [];

    public function __construct()
    {
        $this->modules = [
            'title' => 'Master University',
            'folder_path' => 'software.module.master.university',
            'route' => 'university',
            'table_name' => (new MasterUniversity())->getTable(),
            'permission_prefix' => 'university',
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

        // dd($modules);
        if (!$modules['permission_list']) {
            if ($request->ajax()) {
                return $this->sendError('User does not have the right permissions.', [], [], 403);
            }
            abort(403, 'User does not have the right permissions.');
        }

        View::share('modules', $modules);

        try {
            $columns = [
                (object)['data' => "name", 'name' => 'name', 'td_label' => 'University Name'],
                (object)['data' => "status", 'name' => 'status', 'td_label' => 'Status', 'className' =>  'w-5 text-center', 'width' => '10%'],
                (object)['data' => "action", 'name' => 'action', 'td_label' => 'Action', 'orderable' => false, 'searchable' => false, 'className' =>  'w-10 text-center'],
            ];
            View::share("columns", $columns);

            if ($request->ajax()) {
                $data = MasterUniversity::withTrashed()
                    ->orderBy('id', 'desc');
                // dd('hello');

                return Datatables::of($data)
                    ->addIndexColumn()
                    ->filter(function ($query) use ($request) {
                        if ($request->has('search')) {
                            $query->where('name', 'like', "%" . $request->search . "%");
                        }
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
                                $btn .= '<a href="' . route($modules["route"] . ".edit", [$row->id]) . '" class="btn btn-light btn-icon mx-1"><i class="fa-solid fa-pen-to-square"></i></a>';
                            }
                            if ($modules['permission_delete']) {
                                $btn .= '<a href="javascript:void(0)" data-id="' . $row->id . '" data-did="' . route($modules["route"] . ".destroy", [$row->id]) . '" class="btn btn-danger btn-icon deletebutton mx-1"><i class="fa-solid fa-trash"></i></a>';
                            }
                            $btn .= '<a href="javascript:void(0)" data-id="' . $row->id . '"
    data-did="' . route($modules["route"] . ".permanent-delete", [$row->id])    . '"
    class="btn btn-dark btn-icon permanentDeleteButton mx-1" title="Permanent Delete">
    <i class="fa-solid fa-trash-arrow-up"></i>
</a>';
                        } else {
                            $btn .= '<a href="javascript:void(0)" data-restore="' . route($modules["route"] . ".restore", ['id' => $row->id]) . '" class="btn btn-light mx-1 record-restore" title="Restore Data"><i class="ti ti-history"></i> Restore</a>';
                        }
                        return $btn ?: '-';
                    })
                    ->rawColumns(['status', 'action'])
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
        $modules['login_user_role'] = Helper::getLoginUserRole();
        $modules['permission_add'] = Helper::directCan($modules['permission_prefix'] . '-create');
        View::share('modules', $modules);
        return view($modules['folder_path'] . '.form', compact('modules'));
    }

    public function store(MasterUniversityRequest $request)
    {
        $modules = $this->modules;
        $modules['login_user_role'] = Helper::getLoginUserRole();
        $modules['permission_add'] = Helper::directCan($modules['permission_prefix'] . '-create');
        View::share('modules', $modules);
        $validated = $request->validated();
        // dd($validated);

        try {
            $validated['created_by'] = Auth::user()->id;
            $subject = MasterUniversity::create($validated);
            if ($request->ajax()) {
                return response()->json([
                    'id' => $subject->id,
                    'name' => $subject->name,
                ]);
            }
            if ($request->action == "save_next") {
                return redirect()->route($modules['route'] . '.create')
                    ->withSuccess($modules['title'] . ' created successfully! Add next department.');
            }
            return Redirect::route($modules['route'] . '.index')->withSuccess($modules['title'] . ' create successfully');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
            }
            return Redirect::route('dashboard')->withErrors($e->getMessage());
        }
    }


    public function edit(string $id)
    {
        try {
            $modules = $this->modules;
            $modules['login_user_role'] = Helper::getLoginUserRole();
            $modules['permission_list'] = Helper::directCan($modules['permission_prefix'] . '-list');
            $modules['permission_add'] = Helper::directCan($modules['permission_prefix'] . '-create');
            $modules['permission_edit'] = Helper::directCan($modules['permission_prefix'] . '-edit');
            $modules['permission_delete'] = Helper::directCan($modules['permission_prefix'] . '-delete');

            View::share('modules', $modules);
            $edit = MasterUniversity::findOrFail($id);
            return view($modules['folder_path'] . '.form', compact('modules', 'edit'));
        } catch (\Exception $e) {
            return Redirect::route('software.dashboard')->withErrors($e->getMessage());
        }
    }

    public function update(MasterUniversityRequest $request, string $id)
    {
        $modules = $this->modules;
        // return $request->all();
        $request['id'] = $id;

        $validated = $request->validated();
        try {
            $validated['updated_by'] = Auth::user()->id;
            // return $validated;
            $updateData = MasterUniversity::findOrFail($id);
            if ($updateData) {
                unset($validated['id']);
                $updateData->update($validated);
                // dd($validated);

                return Redirect::route($modules['route'] . '.index')->withSuccess($modules['title'] . ' update successfully');
            }
            return Redirect::back()->withErrors('something went wrong please try again later')->withInput();
        } catch (\Exception $e) {
            return Redirect::route('dashboard')->withErrors($e->getMessage());
        }
    }

    public function update_status(Request $request)
    {
        $isAjax = ($request->ajax()) ? true : false;

        $modules = $this->modules;
        $validator = Validator::make($request->all(), [
            'id' => ['required', Rule::exists($modules['table_name'], 'id')],
            'update_status' => ['required', 'in:active,inactive']
        ]);

        if ($validator->fails() && $isAjax) {
            return $this->sendError($validator->messages()->first(), $validator->messages(), [], 401);
        }

        try {
            $designation = MasterUniversity::withTrashed()->findOrFail($request?->id);

            if ($designation) {
                $designation->status = $request->update_status;
                $designation->save();
                if ($isAjax) {
                    return $this->sendResponse($designation, $modules['title'] . ' status update successfully.');
                }
                return Redirect::route($modules['route'] . '.index')->withSuccess($modules['title'] . ' status update successfully.');
            }
            if ($isAjax) {
                return $this->sendError('something went wrong please try again later');
            }
            return Redirect::back()->withErrors('something went wrong please try again later')->withInput();
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
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User does not have the right permissions.'
                ], 403);
            }
            abort(403, 'User does not have the right permissions.');
        }

        $isAjax = $request->ajax();

        try {
            $shift = MasterUniversity::findOrFail($id);

            // 🔍 Check if Shift is used anywhere
            $isUsed =
                CourceRegistration::where('university', $shift->id)->exists();


            if ($isUsed) {
                $message = 'This University cannot be deleted because it is already assigned in others Data.';

                if ($isAjax) {
                    return response()->json([
                        'success' => false,
                        'message' => $message
                    ], 400);
                }

                return redirect()->route($modules['route'] . '.index')->withErrors($message);
            }

            // Soft delete
            $shift->deleted_by = Auth::id();
            $shift->save();
            $shift->delete();

            $success = $modules['title'] . ' deleted successfully.';

            if ($isAjax) {
                return response()->json([
                    'success' => true,
                    'message' => $success
                ]);
            }

            return redirect()->route($modules['route'] . '.index')->withSuccess($success);
        } catch (\Exception $e) {

            if ($isAjax) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }

            return redirect()->route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }
    public function restore($id)
    {
        $modules = $this->modules;

        try {
            $shift = MasterUniversity::withTrashed()->findOrFail($id);

            // 🔍 Check if Shift is used
            $isUsed =
                CourceRegistration::where('university', $shift->id)->exists();

            if ($isUsed) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'This University cannot be restored because it is already assigned in others Data.'
                ], 400);
            }

            // Restore
            $shift->restore();

            return response()->json([
                'status' => 'success',
                'message' => 'University restored successfully!'
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

            $university = MasterUniversity::withTrashed()->findOrFail($id);

            // 🔍 Check If University is used in CourseRegistration
            $isUsed = CourceRegistration::where('university', $university->id)->exists();

            if ($isUsed) {
                return response()->json([
                    'success' => false,
                    'message' => 'This University cannot be deleted permanently because it is assigned in another data.'
                ], 400);
            }

            // 👇 Permanent Delete
            $university->forceDelete();

            return response()->json([
                'success' => true,
                'message' => 'University permanently deleted successfully.'
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
