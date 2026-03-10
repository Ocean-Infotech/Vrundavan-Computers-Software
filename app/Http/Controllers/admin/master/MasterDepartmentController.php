<?php

namespace App\Http\Controllers\admin\master;

use App\Http\Controllers\Controller;
use App\Http\Requests\MasterDepartmentRequest;
use App\Models\Master\MasterDepartment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\Helper;
use App\Models\CourceRegistration;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Builder;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


class MasterDepartmentController extends Controller
{
    public $modules = [];
    protected array $exportableColumns = [
        'name' => 'Department Name',
        'status' => 'Status',
    ];

    protected array $defaultExportColumns = [
        'name',
        'status',
    ];

    public function __construct()
    {
        $this->modules = [
            'title' => 'Department',
            'folder_path' => 'software.module.master.department',
            'route' => 'department',
            'table_name' => (new MasterDepartment())->getTable(),
            'permission_prefix' => 'department', // ✅ Correct spelling
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
                (object)['data' => "name", 'name' => 'name', 'td_label' => 'Department Name'],
                (object)['data' => "status", 'name' => 'status', 'td_label' => 'Status', 'className' =>  'w-5 text-center', 'width' => '10%'],
                (object)['data' => "action", 'name' => 'action', 'td_label' => 'Action', 'orderable' => false, 'searchable' => false, 'className' =>  'w-10 text-center'],
            ];
            View::share("columns", $columns);

            if ($request->ajax()) {
                $data = MasterDepartment::withTrashed()
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

    // ================================
    // CASE-1: Normal Row (Not Deleted)
    // ================================
    if (!$row->deleted_at) {

        // 🔹 Edit Button
        if ($modules['permission_edit']) {
            $btn .= '
                <a href="' . route($modules["route"] . ".edit", [$row->id]) . '"
                   class="btn btn-light btn-icon mx-1">
                    <i class="fa-solid fa-pen-to-square"></i>
                </a>';
        }

        // 🔹 Soft Delete Button
        if ($modules['permission_delete']) {
            $btn .= '
                <a href="javascript:void(0)"
                   data-id="' . $row->id . '"
                   data-did="' . route($modules["route"] . ".destroy", [$row->id]) . '"
                   class="btn btn-danger btn-icon deletebutton mx-1">
                    <i class="fa-solid fa-trash"></i>
                </a>';

            // 🔹 Permanent Delete Button (ONLY SHOW WHEN NOT DELETED)
            $btn .= '
                <a href="javascript:void(0)"
                   data-id="' . $row->id . '"
                   data-did="' . route($modules["route"] . ".permanent-delete", [$row->id]) . '"
                   class="btn btn-dark btn-icon permanentDeleteButton mx-1"
                   title="Permanent Delete">
                    <i class="fa-solid fa-trash-arrow-up"></i>
                </a>';
        }
    }

    // ================================
    // CASE-2: Deleted Row (Show Restore Only)
    // ================================
    else {
        $btn .= '
            <a href="javascript:void(0)"
               data-restore="' . route($modules["route"] . ".restore", ['id' => $row->id]) . '"
               class="btn btn-light mx-1 record-restore" title="Restore Data">
                <i class="ti ti-history"></i> Restore
            </a>';
    }

    return $btn ?: '-';
})


                    ->rawColumns(['status', 'action'])
                    ->make(true);
            }

            $availableExportColumns = $this->exportableColumns;
            $defaultExportColumns = $this->defaultExportColumns;
            return view($modules['folder_path'] . '.index', compact('availableExportColumns', 'defaultExportColumns'));
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

    public function store(MasterDepartmentRequest $request)
    {
        $modules = $this->modules;
        $modules['login_user_role'] = Helper::getLoginUserRole();
        $modules['permission_add'] = Helper::directCan($modules['permission_prefix'] . '-create');
        View::share('modules', $modules);
        $validated = $request->validated();
        // dd($validated);

        try {
            $validated['created_by'] = Auth::user()->id;
            $subject = MasterDepartment::create($validated);
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
            $edit = MasterDepartment::findOrFail($id);
            return view($modules['folder_path'] . '.form', compact('modules', 'edit'));
        } catch (\Exception $e) {
            return Redirect::route('software.dashboard')->withErrors($e->getMessage());
        }
    }

    public function update(MasterDepartmentRequest $request, string $id)
    {
        $modules = $this->modules;
        // return $request->all();
        $request['id'] = $id;

        $validated = $request->validated();
        try {
            $validated['updated_by'] = Auth::user()->id;
            // return $validated;
            $updateData = MasterDepartment::findOrFail($id);
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
            $designation = MasterDepartment::withTrashed()->findOrFail($request?->id);

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

            $department = MasterDepartment::findOrFail($id);

            // 🔍 Check if department is used in other modules
            $isUsed =
                CourceRegistration::where('department', $department->id)->exists();


            if ($isUsed) {
                $message = 'This department cannot be deleted because it is already assigned in others Data.';

                if ($isAjax) {
                    return response()->json([
                        'success' => false,
                        'message' => $message
                    ], 400);
                }

                return redirect()->route($modules['route'] . '.index')->withErrors($message);
            }

            // Soft delete logic
            $department->deleted_by = Auth::id();
            $department->save();
            $department->delete();

            $successMsg = $modules['title'] . ' deleted successfully.';

            if ($isAjax) {
                return response()->json([
                    'success' => true,
                    'message' => $successMsg
                ]);
            }

            return redirect()->route($modules['route'] . '.index')->withSuccess($successMsg);
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

            $department = MasterDepartment::withTrashed()->findOrFail($id);

            // 🔍 Check if department is still linked in other modules
            $isUsed =
                CourceRegistration::where('department', $department->id)->exists();



            if ($isUsed) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'This department cannot be restored because it is already assigned in others Data'
                ], 400);
            }

            // Restore
            $department->restore();

            return response()->json([
                'status' => 'success',
                'message' => 'Department restored successfully!'
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
            $department = MasterDepartment::withTrashed()->findOrFail($id);

            // Check if department is linked in other modules
            $isUsed = CourceRegistration::where('department', $department->id)->exists();

            if ($isUsed) {
                return response()->json([
                    'success' => false,
                    'message' => 'This department cannot be permanently deleted because it is already assigned in other Data.'
                ], 400);
            }

            // Permanent Delete
            $department->forceDelete();

            return response()->json([
                'success' => true,
                'message' => 'Department permanently deleted successfully!'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function exportExcel(Request $request)
    {
        $this->authorizeDepartmentList();

        $scope = strtolower($request->get('scope', 'filtered')) === 'all' ? 'all' : 'filtered';
        $selectedColumns = $this->resolveSelectedColumns($request->input('columns', []));
        $columnLabels = $this->mapColumnLabels($selectedColumns);

        $departments = $this->buildDepartmentExportQuery($request, $scope)->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        foreach (array_values($columnLabels) as $index => $label) {
            $sheet->setCellValueByColumnAndRow($index + 1, 1, $label);
        }

        $rowNumber = 2;
        foreach ($departments as $department) {
            foreach ($selectedColumns as $columnIndex => $columnKey) {
                $sheet->setCellValueByColumnAndRow(
                    $columnIndex + 1,
                    $rowNumber,
                    $this->formatColumnValueForExcel($department, $columnKey)
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

        $fileName = 'department-master-' . now()->format('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function printView(Request $request)
    {
        $this->authorizeDepartmentList();

        $scope = strtolower($request->get('scope', 'filtered')) === 'all' ? 'all' : 'filtered';
        $selectedColumns = $this->resolveSelectedColumns($request->input('columns', []));
        $columnLabels = $this->mapColumnLabels($selectedColumns);
        $searchTerm = $scope === 'filtered' ? trim((string) $request->get('search', '')) : null;

        $departments = $this->buildDepartmentExportQuery($request, $scope)->get();

        return view($this->modules['folder_path'] . '.print', [
            'departments' => $departments,
            'columnLabels' => $columnLabels,
            'columnKeys' => $selectedColumns,
            'scope' => $scope,
            'searchTerm' => $searchTerm,
            'generatedAt' => now(),
        ]);
    }

    protected function authorizeDepartmentList(): void
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

    protected function buildDepartmentExportQuery(Request $request, string $scope): Builder
    {
        $query = MasterDepartment::query()
            ->withTrashed()
            ->orderByDesc('id');

        if ($scope === 'all') {
            return $query;
        }

        $search = trim((string) $request->get('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
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

    protected function formatColumnValueForExcel(MasterDepartment $department, string $columnKey)
    {
        switch ($columnKey) {
            case 'name':
                return $department->name ?? '-';
            case 'status':
                return $department->status ? ucfirst($department->status) : '-';
            default:
                return data_get($department, $columnKey, '-');
        }
    }
}
