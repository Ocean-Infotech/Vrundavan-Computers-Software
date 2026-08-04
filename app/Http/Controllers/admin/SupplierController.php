<?php

namespace App\Http\Controllers\admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\DataTables;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Database\Eloquent\Builder;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class SupplierController extends Controller
{
    public $modules = [];
    protected array $exportableColumns = [
        'name' => 'Name',
        'category' => 'Category',
        'city' => 'City',
        'mobile' => 'Mobile',
    ];

    protected array $defaultExportColumns = [
        'name',
        'category',
        'city',
        'mobile',
    ];

    public function __construct()
    {
        $this->modules = [
            'title' => 'Supplier Contact',
            'folder_path' => 'software.module.supplier',
            'route' => 'supplier',
            'table_name' => (new Supplier())->getTable(),
            'permission_prefix' => 'supplier',
            'public_folder' => public_path(),
        ];
    }

    public function index(Request $request)
    {
        $modules = $this->modules;
        View::share('modules', $modules);

        try {
            $data = [];
            if ($request->ajax()) {
                $data = Supplier::select('*');

                $editPermission = Auth::user()->hasDirectPermission($modules["permission_prefix"] . '-edit');
                $deletePermission = Auth::user()->hasDirectPermission($modules["permission_prefix"] . '-delete');

                $returnData = DataTables::of($data)
                    ->addIndexColumn()
                    ->filter(function ($query) use ($request) {
                        if ($request->has('search') && !empty($request->search['value'])) {
                            $searchValue = $request->search['value'];
                            $query->where(function ($q) use ($searchValue) {
                                $q->where('name', 'like', "%{$searchValue}%")
                                  ->orWhere('category', 'like', "%{$searchValue}%")
                                  ->orWhere('city', 'like', "%{$searchValue}%")
                                  ->orWhere('mobile', 'like', "%{$searchValue}%");
                            });
                        }
                    })
                    ->addColumn('action', function ($row) use ($modules, $editPermission, $deletePermission) {
                        $btn = '<div class="d-flex gap-2 justify-content-center">';
                        if ($editPermission) {
                            $btn .= '<div class="edit"><a href="' . route($modules["route"] . ".edit", [$row["id"]]) . '" class="btn btn-info btn-icon mr-2"><i class="bx bx-edit-alt"></i></a></div>';
                        }
                        if ($deletePermission) {
                            $btn .= '<div class="remove"><a href="javascript:void(0)" data-id="' . $row->id . '" data-did="' . route($modules["route"] . ".destroy", [$row["id"]]) . '" class="btn btn-danger btn-icon deletebutton"><i class="bx bx-trash"></i></a></div>';
                        }
                        $btn .= '</div>';
                        return $btn;
                    })
                    ->rawColumns(['action'])
                    ->make(true);

                return $returnData;
            }

            $columns = [
                (object)['data' => "name", 'name' => 'name', 'td_label' => 'Name'],
                (object)['data' => "category", 'name' => 'category', 'td_label' => 'Category'],
                (object)['data' => "city", 'name' => 'city', 'td_label' => 'City'],
                (object)['data' => "mobile", 'name' => 'mobile', 'td_label' => 'Mobile'],
                (object)['data' => "action", 'name' => 'action', 'td_label' => 'Action', 'orderable' => false, 'searchable' => false, 'className' => 'w-10 text-center'],
            ];
            View::share("columns", $columns);

            $availableExportColumns = $this->exportableColumns;
            $defaultExportColumns = $this->defaultExportColumns;
            return view($modules['folder_path'] . '.index', compact('data', 'availableExportColumns', 'defaultExportColumns'));
        } catch (\Exception $e) {
            return Redirect::route('software.dashboard')->withErrors($e->getMessage());
        }
    }

    public function create()
    {
        $modules = $this->modules;
        View::share('modules', $modules);

        return view($modules['folder_path'] . '.form', compact('modules'));
    }

    public function store(Request $request)
    {
        $modules = $this->modules;
        View::share('modules', $modules);

        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'mobile' => 'nullable|digits:10',
        ]);

        DB::beginTransaction();
        try {
            Supplier::create([
                'name' => $request->name,
                'category' => $request->category,
                'city' => $request->city,
                'mobile' => $request->mobile,
                'created_by' => Auth::id(),
            ]);

            DB::commit();

            return Redirect::route($modules['route'] . '.index')->withSuccess($modules['title'] . ' created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage())->withInput();
        }
    }

    public function edit(string $id)
    {
        $modules = $this->modules;
        View::share('modules', $modules);

        try {
            $edit = Supplier::findOrFail($id);

            return view($modules['folder_path'] . '.form', compact('modules', 'edit'));
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage())->withInput();
        }
    }

    public function update(Request $request, string $id)
    {
        $modules = $this->modules;
        View::share('modules', $modules);

        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'mobile' => 'nullable|digits:10',
        ]);

        try {
            $supplier = Supplier::findOrFail($id);

            $supplier->update([
                'name' => $request->name,
                'category' => $request->category,
                'city' => $request->city,
                'mobile' => $request->mobile,
                'updated_by' => Auth::id(),
            ]);

            return Redirect::route($modules['route'] . '.index')->withSuccess($modules['title'] . ' updated successfully');
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage())->withInput();
        }
    }

    public function destroy(Request $request, string $id)
    {
        $modules = $this->modules;
        View::share('modules', $modules);

        try {
            $supplier = Supplier::findOrFail($id);
            if ($supplier) {
                $supplier->deleted_by = Auth::id();
                $supplier->save();
                if ($supplier->delete()) {
                    return response()->json([
                        'success' => true,
                        'message' => $modules['title'] . ' deleted successfully.'
                    ]);
                }
            }
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete ' . $modules['title']
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
        $this->authorizeSupplierList();

        $scope = strtolower($request->get('scope', 'filtered')) === 'all' ? 'all' : 'filtered';
        $selectedColumns = $this->resolveSelectedColumns($request->input('columns', []));
        $columnLabels = $this->mapColumnLabels($selectedColumns);

        $records = $this->buildSupplierExportQuery($request, $scope)->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        foreach (array_values($columnLabels) as $index => $label) {
            $sheet->setCellValueByColumnAndRow($index + 1, 1, $label);
        }

        $rowNumber = 2;
        foreach ($records as $record) {
            foreach ($selectedColumns as $columnIndex => $columnKey) {
                $sheet->setCellValueByColumnAndRow(
                    $columnIndex + 1,
                    $rowNumber,
                    $this->formatColumnValueForExcel($record, $columnKey)
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

        $fileName = 'supplier-' . now()->format('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function printView(Request $request)
    {
        $this->authorizeSupplierList();

        $scope = strtolower($request->get('scope', 'filtered')) === 'all' ? 'all' : 'filtered';
        $selectedColumns = $this->resolveSelectedColumns($request->input('columns', []));
        $columnLabels = $this->mapColumnLabels($selectedColumns);
        $searchTerm = $scope === 'filtered' ? trim((string) $request->get('search', '')) : null;

        $records = $this->buildSupplierExportQuery($request, $scope)->get();

        return view($this->modules['folder_path'] . '.print', [
            'records' => $records,
            'columnLabels' => $columnLabels,
            'columnKeys' => $selectedColumns,
            'scope' => $scope,
            'searchTerm' => $searchTerm,
            'generatedAt' => now(),
        ]);
    }

    protected function authorizeSupplierList(): void
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

    protected function buildSupplierExportQuery(Request $request, string $scope): Builder
    {
        $query = Supplier::query()->orderByDesc('id');

        if ($scope === 'all') {
            return $query;
        }

        $search = trim((string) $request->get('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%");
            });
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

    protected function formatColumnValueForExcel(Supplier $record, string $columnKey)
    {
        switch ($columnKey) {
            case 'name':
                return $record->name ?? '-';
            case 'category':
                return $record->category ?? '-';
            case 'city':
                return $record->city ?? '-';
            case 'mobile':
                return $record->mobile ?? '-';
            default:
                return '';
        }
    }
}
