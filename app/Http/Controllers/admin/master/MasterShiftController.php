<?php

namespace App\Http\Controllers\admin\master;

use App\Http\Controllers\Controller;
use App\Models\Master\MasterShift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Requests\MasterShiftRequest;

class MasterShiftController extends Controller
{
    public $modules = [];
    public function __construct()
    {
        $this->modules = [
            'title' => 'Shift',
            'folder_path' => 'software.module.master.shift',
            'route' => 'shift',
            'table_name' => (new MasterShift())->getTable(),
            'permisstion_prefix' => 'shift',
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
        try {
            $columns = [
                (object)['data' => "course.course_name", 'name' => 'course_id', 'td_label' => 'Course Name'],
                (object)['data' => "batch.batch_name", 'name' => 'batch_id', 'td_label' => 'Batch Name'],
                (object)['data' => "class.class", 'name' => 'class_id', 'td_label' => 'Class'],
                (object)['data' => "from_time", 'name' => 'from_time', 'td_label' => 'From Time'],
                (object)['data' => "to_time", 'name' => 'to_time', 'td_label' => 'To Time'],
                (object)['data' => "status", 'name' => 'status', 'td_label' => 'Status', 'className' =>  'w-5 text-center', 'width' => '10%'],
                (object)['data' => "action", 'name' => 'action', 'td_label' => 'Action', 'orderable' => false, 'searchable' => false, 'className' =>  'w-10 text-center'],
            ];
            View::share("columns", $columns);

            $data = [];

            if ($request->ajax()) {
                // dd($request->all());
                $data = MasterShift::select('*');
                $data = $data->withTrashed();
                $data = $data->with(['batch', 'course', 'class']);
                // $editPermisstion = (Auth::user()->can($modules["table_name"].'-edit')) ? true : false;
                // $deletePermisstion = (Auth::user()->can($modules["table_name"].'-delete')) ? true : false;
                $editPermisstion = true;
                $deletePermisstion = true;

                $returnData = Datatables::of($data)
                    ->addIndexColumn()
                    ->filter(function ($query) use ($request) {
                        if ($request->has('search')) {
                        $query->where('batch_id', 'like', "%" . $request->search . "%");
                        $query->orWhere('from_time', 'like', "%" . $request->search . "%");
                    }
                })

                    ->editColumn('from_time', function ($row) {
                        return \Carbon\Carbon::parse($row->from_time)->format('h:i A');
                    })
                    ->editColumn('to_time', function ($row) {
                        return \Carbon\Carbon::parse($row->to_time)->format('h:i A');
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

    // public function store(MasterShiftRequest $request)
    // {
    //     $modules = $this->modules;
    //     View::share('modules', $modules);
    //     $validated = $request->validated();

    //     try {
    //         // Check for existing shift with same details
    //         $exists = MasterShift::where('course_id', $validated['course_id'])
    //             ->where('batch_id', $validated['batch_id'])
    //             ->where('class_id', $validated['class_id'])
    //             ->where(function ($query) use ($validated) {
    //                 $query->where(function ($q) use ($validated) {
    //                     $q->where('from_time', '<', $validated['to_time'])
    //                         ->where('to_time', '>', $validated['from_time']);
    //                 });
    //             })
    //             ->exists();

    //         if ($exists) {
    //             $message = "The shift already exists from " .
    //                 date('h:i A', strtotime($validated['from_time'])) . " to " .
    //                 date('h:i A', strtotime($validated['to_time'])) . ".";

    //             if ($request->ajax()) {
    //                 return response()->json([
    //                     'status' => 'error',
    //                     'message' => $message,
    //                 ], 409);
    //             }

    //             return back()->withErrors($message);
    //         }

    //         $validated['created_by'] = Auth::user()->id;
    //         $shift = MasterShift::create($validated);

    //         if ($request->ajax()) {
    //             return response()->json([
    //                 'status' => 'success',
    //                 'data' => [
    //                     'id' => $shift->id,
    //                     'label' => $shift->from_time . ' - ' . $shift->to_time,
    //                 ],
    //                 'message' => $modules['title'] . ' created successfully.',
    //             ]);
    //         }

    //         return Redirect::route($modules['route'] . '.index')
    //             ->withSuccess($modules['title'] . ' Create Successfully');
    //     } catch (\Exception $e) {
    //         if ($request->ajax()) {
    //             return response()->json([
    //                 'status' => 'error',
    //                 'message' => 'Something went wrong.',
    //                 'error' => $e->getMessage(),
    //             ], 500);
    //         }

    //         return Redirect::route('dashboard')->withErrors($e->getMessage());
    //     }
    // }


    public function store(MasterShiftRequest $request)
    {
        $modules = $this->modules;
        View::share('modules', $modules);
        $validated = $request->validated();

        try {
            // 1. Check for exact duplicate shift (same course, batch, class, from_time, to_time)
            $exactDuplicate = MasterShift::where('course_id', $validated['course_id'])
                ->where('batch_id', $validated['batch_id'])
                ->where('class_id', $validated['class_id'])
                ->where('from_time', $validated['from_time'])
                ->where('to_time', $validated['to_time'])
                ->exists();

            if ($exactDuplicate) {
                $message = "A shift with the same course, batch, class, and exact time already exists.";

                if ($request->ajax()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => $message,
                    ], 409);
                }

                return back()->withErrors($message);
            }

            // 2. Check for overlapping time with same course, batch, class
            $timeOverlap = MasterShift::where('course_id', $validated['course_id'])
                ->where('batch_id', $validated['batch_id'])
                ->where('class_id', $validated['class_id'])
                ->where(function ($query) use ($validated) {
                    $query->where(function ($q) use ($validated) {
                        $q->where('from_time', '<', $validated['to_time'])
                            ->where('to_time', '>', $validated['from_time']);
                    });
                })
                ->exists();

            if ($timeOverlap) {
                $message = "Another shift already exists between " .
                    date('h:i A', strtotime($validated['from_time'])) . " and " .
                    date('h:i A', strtotime($validated['to_time'])) . ".";

                if ($request->ajax()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => $message,
                    ], 409);
                }

                return back()->withErrors($message);
            }

            // 3. If all good, save new shift
            $validated['created_by'] = Auth::user()->id;
            $shift = MasterShift::create($validated);

            if ($request->ajax()) {
                return response()->json([
                    'status' => 'success',
                    'data' => [
                        'id' => $shift->id,
                        'label' => $shift->from_time . ' - ' . $shift->to_time,
                    ],
                    'message' => $modules['title'] . ' created successfully.',
                ]);
            }

            return Redirect::route($modules['route'] . '.index')
                ->withSuccess($modules['title'] . ' Create Successfully');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Something went wrong.',
                    'error' => $e->getMessage(),
                ], 500);
            }

            return Redirect::route('dashboard')->withErrors($e->getMessage());
        }
    }


    public function edit(string $id)
    {
        try {
            $modules = $this->modules;
            View::share('modules', $modules);

            $edit = MasterShift::findOrFail($id);
            return view($modules['folder_path'] . '.form', compact('modules', 'edit'));
        } catch (\Exception $e) {
            return Redirect::route('dashboard')->withErrors($e->getMessage());
        }
    }

    public function update(MasterShiftRequest $request, string $id)
    {
        $modules = $this->modules;
        // return $request->all();
        $request['id'] = $id;

        $validated = $request->validated();
        try {
            $validated['updated_by'] = Auth::user()->id;
            // return $validated;
            $updateData = MasterShift::findOrFail($id);
            if ($updateData) {
                unset($validated['id']);
                $updateData->update($validated);

                return Redirect::route($modules['route'] . '.index')->withSuccess($modules['title'] . ' update successfully');
            }
            return Redirect::back()->withErrors('something went wrong please try again later')->withInput();
        } catch (\Exception $e) {
            return Redirect::route('dashboard')->withErrors($e->getMessage());
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
            $designation = MasterShift::withTrashed()->findOrFail($request?->id);

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
            $dataDelete = MasterShift::findOrFail($id);

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

            $restore_data = MasterShift::withTrashed()->findOrFail($id);
            $restore_data->restore();

            return Redirect::back()->withSuccess($modules['title'] . ' restored successfully!');
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }
}
