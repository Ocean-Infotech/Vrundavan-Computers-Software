<?php

namespace App\Http\Controllers\admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admission;
use App\Models\Assessment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\Facades\DataTables;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Models\Master\MasterCourse;
use App\Models\Master\MasterBatch;
use App\Models\Master\MasterClass;

class AssessmentController extends Controller
{
    public $modules = [];
    protected array $exportableColumns = [
        'gr_no' => 'GR No.',
        'student_name' => 'Student Name',
        'subject' => 'Subject',
        'performance' => 'Performance',
        'remarks' => 'Remarks',
    ];

    protected array $defaultExportColumns = [
        'gr_no',
        'student_name',
        'subject',
        'performance',
        'remarks',
    ];

    public function __construct()
    {
        $this->modules = [
            'title' => 'Assessment',
            'folder_path' => 'software.module.assessment',
            'route' => 'assessment',
            'table_name' => (new Assessment())->getTable(),
            'permission_prefix' => 'assessment',
        ];
    }

    public function index(Request $request)
    {
        $modules = $this->modules;
        View::share('modules', $modules);

        try {
            $canList = Helper::directCan($modules['permission_prefix'] . '-list');
            if (!$canList) {
                abort(403, 'User does not have the right permissions.');
            }

            if ($request->ajax()) {
                $data = Assessment::with('admission')->select('assessments.*');

                $editPermission = Auth::user()->hasDirectPermission($modules["permission_prefix"] . '-edit');
                $deletePermission = Auth::user()->hasDirectPermission($modules["permission_prefix"] . '-delete');

                return DataTables::of($data)
                    ->addIndexColumn()
                    ->filter(function ($query) use ($request) {
                        if ($request->has('search') && !empty($request->search['value'])) {
                            $searchValue = $request->search['value'];
                            $query->where(function ($q) use ($searchValue) {
                                $q->where('admission_id', $searchValue)
                                  ->orWhereHas('admission', function ($sub) use ($searchValue) {
                                      $sub->where('first_name', 'like', "%{$searchValue}%")
                                          ->orWhere('last_name', 'like', "%{$searchValue}%")
                                          ->orWhere('father_name', 'like', "%{$searchValue}%");
                                  });
                            });
                        }
                    })
                    ->addColumn('gr_no', function ($row) {
                        return $row->admission->gr_no ?? '-';
                    })
                    ->addColumn('student_name', function ($row) {
                        if ($row->admission) {
                            $first  = $row->admission->first_name ?? '';
                            $last   = $row->admission->last_name ?? '';
                            $father = $row->admission->father_name ?? '';
                            return trim(preg_replace('/\s+/', ' ', $first . ' ' . $last . ' ' . $father));
                        }
                        return '-';
                    })
                    ->editColumn('performance', function ($row) {
                        return ucfirst($row->performance);
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
            }

            $columns = [
                (object)['data' => 'gr_no', 'name' => 'gr_no', 'td_label' => 'GR No.'],
                (object)['data' => 'student_name', 'name' => 'student_name', 'td_label' => 'Student Name', 'orderable' => false],
                (object)['data' => 'subject', 'name' => 'subject', 'td_label' => 'Subject'],
                (object)['data' => 'performance', 'name' => 'performance', 'td_label' => 'Performance'],
                (object)['data' => 'remarks', 'name' => 'remarks', 'td_label' => 'Remarks'],
                (object)['data' => 'action', 'name' => 'action', 'td_label' => 'Action', 'orderable' => false, 'searchable' => false, 'className' => 'w-10 text-center'],
            ];
            View::share('columns', $columns);

            $availableExportColumns = $this->exportableColumns;
            $defaultExportColumns = $this->defaultExportColumns;

            return view($modules['folder_path'] . '.index', compact('modules', 'availableExportColumns', 'defaultExportColumns'));
        } catch (\Exception $e) {
            return Redirect::route('software.dashboard')->withErrors($e->getMessage());
        }
    }

    public function create()
    {
        $modules = $this->modules;
        View::share('modules', $modules);

        $canCreate = Helper::directCan($modules['permission_prefix'] . '-create');
        if (!$canCreate) {
            abort(403, 'User does not have the right permissions.');
        }

        $courses = MasterCourse::pluck('course_name', 'id');
        $batches = [];
        $classes = [];
        $students = [];
        $selectedStudents = [];

        return view($modules['folder_path'] . '.form', compact('modules', 'courses', 'batches', 'classes', 'students', 'selectedStudents'));
    }

    public function store(Request $request)
    {
        $modules = $this->modules;
        View::share('modules', $modules);

        $canCreate = Helper::directCan($modules['permission_prefix'] . '-create');
        if (!$canCreate) {
            abort(403, 'User does not have the right permissions.');
        }

        // Check if it is a bulk submission
        if ($request->has('assessments') && is_array($request->assessments)) {
            $request->validate([
                'assessments' => 'required|array',
                'assessments.*.admission_id' => 'required|exists:admission,id',
                'assessments.*.subject' => 'required|string|max:255',
                'assessments.*.performance' => 'required|in:best,good,average,poor',
                'assessments.*.remarks' => 'nullable|string',
            ]);

            DB::beginTransaction();
            try {
                foreach ($request->assessments as $assess) {
                    Assessment::create([
                        'admission_id' => $assess['admission_id'],
                        'subject'      => $assess['subject'],
                        'performance'  => $assess['performance'],
                        'remarks'      => $assess['remarks'] ?? null,
                        'created_by'   => Auth::id(),
                    ]);
                }
                DB::commit();
                return Redirect::route($modules['route'] . '.index')->withSuccess($modules['title'] . ' records created successfully.');
            } catch (\Exception $e) {
                DB::rollBack();
                return Redirect::back()->withErrors($e->getMessage())->withInput();
            }
        }

        $request->validate([
            'admission_id' => 'required|exists:admission,id',
            'subject'      => 'required|string|max:255',
            'performance'  => 'required|in:best,good,average,poor',
            'remarks'      => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            Assessment::create([
                'admission_id' => $request->admission_id,
                'subject'      => $request->subject,
                'performance'  => $request->performance,
                'remarks'      => $request->remarks,
                'created_by'   => Auth::id(),
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

        $canEdit = Helper::directCan($modules['permission_prefix'] . '-edit');
        if (!$canEdit) {
            abort(403, 'User does not have the right permissions.');
        }

        try {
            $edit = Assessment::findOrFail($id);

            $students = Admission::whereIn('status', ['active', 'created'])
                ->select('id', 'gr_no', 'first_name', 'last_name', 'father_name')
                ->orderBy('id', 'asc')
                ->get();

            return view($modules['folder_path'] . '.form', compact('modules', 'edit', 'students'));
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }

    public function update(Request $request, string $id)
    {
        $modules = $this->modules;
        View::share('modules', $modules);

        $canEdit = Helper::directCan($modules['permission_prefix'] . '-edit');
        if (!$canEdit) {
            abort(403, 'User does not have the right permissions.');
        }

        $request->validate([
            'admission_id' => 'required|exists:admission,id',
            'subject'      => 'required|string|max:255',
            'performance'  => 'required|in:best,good,average,poor',
            'remarks'      => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $record = Assessment::findOrFail($id);

            $record->update([
                'admission_id' => $request->admission_id,
                'subject'      => $request->subject,
                'performance'  => $request->performance,
                'remarks'      => $request->remarks,
                'updated_by'   => Auth::id(),
            ]);

            DB::commit();

            return Redirect::route($modules['route'] . '.index')->withSuccess($modules['title'] . ' updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage())->withInput();
        }
    }

    public function destroy(Request $request, string $id)
    {
        $modules = $this->modules;

        $canDelete = Helper::directCan($modules['permission_prefix'] . '-delete');
        if (!$canDelete) {
            return response()->json(['success' => false, 'message' => 'User does not have the right permissions.']);
        }

        try {
            $record = Assessment::findOrFail($id);
            $record->delete();
            return response()->json(['success' => true, 'message' => $modules['title'] . ' deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function exportExcel(Request $request)
    {
        $this->authorizeAssessmentList();

        $scope = strtolower($request->get('scope', 'filtered')) === 'all' ? 'all' : 'filtered';
        $selectedColumns = $this->resolveSelectedColumns($request->input('columns', []));
        $columnLabels = $this->mapColumnLabels($selectedColumns);

        $records = $this->buildAssessmentExportQuery($request, $scope)->get();

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

        $fileName = 'assessment-' . now()->format('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function printView(Request $request)
    {
        $this->authorizeAssessmentList();

        $scope = strtolower($request->get('scope', 'filtered')) === 'all' ? 'all' : 'filtered';
        $selectedColumns = $this->resolveSelectedColumns($request->input('columns', []));
        $columnLabels = $this->mapColumnLabels($selectedColumns);
        $searchTerm = $scope === 'filtered' ? trim((string) $request->get('search', '')) : null;

        $records = $this->buildAssessmentExportQuery($request, $scope)->get();

        return view($this->modules['folder_path'] . '.print', [
            'records' => $records,
            'columnLabels' => $columnLabels,
            'columnKeys' => $selectedColumns,
            'scope' => $scope,
            'searchTerm' => $searchTerm,
            'generatedAt' => now(),
        ]);
    }

    protected function authorizeAssessmentList(): void
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

    protected function mapColumnLabels(array $selectedColumns): array
    {
        $labels = [];
        foreach ($selectedColumns as $columnKey) {
            $labels[$columnKey] = $this->exportableColumns[$columnKey] ?? ucfirst(str_replace('_', ' ', $columnKey));
        }
        return $labels;
    }

    protected function formatColumnValueForExcel(Assessment $record, string $columnKey)
    {
        switch ($columnKey) {
            case 'gr_no':
                return $record->admission->gr_no ?? '-';
            case 'student_name':
                if ($record->admission) {
                    $first  = $record->admission->first_name ?? '';
                    $last   = $record->admission->last_name ?? '';
                    $father = $record->admission->father_name ?? '';
                    return trim(preg_replace('/\s+/', ' ', $first . ' ' . $last . ' ' . $father));
                }
                return '-';
            case 'subject':
                return $record->subject ?? '-';
            case 'performance':
                return ucfirst($record->performance) ?? '-';
            case 'remarks':
                return $record->remarks ?? '-';
            default:
                return '';
        }
    }

    protected function buildAssessmentExportQuery(Request $request, string $scope): Builder
    {
        $query = Assessment::with('admission')->orderByDesc('id');

        if ($scope === 'all') {
            return $query;
        }

        $search = trim((string) $request->get('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('admission_id', $search)
                  ->orWhereHas('admission', function ($sub) use ($search) {
                      $sub->where('first_name', 'like', "%{$search}%")
                          ->orWhere('last_name', 'like', "%{$search}%")
                          ->orWhere('father_name', 'like', "%{$search}%");
                  });
            });
        }

        return $query;
    }
}
