<?php

namespace App\Http\Controllers\admin\master;

use App\Http\Controllers\Controller;
use App\Models\Master\MasterCourse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Requests\MasterCourceRequest;
use App\Traits\BioMetricTrait;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class MasterCourseController extends Controller
{

    use BioMetricTrait;

    public $modules = [];
    public function __construct()
    {
        $this->modules = [
            'title' => 'Course',
            'folder_path' => 'software.module.master.course',
            'route' => 'course',
            'table_name' => (new MasterCourse())->getTable(),
            'permisstion_prefix' => 'course',
        ];

        // $this->middleware('permission:'.$this->modules['permisstion_prefix'].'-list', ['only' => ['index','show']]);
        // $this->middleware('permission:'.$this->modules['permisstion_prefix'].'-create', ['only' => ['create','store']]);
        // $this->middleware('permission:'.$this->modules['permisstion_prefix'].'-edit', ['only' => ['edit','update']]);
        // $this->middleware('permission:'.$this->modules['permisstion_prefix'].'-delete', ['only' => ['destroy']]);

        View::share('course_year_type', MasterCourse::course_year_type());
    }

    public function index(Request $request)
    {
        $modules = $this->modules;
        View::share('modules', $modules);
        try {
            $columns = [
                (object)['data' => "course_name", 'name' => 'course_name', 'td_label' => 'Course Name'],
                (object)['data' => "course_fees", 'name' => 'course_fees', 'td_label' => 'Course Fees'],
                (object)['data' => "course_year", 'name' => 'course_year', 'td_label' => 'Course Year'],
                (object)['data' => "semester", 'name' => 'semester', 'td_label' => 'Course Semester'],

                (object)['data' => "status", 'name' => 'status', 'td_label' => 'Status', 'className' =>  'w-5 text-center', 'width' => '10%'],
                (object)['data' => "action", 'name' => 'action', 'td_label' => 'Action', 'orderable' => false, 'searchable' => false, 'className' =>  'w-10 text-center'],
            ];
            View::share("columns", $columns);

            $data = [];

            if ($request->ajax()) {
                // dd($request->all());
                $data = MasterCourse::select('*');
                $data = $data->withTrashed();
                // $editPermisstion = (Auth::user()->can($modules["table_name"].'-edit')) ? true : false;
                // $deletePermisstion = (Auth::user()->can($modules["table_name"].'-delete')) ? true : false;
                $editPermisstion = true;
                $deletePermisstion = true;

                $returnData = Datatables::of($data)
                    ->addIndexColumn()
                    ->filter(function ($query) use ($request) {
                        if ($request->has('search')) {
                            $query->where('course_name', 'like', "%" . $request->search . "%");
                            $query->orWhere('course_fees', 'like', "%" . $request->search . "%");
                        }
                    })
                    ->editColumn('status', function ($row) use ($modules) {
                        $status = '';
                        $dropdown = "";
                        $dropdown .= '<ul class="dropdown-menu" style="">';

                        if ($row->status == "active") {
                            $status .= '<button type="button" class="btn btn-success btn-sm w-100 dropdown-toggle waves-effect waves-light" data-bs-toggle="dropdown" aria-expanded="false">' . ucfirst($row->status) . '</button>';
                            $dropdown .= '<li><a href="javascript:void(0)" class="dropdown-item waves-effect btn-label-danger update-status" data-url="' . route($modules["route"] . '.status-update') . '" data-id="' . $row["id"] . '"  data-update_status="inactive">Inactive</a></li>';
                            // $btn = '<span class="badge bg-success bg-glow">Active</span>';
                        } elseif ($row->status == "inactive") {
                            $status .= '<button type="button" class="btn btn-danger btn-sm w-100 dropdown-toggle waves-effect waves-light" data-bs-toggle="dropdown" aria-expanded="false">' . ucfirst($row->status) . '</button>';
                            $dropdown .= '<li><a href="javascript:void(0)" class="dropdown-item waves-effect btn-label-success update-status" data-url="' . route($modules["route"] . '.status-update') . '" data-id="' . $row["id"] . '"  data-update_status="active">Active</a></li>';
                            // $btn = '<span class="badge bg-danger bg-glow">In-Active</span>';
                        } else {
                            return $status;
                        }
                        $dropdown .= '</ul>';
                        $status .= $dropdown;
                        return $status;
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
                    ->rawColumns(['status', 'action'])
                    ->make(true);
                return $returnData;
            }
            return view($modules['folder_path'] . '.index', compact('data'));
        } catch (\Exception $e) {
            return Redirect::route('dashboard')->withErrors($e->getMessage());
        }
    }

    public function create()
    {
        $modules = $this->modules;
        View::share('modules', $modules);
        return view($modules['folder_path'] . '.form', compact('modules'));
    }

    public function store(MasterCourceRequest $request)
    {
        $modules = $this->modules;
        View::share('modules', $modules);

        $token = Session::get('external_api_token');

        try {
            $data = $request->validated();
            $loginUserId = Auth::user()->id;
            $data['created_by'] = $loginUserId;

            $mastercourse = MasterCourse::create($data);

            if ($mastercourse?->biometric_id ==  null) {
                /*
                $apiPayload = [
                    'Name' => $mastercourse->course_name,
                ];

                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $token,
                ])->post('http://192.168.1.88:88/api/Department', $apiPayload);

                if ($response->successful()) {
                    $responseData = $response->json();

                    if (isset($responseData['Id'])) {
                        $mastercourse->update([
                            'biometric_id' => $responseData['Id']
                        ]);
                    }
                } else {
                    return Redirect::route('software.dashboard')
                        ->withErrors('Biometric API Error: ' . $response->body());
                }
                        */
            }

            return Redirect::route($modules['route'] . '.index')
                ->withSuccess($modules['title'] . ' Create Successfully');
        } catch (\Exception $e) {
            return Redirect::route('software.dashboard')->withErrors($e->getMessage());
        }
    }


    public function edit(string $id)
    {
        try {
            $modules = $this->modules;
            View::share('modules', $modules);

            $edit = MasterCourse::findOrFail($id);
            return view($modules['folder_path'] . '.form', compact('modules', 'edit'));
        } catch (\Exception $e) {
            return Redirect::route('software.dashboard')->withErrors($e->getMessage());
        }
    }

    public function update(MasterCourceRequest $request, string $id)
    {
        $modules = $this->modules;
        View::share('modules', $modules);

        try {
            $validated = $request->validated();
            $validated['updated_by'] = Auth::user()->id;

            $updateData = MasterCourse::findOrFail($id);
            $updateData->update($validated);

            // if ($updateData?->biometric_id != "") {
            //     $token = Session::get('external_api_token');

            //     $apiResponse = Http::withHeaders([
            //         'Content-Type' => 'application/json',
            //         'Authorization' => 'Bearer ' . $token
            //     ])->post('http://192.168.1.88:88/api/Department', [
            //         "ID" => (int)$updateData->biometric_id,
            //         "Name" => $updateData->course_name
            //     ]);

            //     if ($apiResponse->successful()) {
            //         $responseData = $apiResponse->json();

            //         if (isset($responseData['Id'])) {
            //             $updateData->update([
            //                 'biometric_id' => $responseData['Id']
            //             ]);
            //         } else {
            //             return Redirect::route('software.dashboard')->withErrors('Biometric API did not return a valid ID.');
            //         }
            //     } else {
            //         return Redirect::route('software.dashboard')->withErrors('Biometric API error: ' . $apiResponse->status());
            //     }
            // }
            return Redirect::route($modules['route'] . '.index')
                ->withSuccess($modules['title'] . ' updated successfully');
        } catch (\Exception $e) {
            return Redirect::route('software.dashboard')->withErrors($e->getMessage());
        }
    }


    public function status_update(Request $request)
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
            $designation = MasterCourse::withTrashed()->findOrFail($request?->id);

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
        $isAjax = $request->ajax();

        try {
            $dataDelete = MasterCourse::findOrFail($id);

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

            $restore_data = MasterCourse::withTrashed()->findOrFail($id);
            $restore_data->restore();

            return Redirect::back()->withSuccess($modules['title'] . ' restored successfully!');
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }
}
