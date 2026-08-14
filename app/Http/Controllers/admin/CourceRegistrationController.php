<?php

namespace App\Http\Controllers\admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;

use App\Models\Admission;

use App\Models\CourceRegistration;
use App\Http\Requests\CourceRegistrationRequest;
use App\Models\Master\MasterCourse;
use App\Models\Master\MasterDepartment;
use App\Models\FeesCollection;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;


class CourceRegistrationController extends Controller
{
    public $modules = [];
    public function __construct()
    {
        $this->modules = [
            'title' => 'Cource Registration',
            'folder_path' => 'software.module.cource-registration',
            'route' => 'cource-registration',
            'table_name' => (new CourceRegistration())->getTable(),
            'permission_prefix' => 'course-register',
        ];

        // $this->middleware('permission:'.$this->modules['permission_prefix'].'-list', ['only' => ['index','show']]);
        // $this->middleware('permission:'.$this->modules['permission_prefix'].'-create', ['only' => ['create','store']]);
        // $this->middleware('permission:'.$this->modules['permission_prefix'].'-edit', ['only' => ['edit','update']]);
        // $this->middleware('permission:'.$this->modules['permission_prefix'].'-delete', ['only' => ['destroy']]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $modules = $this->modules;
        $modules['login_user_role'] = Helper::getLoginUserRole();
        $modules['permission_list'] = Helper::directCan($modules['permission_prefix'] . '-list');
        $modules['permission_add'] = Helper::directCan($modules['permission_prefix'] . '-create');
        $modules['permission_edit'] = Helper::directCan($modules['permission_prefix'] . '-edit');
        $modules['permission_delete'] = Helper::directCan($modules['permission_prefix'] . '-delete');
        if (!$modules['permission_list']) {
            if (isset($request) && $request->ajax()) {
                return $this->sendError('User does not have the right permissions.', [], [], 403);
            }
            abort(403, 'User does not have the right permissions.');
        }
        View::share('modules', $modules);
        try {
            $columns = [
                (object) ['data' => 'register_id', 'name' => 'register_id', 'td_label' => 'Stud ID'],
                (object) ['data' => 'id', 'name' => 'id', 'td_label' => 'Addm ID'],
                (object) ['data' => 'student_name', 'name' => 'register_id', 'td_label' => 'Student Name'],
                (object) ['data' => 'course.course_name', 'name' => 'course_id', 'td_label' => 'Course Name'],
                (object) ['data' => 'batch_and_class_or_shift', 'name' => 'batch_and_class_or_shift', 'td_label' => 'Batch and Class or Shift'],
                (object) ['data' => 'fee', 'name' => 'fee', 'td_label' => 'Course Fees'],
                (object) ['data' => 'date', 'name' => 'date', 'td_label' => 'Date'],
                (object) ['data' => 'note', 'name' => 'note', 'td_label' => 'Note'],
                (object) ['data' => 'status', 'name' => 'status', 'td_label' => 'Status', 'className' => 'w-5 text-center', 'width' => '10%', 'orderable' => false],
                (object) ['data' => 'action', 'name' => 'action', 'td_label' => 'Action', 'className' => 'w-10 text-center', 'orderable' => false, 'searchable' => false],
            ];
            View::share("columns", $columns);

            $data = [];

            if ($request->ajax()) {
                // dd($request->all());
                $data = CourceRegistration::select('*');
                $data = $data->withTrashed();
                $data = $data->orderBy('created_at', 'desc');
                $data = $data->with(['course', 'admission', 'batch', 'class', 'shift']);
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
                    ->editColumn('student_name', function ($row) {
                        $first = $row->admission->first_name ?? '';

                        $last = $row->admission->last_name ?? '';
                         $father = $row->admission->father_name ?? '';
                        return trim("{$first} {$last}  {$father} ");
                    })

                    ->editColumn('batch_and_class_or_shift', function ($row) {
                        $batch = $row->batch->batch_name ?? '';
                        $class = $row->class->class ?? '';

                        $from_time = Carbon::parse($row->shift->from_time)->format('h:i A');
                        $to_time = Carbon::parse($row->shift->to_time)->format('h:i A');

                        $shift = $from_time . ' To ' . $to_time ?? '';
                        return trim("{$batch}<br>{$class}<br>{$shift}");
                    })

                    ->editColumn('date', function ($row) {
                        return Carbon::parse($row->date)->format('d-m-Y');
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
                    ->rawColumns(['status', 'action', 'student_name', 'data', 'batch_and_class_or_shift'])
                    ->make(true);
                return $returnData;
            }
            return view($modules['folder_path'] . '.index');
        } catch (\Exception $e) {
            return Redirect::route('software.dashboard')->withErrors($e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($admission_id)
    {
        $modules = $this->modules;
        $modules['login_user_role'] = Helper::getLoginUserRole();
        $modules['permission_add'] = Helper::directCan($modules['permission_prefix'] . '-create');
        if (!$modules['permission_add']) {
            if (isset($request) && $request->ajax()) {
                return $this->sendError('User does not have the right permissions.', [], [], 403);
            }
            abort(403, 'User does not have the right permissions.');
        }
        View::share('modules', $modules);


        View::share('admission_id', $admission_id);

        $admissions = Admission::whereIn('status', ['created', 'completed'])->get();
        if (Session::get('admission_id')) {
            View::share('admission_id', Session::get('admission_id'));
            // Session::remove('admission_id');
        }


        // dd($modules, request()->all(), Session::get('admission_id'), $admissions,$admission_id);
        return view($modules['folder_path'] . '.form', compact('modules', 'admissions'));
    }



    public function get_register_cource(Request $request)
    {
        $id = $request->id;

        try {
            $getcource = CourceRegistration::where('register_id', $id)->with(['course'])->get();

            $admission = Admission::find($id);
            return response()->json([
                'data' => $getcource,
                'admission' => $admission,
            ]);
        } catch (\Exception $e) {
            return Redirect::route('software.dashboard')->withErrors($e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CourceRegistrationRequest $request)
    {
        $modules = $this->modules;
        $modules['login_user_role'] = Helper::getLoginUserRole();
        $modules['permission_add'] = Helper::directCan($modules['permission_prefix'] . '-create');
        if (!$modules['permission_add']) {
            if (isset($request) && $request->ajax()) {
                return $this->sendError('User does not have the right permissions.', [], [], 403);
            }
            abort(403, 'User does not have the right permissions.');
        }
        View::share('modules', $modules);

        try {
            $coursereg = $request->validated();
            // if (!empty($coursereg['department'])) {
            //     $departmentName = MasterDepartment::where('id', $coursereg['department'])
            //         ->value('name');
            //     $coursereg['department'] = $departmentName ?? null;
            // }
            // Format the date if provided
            if (!empty($coursereg['date'])) {
                $coursereg['date'] = Carbon::parse($coursereg['date'])->format('Y-m-d');
            }

            $coursereg['created_by'] = Auth::id();
            if (Session::get('admission_id')) {
                $coursereg['admission_id'] = Session::get('admission_id');
                Session::remove('admission_id');
            }
            // return $coursereg;
            CourceRegistration::create($coursereg);

            // dd($coursereg, $request->all());
            return Redirect::route($modules['route'] . '.index')
                ->withSuccess($modules['title'] . ' Create Successfully');
        } catch (\Exception $e) {
            return Redirect::route('software.dashboard')->withErrors($e->getMessage());
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
        $modules = $this->modules;
        $modules['login_user_role'] = Helper::getLoginUserRole();
        $modules['permission_edit'] = Helper::directCan($modules['permission_prefix'] . '-edit');

        if (!$modules['permission_edit']) {
            if (request()->ajax()) {
                return $this->sendError('User does not have the right permissions.', [], [], 403);
            }
            abort(403, 'User does not have the right permissions.');
        }

        View::share('modules', $modules);

        try {
            $admissions = Admission::all();
            $edit = CourceRegistration::findOrFail($id);




            return view(
                $modules['folder_path'] . '.form',
                compact('modules', 'edit', 'admissions')
            );
        } catch (\Exception $e) {
            return Redirect::route('software.dashboard')->withErrors($e->getMessage());
        }
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(CourceRegistrationRequest $request, string $id)
    {

        $modules = $this->modules;
        $modules['login_user_role'] = Helper::getLoginUserRole();
        $modules['permission_edit'] = Helper::directCan($modules['permission_prefix'] . '-edit');
        if (!$modules['permission_edit']) {
            if (isset($request) && $request->ajax()) {
                return $this->sendError('User does not have the right permissions.', [], [], 403);
            }
            abort(403, 'User does not have the right permissions.');
        }
        // return $request->all();
        $request['id'] = $id;

        $validated = $request->validated();

        try {
            $validated['updated_by'] = Auth::user()->id;
            // return $validated;
            // dd($request->all(),$validated);
            $updateData = CourceRegistration::findOrFail($id);
            if ($updateData) {
                unset($validated['id']);

                if (!empty($validated['date'])) {
                    $validated['date'] = Carbon::parse($validated['date'])->format('Y-m-d');
                }


                $updateData->update($validated);

                return Redirect::route('admission.index')->withSuccess($modules['title'] . ' update successfully');
            }
            return Redirect::back()->withErrors('something went wrong please try again later')->withInput();
        } catch (\Exception $e) {
            return Redirect::route('software.dashboard')->withErrors($e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $modules = $this->modules;
        $modules['login_user_role'] = Helper::getLoginUserRole();
        $modules['permission_delete'] = Helper::directCan($modules['permission_prefix'] . '-delete');
        if (!$modules['permission_delete']) {
            if (isset($request) && $request->ajax()) {
                return $this->sendError('User does not have the right permissions.', [], [], 403);
            }
            abort(403, 'User does not have the right permissions.');
        }
        $isAjax = $request->ajax();

        try {
            $dataDelete = CourceRegistration::findOrFail($id);

            // Soft delete logic
            $dataDelete->deleted_by = Auth::user()?->id;
            $dataDelete->save();

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

        try {

            $restore_data = CourceRegistration::withTrashed()->findOrFail($id);
            $restore_data->restore();

            return Redirect::back()->withSuccess($modules['title'] . ' restored successfully!');
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }
    public function update_status(Request $request)
    {
        $isAjax = ($request->ajax()) ? true : false;

        $modules = $this->modules;
        $modules['login_user_role'] = Helper::getLoginUserRole();
        $modules['permission_edit'] = Helper::directCan($modules['permission_prefix'] . '-edit');
        if (!$modules['permission_edit']) {
            if (isset($request) && $request->ajax()) {
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
            $country = CourceRegistration::withTrashed()->findOrFail($request?->id);
            if ($country) {
                $country->status = $request->update_status;
                $country->save();
                if ($isAjax) {
                    return $this->sendResponse($country, $modules['title'] . ' status update successfully.');
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
}
