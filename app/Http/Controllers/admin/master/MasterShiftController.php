<?php

namespace App\Http\Controllers\admin\master;

use App\Helpers\Helper;
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
use App\Models\CourceRegistration;
use Illuminate\Database\Eloquent\Builder;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class MasterShiftController extends Controller
{
    public $modules = [];
    protected array $exportableColumns = [
        'course_name' => 'Course Name',
        'batch_name' => 'Batch Name',
        'class_name' => 'Class',
        'from_time' => 'From Time',
        'to_time' => 'To Time',
        'status' => 'Status',
    ];

    protected array $defaultExportColumns = [
        'course_name',
        'batch_name',
        'class_name',
        'from_time',
        'to_time',
        'status',
    ];

    public function __construct()
    {
        $this->modules = [
            'title' => 'Shift',
            'folder_path' => 'software.module.master.shift',
            'route' => 'shift',
            'table_name' => (new MasterShift())->getTable(),
            'permission_prefix' => 'shift',
        ];

        // $this->middleware('permission:'.$this->modules['permission_prefix'].'-list', ['only' => ['index','show']]);
        // $this->middleware('permission:'.$this->modules['permission_prefix'].'-create', ['only' => ['create','store']]);
        // $this->middleware('permission:'.$this->modules['permission_prefix'].'-edit', ['only' => ['edit','update']]);
        // $this->middleware('permission:'.$this->modules['permission_prefix'].'-delete', ['only' => ['destroy']]);
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
            if (isset($request) && $request->ajax()) {
                return $this->sendError('User does not have the right permissions.', [], [], 403);
            }
            abort(403, 'User does not have the right permissions.');
        }
        View::share('modules', $modules);
        try {
            $columns = [
                (object)[
                    'data' => "course.course_name",
                    'name' => 'course_id',
                    'td_label' => 'Course Name',
                    'defaultContent' => '-',
                ],
                (object)[
                    'data' => "batch.batch_name",
                    'name' => 'batch_id',
                    'td_label' => 'Batch Name',
                    'defaultContent' => '-',
                ],
                (object)[
                    'data' => "class.class",
                    'name' => 'class_id',
                    'td_label' => 'Class',
                    'defaultContent' => '-',
                ],
                (object)[
                    'data' => "from_time",
                    'name' => 'from_time',
                    'td_label' => 'From Time',
                    'defaultContent' => '-',
                ],
                (object)[
                    'data' => "to_time",
                    'name' => 'to_time',
                    'td_label' => 'To Time',
                    'defaultContent' => '-',
                ],
                (object)[
                    'data' => "status",
                    'name' => 'status',
                    'td_label' => 'Status',
                    'className' => 'w-5 text-center',
                    'width' => '10%',
                    'defaultContent' => '-',
                ],
                (object)[
                    'data' => "action",
                    'name' => 'action',
                    'td_label' => 'Action',
                    'orderable' => false,
                    'searchable' => false,
                    'className' => 'w-10 text-center',
                    'defaultContent' => '-',
                ],
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
                $editPermisstion = $modules['permission_edit'];
                $deletePermisstion = $modules['permission_delete'];

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
            $availableExportColumns = $this->exportableColumns;
            $defaultExportColumns = $this->defaultExportColumns;
            return view($modules['folder_path'] . '.index', compact('data', 'availableExportColumns', 'defaultExportColumns'));
        } catch (\Exception $e) {
            return Redirect::route('dashboard')->withErrors($e->getMessage());
        }
    }

    public function create()
    {
        $modules = $this->modules;
        $modules['permission_add'] = Helper::directCan($modules['permission_prefix'] . '-create');
        if (!$modules['permission_add']) {
            if (isset($request) && $request->ajax()) {
                return $this->sendError('User does not have the right permissions.', [], [], 403);
            }
            abort(403, 'User does not have the right permissions.');
        }
        View::share('modules', $modules);
        return view($modules['folder_path'] . '.form', compact('modules'));
    }

    public function store(MasterShiftRequest $request)
    {
        $modules = $this->modules;
        $modules['permission_add'] = Helper::directCan($modules['permission_prefix'] . '-create');
        if (!$modules['permission_add']) {
            if (isset($request) && $request->ajax()) {
                return $this->sendError('User does not have the right permissions.', [], [], 403);
            }
            abort(403, 'User does not have the right permissions.');
        }
        View::share('modules', $modules);

        $validated = $request->validated();

        try {
            // Final check before save (defensive duplication prevention)
            $exists = MasterShift::where('course_id', $validated['course_id'])
                ->where('batch_id', $validated['batch_id'])
                ->where('class_id', $validated['class_id'])
                ->where(function ($query) use ($validated) {
                    $query->where('from_time', '<', $validated['to_time'])
                        ->where('to_time', '>', $validated['from_time']);
                })
                ->exists();

            if ($exists) {
                $message = "A shift already exists between " .
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
            if ($request->action == "save_next") {
                return redirect()->route($modules['route'] . '.create')
                    ->withSuccess($modules['title'] . ' created successfully. Add next shift.');
            }
            return Redirect::route($modules['route'] . '.index')
                ->withSuccess($modules['title'] . ' created successfully.');
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



    // public function store(MasterShiftRequest $request)
    // {
    //     $modules = $this->modules;
    //     View::share('modules', $modules);
    //     $validated = $request->validated();

    //     try {
    //         // 1. Check for exact duplicate shift (same course, batch, class, from_time, to_time)
    //         $exactDuplicate = MasterShift::where('course_id', $validated['course_id'])
    //             ->where('batch_id', $validated['batch_id'])
    //             ->where('class_id', $validated['class_id'])
    //             ->where('from_time', $validated['from_time'])
    //             ->where('to_time', $validated['to_time'])
    //             ->exists();

    //         dd($exactDuplicate);
    //         exit();

    //         if ($exactDuplicate) {
    //             $message = "A shift with the same course, batch, class, and exact time already exists.";

    //             if ($request->ajax()) {
    //                 return response()->json([
    //                     'status' => 'error',
    //                     'message' => $message,
    //                 ], 409);
    //             }

    //             return back()->withErrors($message);
    //         }

    //         // 2. Check for overlapping time with same course, batch, class
    //         $timeOverlap = MasterShift::where('course_id', $validated['course_id'])
    //             ->where('batch_id', $validated['batch_id'])
    //             ->where('class_id', $validated['class_id'])
    //             ->where(function ($query) use ($validated) {
    //                 $query->where(function ($q) use ($validated) {
    //                     $q->where('from_time', '<', $validated['to_time'])
    //                         ->where('to_time', '>', $validated['from_time']);
    //                 });
    //             })
    //             ->exists();

    //         if ($timeOverlap) {
    //             $message = "Another shift already exists between " .
    //                 date('h:i A', strtotime($validated['from_time'])) . " and " .
    //                 date('h:i A', strtotime($validated['to_time'])) . ".";

    //             if ($request->ajax()) {
    //                 return response()->json([
    //                     'status' => 'error',
    //                     'message' => $message,
    //                 ], 409);
    //             }

    //             return back()->withErrors($message);
    //         }

    //         // 3. If all good, save new shift
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


    public function edit(string $id)
    {
        $modules = $this->modules;
        $modules['permission_edit'] = Helper::directCan($modules['permission_prefix'] . '-edit');
        if (!$modules['permission_edit']) {
            if (isset($request) && $request->ajax()) {
                return $this->sendError('User does not have the right permissions.', [], [], 403);
            }
            abort(403, 'User does not have the right permissions.');
        }
        View::share('modules', $modules);
        try {

            $edit = MasterShift::findOrFail($id);
            return view($modules['folder_path'] . '.form', compact('modules', 'edit'));
        } catch (\Exception $e) {
            return Redirect::route('dashboard')->withErrors($e->getMessage());
        }
    }

    public function update(MasterShiftRequest $request, string $id)
    {
        $modules = $this->modules;
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
            $shift = MasterShift::findOrFail($id);

            // 🔍 Check if Shift is used anywhere
            $isUsed =
                CourceRegistration::where('shift_id', $shift->id)->exists() ;


            if ($isUsed) {
                $message = 'This shift cannot be deleted because it is already assigned in others Data.';

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
            $shift = MasterShift::withTrashed()->findOrFail($id);

            // 🔍 Check if Shift is used
            $isUsed =
                CourceRegistration::where('shift_id', $shift->id)->exists() ;

            if ($isUsed) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'This shift cannot be restored because it is already assigned in others Data.'
                ], 400);
            }

            // Restore
            $shift->restore();

            return response()->json([
                'status' => 'success',
                'message' => 'Shift restored successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function exportExcel(Request $request)
    {
        $this->authorizeShiftList();

        $scope = strtolower($request->get('scope', 'filtered')) === 'all' ? 'all' : 'filtered';
        $selectedColumns = $this->resolveSelectedColumns($request->input('columns', []));
        $columnLabels = $this->mapColumnLabels($selectedColumns);

        $shifts = $this->buildShiftExportQuery($request, $scope)->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        foreach (array_values($columnLabels) as $index => $label) {
            $sheet->setCellValueByColumnAndRow($index + 1, 1, $label);
        }

        $rowNumber = 2;
        foreach ($shifts as $shift) {
            foreach ($selectedColumns as $columnIndex => $columnKey) {
                $sheet->setCellValueByColumnAndRow(
                    $columnIndex + 1,
                    $rowNumber,
                    $this->formatColumnValueForExcel($shift, $columnKey)
                );
            }
            $rowNumber++;
        }

        if (!empty($selectedColumns)) {
            for ($i = 1; $i <= count($selectedColumns); $i++) {
                $columnLetter = Coordinate::stringFromColumnIndex($i);
                $sheet->getColumnDimension($columnLetter)->setAutoSize(true);
            }
        }

        $fileName = 'shift-master-' . now()->format('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function printView(Request $request)
    {
        $this->authorizeShiftList();

        $scope = strtolower($request->get('scope', 'filtered')) === 'all' ? 'all' : 'filtered';
        $selectedColumns = $this->resolveSelectedColumns($request->input('columns', []));
        $columnLabels = $this->mapColumnLabels($selectedColumns);
        $searchTerm = $scope === 'filtered' ? trim((string) $request->get('search', '')) : null;

        $shifts = $this->buildShiftExportQuery($request, $scope)->get();

        return view($this->modules['folder_path'] . '.print', [
            'shifts' => $shifts,
            'columnLabels' => $columnLabels,
            'columnKeys' => $selectedColumns,
            'scope' => $scope,
            'searchTerm' => $searchTerm,
            'generatedAt' => now(),
        ]);
    }

    protected function authorizeShiftList(): void
    {
        $canList = Helper::directCan($this->modules['permission_prefix'] . '-list');
        if (!$canList) {
            abort(403, 'User does not have the right permissions.');
        }
    }

    protected function resolveSelectedColumns($columns): array
    {
        if (!is_array($columns) || empty($columns)) {
            return $this->defaultExportColumns;
        }

        $validColumns = array_keys($this->exportableColumns);
        $filtered = array_values(array_filter($columns, function ($column) use ($validColumns) {
            return in_array($column, $validColumns, true);
        }));

        return !empty($filtered) ? $filtered : $this->defaultExportColumns;
    }

    protected function buildShiftExportQuery(Request $request, string $scope): Builder
    {
        $query = MasterShift::query()
            ->withTrashed()
            ->with(['course', 'batch', 'class'])
            ->orderByDesc('id');

        if ($scope === 'all') {
            return $query;
        }

        $search = trim((string) $request->get('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('from_time', 'like', "%{$search}%")
                    ->orWhere('to_time', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhereHas('course', function ($courseQuery) use ($search) {
                        $courseQuery->where('course_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('batch', function ($batchQuery) use ($search) {
                        $batchQuery->where('batch_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('class', function ($classQuery) use ($search) {
                        $classQuery->where('class', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return $query;
    }

    protected function mapColumnLabels(array $selectedColumns): array
    {
        $labels = [];
        foreach ($selectedColumns as $columnKey) {
            $labels[$columnKey] = $this->exportableColumns[$columnKey] ?? ucfirst(str_replace('_', ' ', $columnKey));
        }
        return $labels;
    }

    protected function formatColumnValueForExcel(MasterShift $shift, string $columnKey)
    {
        switch ($columnKey) {
            case 'course_name':
                return $shift->course?->course_name ?? '-';
            case 'batch_name':
                return $shift->batch?->batch_name ?? '-';
            case 'class_name':
                return $shift->class?->class ?? '-';
            case 'from_time':
                return $shift->from_time
                    ? \Carbon\Carbon::parse($shift->from_time)->format('h:i A')
                    : '-';
            case 'to_time':
                return $shift->to_time
                    ? \Carbon\Carbon::parse($shift->to_time)->format('h:i A')
                    : '-';
            case 'status':
                return $shift->status ? ucfirst($shift->status) : '-';
            default:
                return data_get($shift, $columnKey, '-');
        }
    }
}
