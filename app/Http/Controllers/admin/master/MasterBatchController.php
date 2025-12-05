<?php

namespace App\Http\Controllers\admin\master;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Master\MasterBatch;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Requests\MasterBatchRequest;
use App\Models\CourceRegistration;
use App\Models\FeesCollection;
use App\Models\StudentRequest;
use App\Models\Timetable;
use Illuminate\Database\Eloquent\Builder;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class MasterBatchController extends Controller
{
    public $modules = [];
    protected array $exportableColumns = [
        'course_name' => 'Course Name',
        'batch_name' => 'Batch Name',
        'status' => 'Status',
    ];

    protected array $defaultExportColumns = [
        'course_name',
        'batch_name',
        'status',
    ];

    public function __construct()
    {
        $this->modules = [
            'title' => 'Batch',
            'folder_path' => 'software.module.master.batch',
            'route' => 'batch',
            'table_name' => (new MasterBatch())->getTable(),
            'permission_prefix' => 'batch',
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
                (object)['data' => "course.course_name", 'name' => 'course_name', 'td_label' => 'Course Name'],
                (object)['data' => "batch_name", 'name' => 'batch_name', 'td_label' => 'Batch Name'],
                (object)['data' => "status", 'name' => 'status', 'td_label' => 'Status', 'className' =>  'w-5 text-center', 'width' => '10%'],
                (object)['data' => "action", 'name' => 'action', 'td_label' => 'Action', 'orderable' => false, 'searchable' => false, 'className' =>  'w-10 text-center'],
            ];
            View::share("columns", $columns);

            $data = [];

            if ($request->ajax()) {
                // dd($request->all());
                $data = MasterBatch::select('*');
                $data = $data->withTrashed();
                $data = $data->with(['course']);

                $returnData = Datatables::of($data)
                    ->addIndexColumn()
                    ->filter(function ($query) use ($request) {
                        if ($request->has('search')) {
                            $query->where('course_id', 'like', "%" . $request->search . "%");
                            $query->orWhere('batch_name', 'like', "%" . $request->search . "%");
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
                    })->editColumn('course.course_name', function ($row) {
                        return $row->course->course_name ?? '-';
                    })

                    ->addColumn('action', function ($row) use ($modules) {
                        $btn = '';
                        if (!$row?->deleted_at) {
                            // $btn .= '<a href="javascript:void(0)" class="edit btn btn-primary btn-sm">View</a>';
                            if ($modules['permission_edit']) {
                                $btn .= '<a href="' . route($modules["route"] . ".edit", [$row["id"]]) . '" class="btn btn-light btn-icon mx-1"><i class="fa-solid fa-pen-to-square"></i></a>';
                            }
                            if ($modules['permission_delete']) {
                                $btn .= '<a href="javascript:void(0)" data-id="' . $row->id . '" data-did="' . route($modules["route"] . ".destroy", [$row["id"]]) . '" class="btn btn-danger btn-icon deletebutton mx-1"><i class="fa-solid fa-trash"></i></i></a>';
                            }
                            $btn .= '<a href="javascript:void(0)"
                data-did="' . route($modules["route"] . ".permanent-delete", [$row["id"]]) . '"
                class="btn btn-danger btn-icon permanentDeleteButton mx-1"
                data-bs-toggle="tooltip" title="Permanent Delete">
                <i class="fa-solid fa-ban"></i>
            </a>';
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
        View::share('modules', $modules);
        return view($modules['folder_path'] . '.form', compact('modules'));
    }

    public function store(MasterBatchRequest $request)
    {
        $modules = $this->modules;
        View::share('modules', $modules);
        $validated = $request->validated();

        try {
            $validated['created_by'] = Auth::user()->id;
            $batch = MasterBatch::create($validated);

            if ($request->ajax()) {
                return response()->json([
                    'id' => $batch->id,
                    'name' => $batch->batch_name,
                    'message' => $modules['title'] . ' created successfully!'
                ]);
            }
            if ($request->action == "save_next") {
                return Redirect::route($modules['route'] . '.create')
                    ->withSuccess($modules['title'] . ' added successfully. Please add next record.');
            }
            // Normal form submit: redirect
            return Redirect::route($modules['route'] . '.index')->withSuccess($modules['title'] . ' Create Successfully');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'message' => 'Error: ' . $e->getMessage()
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

            $edit = MasterBatch::findOrFail($id);
            return view($modules['folder_path'] . '.form', compact('modules', 'edit'));
        } catch (\Exception $e) {
            return Redirect::route('dashboard')->withErrors($e->getMessage());
        }
    }

    public function update(MasterBatchRequest $request, string $id)
    {
        $modules = $this->modules;
        // return $request->all();
        $request['id'] = $id;

        $validated = $request->validated();
        try {
            $validated['updated_by'] = Auth::user()->id;
            // return $validated;
            $updateData = MasterBatch::findOrFail($id);
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
            $designation = MasterBatch::withTrashed()->findOrFail($request?->id);

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
        $modules['login_user_role'] = Helper::getLoginUserRole();
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
            $batch = MasterBatch::findOrFail($id);

            // ✅ Correct dependency checks for batch usage
            $isUsed =
                // StudentRequest::where('batch_id', $batch->id)->exists() ||
                // FeesCollection::where('batch_id', $batch->id)->exists() ||
                CourceRegistration::where('batch_id', $batch->id)->exists() ||
                Timetable::where('batch_id', $batch->id)->exists();

            if ($isUsed) {
                $message = 'This batch cannot be deleted because it is already assigned in other modules.';

                if ($isAjax) {
                    return response()->json(['success' => false, 'message' => $message], 400);
                }

                return redirect()->route($modules['route'] . '.index')->withErrors($message);
            }

            // Soft Delete
            $batch->deleted_by = Auth::id();
            $batch->save();
            $batch->delete();

            $successMsg = $modules['title'] . ' deleted successfully.';

            if ($isAjax) {
                return response()->json(['success' => true, 'message' => $successMsg]);
            }

            return redirect()->route($modules['route'] . '.index')->withSuccess($successMsg);
        } catch (\Exception $e) {
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => $e->getMessage()]);
            }

            return redirect()->route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }
    public function restore($id)
    {
        try {
            $batch = MasterBatch::withTrashed()->findOrFail($id);

            // Correct dependency checks
            $isUsed =
                // StudentRequest::where('batch_id', $batch->id)->exists() ||
                // FeesCollection::where('batch_id', $batch->id)->exists() ||
                CourceRegistration::where('batch_id', $batch->id)->exists() ||
                Timetable::where('batch_id', $batch->id)->exists();

            if ($isUsed) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'This batch cannot be restored because it is already assigned in other modules.'
                ], 400);
            }

            $batch->restore();

            return response()->json([
                'status' => 'success',
                'message' => 'Batch restored successfully.'
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function permanentDelete($id)
    {
        try {
            $modules = $this->modules;

            $batch = MasterBatch::withTrashed()->findOrFail($id);

            // Check dependencies before permanent delete
            $isUsed =
                CourceRegistration::where('batch_id', $batch->id)->exists() ||
                Timetable::where('batch_id', $batch->id)->exists();

            if ($isUsed) {
                return response()->json([
                    'success' => false,
                    'message' => 'This batch cannot be permanently deleted because it is assigned in other modules.'
                ], 400);
            }

            // Permanent delete
            $batch->forceDelete();

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


    public function exportExcel(Request $request)
    {
        $this->authorizeBatchList();

        $scope = strtolower($request->get('scope', 'filtered')) === 'all' ? 'all' : 'filtered';
        $selectedColumns = $this->resolveSelectedColumns($request->input('columns', []));
        $columnLabels = $this->mapColumnLabels($selectedColumns);

        $batches = $this->buildBatchExportQuery($request, $scope)->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        foreach (array_values($columnLabels) as $index => $label) {
            $sheet->setCellValueByColumnAndRow($index + 1, 1, $label);
        }

        $rowNumber = 2;
        foreach ($batches as $batch) {
            foreach ($selectedColumns as $columnIndex => $columnKey) {
                $sheet->setCellValueByColumnAndRow(
                    $columnIndex + 1,
                    $rowNumber,
                    $this->formatColumnValueForExcel($batch, $columnKey)
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

        $fileName = 'batch-master-' . now()->format('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function printView(Request $request)
    {
        $this->authorizeBatchList();

        $scope = strtolower($request->get('scope', 'filtered')) === 'all' ? 'all' : 'filtered';
        $selectedColumns = $this->resolveSelectedColumns($request->input('columns', []));
        $columnLabels = $this->mapColumnLabels($selectedColumns);
        $searchTerm = $scope === 'filtered' ? trim((string) $request->get('search', '')) : null;

        $batches = $this->buildBatchExportQuery($request, $scope)->get();

        return view($this->modules['folder_path'] . '.print', [
            'batches' => $batches,
            'columnLabels' => $columnLabels,
            'columnKeys' => $selectedColumns,
            'scope' => $scope,
            'searchTerm' => $searchTerm,
            'generatedAt' => now(),
        ]);
    }

    protected function authorizeBatchList(): void
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

    protected function buildBatchExportQuery(Request $request, string $scope): Builder
    {
        $query = MasterBatch::query()
            ->withTrashed()
            ->with(['course'])
            ->orderByDesc('id');

        if ($scope === 'all') {
            return $query;
        }

        $search = trim((string) $request->get('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('batch_name', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhereHas('course', function ($courseQuery) use ($search) {
                        $courseQuery->where('course_name', 'like', "%{$search}%");
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

    protected function formatColumnValueForExcel(MasterBatch $batch, string $columnKey)
    {
        switch ($columnKey) {
            case 'course_name':
                return $batch->course?->course_name ?? '-';
            case 'batch_name':
                return $batch->batch_name ?? '-';
            case 'status':
                return $batch->status ? ucfirst($batch->status) : '-';
            default:
                return data_get($batch, $columnKey, '-');
        }
    }
}
