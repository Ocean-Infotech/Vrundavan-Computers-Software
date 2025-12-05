<?php

namespace App\Http\Controllers\admin\master;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Master\MasterClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Requests\MasterClassRequest;
use App\Models\CourceRegistration;
use App\Models\FeesCollection;
use App\Models\Master\MasterShift;
use App\Models\StudentRequest;
use App\Models\Timetable;
use Illuminate\Database\Eloquent\Builder;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class MasterClassController extends Controller
{
    public $modules = [];
    protected array $exportableColumns = [
        'class' => 'Class',
        'status' => 'Status',
    ];

    protected array $defaultExportColumns = [
        'class',
        'status',
    ];

    public function __construct()
    {
        $this->modules = [
            'title' => 'Class',
            'folder_path' => 'software.module.master.class',
            'route' => 'class',
            'table_name' => (new MasterClass())->getTable(),
            'permission_prefix' => 'class',
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
                (object)['data' => "class", 'name' => 'class', 'td_label' => 'Class'],
                (object)['data' => "status", 'name' => 'status', 'td_label' => 'Status', 'className' =>  'w-5 text-center', 'width' => '10%'],
                (object)['data' => "action", 'name' => 'action', 'td_label' => 'Action', 'orderable' => false, 'searchable' => false, 'className' =>  'w-10 text-center'],
            ];
            View::share("columns", $columns);

            $data = [];

            if ($request->ajax()) {
                // dd($request->all());
                $data = MasterClass::select('*');
                $data = $data->withTrashed();

                $returnData = Datatables::of($data)
                    ->addIndexColumn()
                    ->filter(function ($query) use ($request) {
                        if ($request->has('search')) {
                            $query->where('class', 'like', "%" . $request->search . "%");
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
                    ->addColumn('action', function ($row) use ($modules) {
                        $btn = '';

                        // ================================
                        // CASE-1: Record Not Deleted
                        // ================================
                        if (!$row?->deleted_at) {

                            // 🔹 Edit Button
                            if ($modules['permission_edit']) {
                                $btn .= '<a href="' . route($modules["route"] . ".edit", [$row["id"]]) . '"
                        class="btn btn-light btn-icon mx-1">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </a>';
                            }

                            // 🔹 Soft Delete Button
                            if ($modules['permission_delete']) {
                                $btn .= '<a href="javascript:void(0)"
                        data-id="' . $row->id . '"
                        data-did="' . route($modules["route"] . ".destroy", [$row["id"]]) . '"
                        class="btn btn-danger btn-icon deletebutton mx-1">
                        <i class="fa-solid fa-trash"></i>
                    </a>';

                                // 🔹 Permanent Delete Button
                                $btn .= '<a href="javascript:void(0)"
                        data-id="' . $row->id . '"
                        data-did="' . route($modules["route"] . ".permanent-delete", [$row["id"]]) . '"
                        class="btn btn-dark btn-icon permanentDeleteButton mx-1"
                        title="Permanent Delete">
                        <i class="fa-solid fa-trash-arrow-up"></i>
                    </a>';
                            }
                        }

                        // ================================
                        // CASE-2: Soft Deleted Record
                        // ================================
                        else {
                            $btn .= '<a href="javascript:void(0)"
                    data-restore="' . route($modules["route"] . ".restore", ['id' => $row["id"]]) . '"
                    class="btn btn-light mx-1 record-restore"
                    data-bs-toggle="tooltip"
                    title="Restore Data">
                    <i class="ti ti-history"></i> Restore
                </a>';
                        }

                        return $btn ?: '-';
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

    public function store(MasterClassRequest $request)
    {
        $validated = $request->validated();

        try {
            $validated['created_by'] = Auth::user()->id;

            $class = MasterClass::create($validated);

            if ($request->ajax()) {
                return response()->json([
                    'id' => $class->id,
                    'name' => $class->class,
                ]);
            }
            if ($request->action == "save_next") {
                return redirect()->route('class.create')
                    ->withSuccess('Class created successfully! Add next record.');
            }
            // For non-AJAX, redirect normally
            return redirect()->route('class.index')->withSuccess('Class created successfully!');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
            }
            return redirect()->route('dashboard')->withErrors($e->getMessage());
        }
    }

    public function edit(string $id)
    {
        try {
            $modules = $this->modules;
            View::share('modules', $modules);

            $edit = MasterClass::findOrFail($id);
            return view($modules['folder_path'] . '.form', compact('modules', 'edit'));
        } catch (\Exception $e) {
            return Redirect::route('dashboard')->withErrors($e->getMessage());
        }
    }

    public function update(MasterClassRequest $request, string $id)
    {
        $modules = $this->modules;
        // return $request->all();
        $request['id'] = $id;

        $validated = $request->validated();
        try {
            $validated['updated_by'] = Auth::user()->id;
            // return $validated;
            $updateData = MasterClass::findOrFail($id);
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
            $designation = MasterClass::withTrashed()->findOrFail($request?->id);

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
            $class = MasterClass::findOrFail($id);

            // 🔍 Check if Class is used anywhere
            $isUsed =
                MasterShift::where('class_id', $class->id)->exists() ||

                CourceRegistration::where('class_id', $class->id)->exists() ||
                Timetable::where('class_id', $class->id)->exists();

            if ($isUsed) {
                $message = 'This class cannot be deleted because it is already assigned in others Data.';

                if ($isAjax) {
                    return response()->json([
                        'success' => false,
                        'message' => $message
                    ], 400);
                }

                return redirect()->route($modules['route'] . '.index')->withErrors($message);
            }

            // Soft delete logic
            $class->deleted_by = Auth::user()?->id;
            $class->save();
            $class->delete();

            $success = $modules['title'] . ' deleted successfully.';

            if ($isAjax) {
                return response()->json([
                    'success' => true,
                    'message' => $success,
                ]);
            }

            return redirect()->route($modules['route'] . '.index')
                ->withSuccess($success);
        } catch (\Exception $e) {

            if ($isAjax) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ]);
            }

            return redirect()->route($modules['route'] . '.index')
                ->withErrors($e->getMessage());
        }
    }
    public function restore($id)
    {
        $modules = $this->modules;

        try {
            $class = MasterClass::withTrashed()->findOrFail($id);

            // 🔍 Check if class is used anywhere
            $isUsed =
                MasterShift::where('class_id', $class->id)->exists() ||

                CourceRegistration::where('class_id', $class->id)->exists() ||
                Timetable::where('class_id', $class->id)->exists();

            if ($isUsed) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'This class cannot be restored because it is already assigned in others data.'
                ], 400);
            }

            // Restore soft deleted class
            $class->restore();

            return response()->json([
                'status' => 'success',
                'message' => 'Class restored successfully!'
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function permanent_delete(Request $request, $id)
{
    $modules = $this->modules;

    try {

        $class = MasterClass::withTrashed()->findOrFail($id);

        // 🔍 Check if Class is used anywhere
        $isUsed =
            MasterShift::where('class_id', $class->id)->exists() ||
            CourceRegistration::where('class_id', $class->id)->exists() ||
            Timetable::where('class_id', $class->id)->exists();

        if ($isUsed) {
            return response()->json([
                'success' => false,
                'message' => 'This class cannot be permanently deleted because it is already assigned in others data.'
            ], 400);
        }

        // 🔥 Permanent Delete (force delete)
        $class->forceDelete();

        return response()->json([
            'success' => true,
            'message' => 'Class permanently deleted successfully.'
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
        $this->authorizeClassList();

        $scope = strtolower($request->get('scope', 'filtered')) === 'all' ? 'all' : 'filtered';
        $selectedColumns = $this->resolveSelectedColumns($request->input('columns', []));
        $columnLabels = $this->mapColumnLabels($selectedColumns);

        $classes = $this->buildClassExportQuery($request, $scope)->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        foreach (array_values($columnLabels) as $index => $label) {
            $sheet->setCellValueByColumnAndRow($index + 1, 1, $label);
        }

        $rowNumber = 2;
        foreach ($classes as $classItem) {
            foreach ($selectedColumns as $columnIndex => $columnKey) {
                $sheet->setCellValueByColumnAndRow(
                    $columnIndex + 1,
                    $rowNumber,
                    $this->formatColumnValueForExcel($classItem, $columnKey)
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

        $fileName = 'class-master-' . now()->format('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function printView(Request $request)
    {
        $this->authorizeClassList();

        $scope = strtolower($request->get('scope', 'filtered')) === 'all' ? 'all' : 'filtered';
        $selectedColumns = $this->resolveSelectedColumns($request->input('columns', []));
        $columnLabels = $this->mapColumnLabels($selectedColumns);
        $searchTerm = $scope === 'filtered' ? trim((string) $request->get('search', '')) : null;

        $classes = $this->buildClassExportQuery($request, $scope)->get();

        return view($this->modules['folder_path'] . '.print', [
            'classes' => $classes,
            'columnLabels' => $columnLabels,
            'columnKeys' => $selectedColumns,
            'scope' => $scope,
            'searchTerm' => $searchTerm,
            'generatedAt' => now(),
        ]);
    }

    protected function authorizeClassList(): void
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

    protected function buildClassExportQuery(Request $request, string $scope): Builder
    {
        $query = MasterClass::query()
            ->withTrashed()
            ->orderByDesc('id');

        if ($scope === 'all') {
            return $query;
        }

        $search = trim((string) $request->get('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('class', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");
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

    protected function formatColumnValueForExcel(MasterClass $classItem, string $columnKey)
    {
        switch ($columnKey) {
            case 'class':
                return $classItem->class ?? '-';
            case 'status':
                return $classItem->status ? ucfirst($classItem->status) : '-';
            default:
                return data_get($classItem, $columnKey, '-');
        }
    }
}
