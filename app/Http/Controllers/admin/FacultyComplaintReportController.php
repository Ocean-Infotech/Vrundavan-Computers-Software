<?php

namespace App\Http\Controllers\admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Admission;
use App\Models\FacultyComplaintReport;
use App\Models\User;
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

class FacultyComplaintReportController extends Controller
{
    public $modules = [];
    protected array $exportableColumns = [
        'gr_no' => 'GR No.',
        'student_name' => 'Student Name',
        'date' => 'Date',
        'faculty_name' => 'Faculty Name',
        'complaint' => 'Complaint',
    ];

    protected array $defaultExportColumns = [
        'gr_no',
        'student_name',
        'date',
        'faculty_name',
        'complaint',
    ];

    public function __construct()
    {
        $this->modules = [
            'title' => 'Faculty Complaint Report',
            'folder_path' => 'software.module.faculty-complaint-report',
            'route' => 'faculty-complaint-report',
            'table_name' => (new FacultyComplaintReport())->getTable(),
            'permission_prefix' => 'faculty-complaint-report',
            'public_folder' => public_path(),
        ];
    }

    public function index(Request $request)
    {
        $modules = $this->modules;
        View::share('modules', $modules);

        try {
            $canList = Helper::directCan($modules['permission_prefix'] . '-list');
            if (!$canList) {
                return Redirect::route('software.dashboard')->withErrors('User does not have the right permissions.');
            }

            $data = [];
            if ($request->ajax()) {
                $data = FacultyComplaintReport::with(['admission', 'faculty'])
                    ->select('faculty_complaint_report.*')
                    ->orderBy('faculty_complaint_report.id', 'desc');

                $editPermission = Auth::user()->hasDirectPermission($modules["permission_prefix"] . '-edit');
                $deletePermission = Auth::user()->hasDirectPermission($modules["permission_prefix"] . '-delete');

                $returnData = DataTables::of($data)
                    ->addIndexColumn()
                    ->filter(function ($query) use ($request) {
                        if ($request->has('search') && !empty($request->search['value'])) {
                            $searchValue = $request->search['value'];
                            $query->where(function ($q) use ($searchValue) {
                                if (is_numeric($searchValue) && \App\Models\Admission::where('id', $searchValue)->exists()) {
                                    $q->where('admission_id', $searchValue);
                                } else {
                                    $q->whereHas('admission', function ($sq) use ($searchValue) {
                                        $sq->where('first_name', 'like', "%{$searchValue}%")
                                           ->orWhere('last_name', 'like', "%{$searchValue}%")
                                           ->orWhere('father_name', 'like', "%{$searchValue}%")
                                           ->orWhere('gr_no', 'like', "%{$searchValue}%");
                                    });
                                }
                            });
                        }
                    })
                    ->editColumn('date', function ($record) {
                        return $record->date ? Helper::convert_date($record->date, 'Y-m-d', 'd-m-Y') : '-';
                    })
                    ->addColumn('student_name', function ($record) {
                        if ($record->admission) {
                            $first = $record->admission->first_name ?? '';
                            $last = $record->admission->last_name ?? '';
                            $father = $record->admission->father_name ?? '';
                            return trim($first . ' ' . $last . ' ' . $father);
                        }
                        return '-';
                    })
                    ->addColumn('faculty_name', function ($record) {
                        return $record->faculty ? $record->faculty->name : '-';
                    })
                    ->addColumn('action', function ($row) use ($modules, $editPermission, $deletePermission) {
                        $btn = '<div class="d-flex gap-2 justify-content-center">';
                        
                        if (!$row?->deleted_at) {
                            if ($editPermission) {
                                $btn .= '<div class="edit"><a href="' . route($modules["route"] . ".edit", [$row["id"]]) . '" class="btn btn-info btn-icon"><i class="bx bx-edit-alt"></i></a></div>';
                            }
                            if ($deletePermission) {
                                $btn .= '<div class="remove"><a href="javascript:void(0)" data-id="' . $row->id . '" data-did="' . route($modules["route"] . ".destroy", [$row["id"]]) . '" class="btn btn-danger btn-icon deletebutton"><i class="bx bx-trash"></i></a></div>';
                            }
                            // 🔹 Permanent Delete Button
                            $btn .= '<a href="javascript:void(0)"
                                        data-id="' . $row->id . '"
                                        data-did="' . route($modules["route"] . ".permanent-delete", [$row["id"]]) . '"
                                        class="btn btn-dark btn-icon permanentDeleteButton"
                                        title="Permanent Delete">
                                        <i class="fa-solid fa-trash-arrow-up"></i>
                                    </a>';
                        } else {
                            $btn .= '<a href="javascript:void(0)"
                                        data-restore="' . route($modules["route"] . ".restore", ['id' => $row["id"]]) . '"
                                        class="btn btn-light record-restore"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        data-bs-original-title="Restore Data">
                                        <i class="ti ti-history"></i> Restore
                                    </a>';
                        }
                        
                        $btn .= '</div>';
                        return $btn;
                    })
                    ->rawColumns(['action'])
                    ->make(true);

                return $returnData;
            }

            $columns = [
                (object)['data' => "gr_no", 'name' => 'gr_no', 'td_label' => 'GR No.'],
                (object)['data' => "student_name", 'name' => 'student_name', 'td_label' => 'Student Name', 'orderable' => false],
                (object)['data' => "date", 'name' => 'date', 'td_label' => 'Date'],
                (object)['data' => "faculty_name", 'name' => 'faculty_name', 'td_label' => 'Faculty', 'orderable' => false],
                (object)['data' => "complaint", 'name' => 'complaint', 'td_label' => 'Complaint'],
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

        $canCreate = Helper::directCan($modules['permission_prefix'] . '-create');
        if (!$canCreate) {
            return Redirect::route($modules['route'] . '.index')->withErrors('User does not have the right permissions.');
        }

        $students = Admission::whereIn('status', ['active', 'created'])
            ->whereHas('courses', function ($q) {
                $q->whereNotIn('status', ['Cancel', 'cancel']);
            })
            ->select('id', 'gr_no', 'first_name', 'last_name', 'father_name')
            ->orderBy('id', 'asc')
            ->get();

        $faculties = User::where('status', 'active')
            ->whereHas('roles', function ($q) {
                $q->where(DB::raw('LOWER(name)'), 'like', '%faculty%');
            })->where('name', 'not like', '%Ocean%')->get();

        if ($faculties->isEmpty()) {
            $faculties = User::where('status', 'active')->where('name', 'not like', '%Ocean%')->get();
        }

        $courses = \App\Models\Master\MasterCourse::where('status', 'active')->get();

        return view($modules['folder_path'] . '.form', compact('modules', 'students', 'faculties', 'courses'));
    }

    public function store(Request $request)
    {
        $modules = $this->modules;
        View::share('modules', $modules);

        $canCreate = Helper::directCan($modules['permission_prefix'] . '-create');
        if (!$canCreate) {
            return Redirect::route($modules['route'] . '.index')->withErrors('User does not have the right permissions.');
        }

        $request->validate([
            'admission_id' => 'required|array',
            'admission_id.*' => 'required|exists:admission,id',
            'date' => 'required|date',
            'complaint' => 'required|string',
            'faculty_id' => 'required|exists:users,id',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->admission_id as $studentId) {
                $admission = Admission::find($studentId);

                FacultyComplaintReport::create([
                    'admission_id' => $studentId,
                    'gr_no' => $admission ? $admission->gr_no : null,
                    'date' => $request->date,
                    'complaint' => $request->complaint,
                    'faculty_id' => $request->faculty_id,
                ]);
            }

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
            return Redirect::route($modules['route'] . '.index')->withErrors('User does not have the right permissions.');
        }

        try {
            $edit = FacultyComplaintReport::findOrFail($id);

            $students = Admission::whereIn('status', ['active', 'created'])
                ->whereHas('courses', function ($q) {
                    $q->whereNotIn('status', ['Cancel', 'cancel']);
                })
                ->select('id', 'gr_no', 'first_name', 'last_name', 'father_name')
                ->orderBy('id', 'asc')
                ->get();

            $faculties = User::where('status', 'active')
                ->whereHas('roles', function ($q) {
                    $q->where(DB::raw('LOWER(name)'), 'like', '%faculty%');
                })->where('name', 'not like', '%Ocean%')->get();

            if ($faculties->isEmpty()) {
                $faculties = User::where('status', 'active')->where('name', 'not like', '%Ocean%')->get();
            }

            $courses = \App\Models\Master\MasterCourse::where('status', 'active')->get();

            return view($modules['folder_path'] . '.form', compact('modules', 'edit', 'students', 'faculties', 'courses'));
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage())->withInput();
        }
    }

    public function update(Request $request, string $id)
    {
        $modules = $this->modules;
        View::share('modules', $modules);

        $canEdit = Helper::directCan($modules['permission_prefix'] . '-edit');
        if (!$canEdit) {
            return Redirect::route($modules['route'] . '.index')->withErrors('User does not have the right permissions.');
        }

        $request->validate([
            'admission_id' => 'required|exists:admission,id',
            'date' => 'required|date',
            'complaint' => 'required|string',
            'faculty_id' => 'required|exists:users,id',
        ]);

        try {
            $report = FacultyComplaintReport::findOrFail($id);
            $admission = Admission::find($request->admission_id);

            $report->update([
                'admission_id' => $request->admission_id,
                'gr_no' => $admission ? $admission->gr_no : $request->gr_no,
                'date' => $request->date,
                'complaint' => $request->complaint,
                'faculty_id' => $request->faculty_id,
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

        $canDelete = Helper::directCan($modules['permission_prefix'] . '-delete');
        if (!$canDelete) {
            return response()->json(['success' => false, 'message' => 'User does not have the right permissions.']);
        }

        try {
            $report = FacultyComplaintReport::findOrFail($id);
            if ($report) {
                if ($report->delete()) {
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

    public function restore($id)
    {
        $canDelete = Helper::directCan($this->modules['permission_prefix'] . '-delete');
        if (!$canDelete) {
            return response()->json(['success' => false, 'message' => 'User does not have the right permissions.']);
        }

        try {
            $report = FacultyComplaintReport::withTrashed()->findOrFail($id);
            $report->restore();

            return response()->json([
                'success' => true,
                'message' => 'Faculty Complaint Report restored successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function permanentDelete($id)
    {
        $canDelete = Helper::directCan($this->modules['permission_prefix'] . '-delete');
        if (!$canDelete) {
            return response()->json(['success' => false, 'message' => 'User does not have the right permissions.']);
        }

        try {
            $report = FacultyComplaintReport::withTrashed()->findOrFail($id);
            $report->forceDelete();

            return response()->json([
                'success' => true,
                'message' => 'Faculty Complaint Report permanently deleted successfully.'
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
        $this->authorizeFacultyComplaintReportList();

        $scope = strtolower($request->get('scope', 'filtered')) === 'all' ? 'all' : 'filtered';
        $selectedColumns = $this->resolveSelectedColumns($request->input('columns', []));
        $columnLabels = $this->mapColumnLabels($selectedColumns);

        $records = $this->buildFacultyComplaintReportExportQuery($request, $scope)->get();

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

        $fileName = 'faculty-complaint-report-' . now()->format('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function printView(Request $request)
    {
        $this->authorizeFacultyComplaintReportList();

        $scope = strtolower($request->get('scope', 'filtered')) === 'all' ? 'all' : 'filtered';
        $selectedColumns = $this->resolveSelectedColumns($request->input('columns', []));
        $columnLabels = $this->mapColumnLabels($selectedColumns);
        $searchTerm = $scope === 'filtered' ? trim((string) $request->get('search', '')) : null;

        $records = $this->buildFacultyComplaintReportExportQuery($request, $scope)->get();

        return view($this->modules['folder_path'] . '.print', [
            'records' => $records,
            'columnLabels' => $columnLabels,
            'columnKeys' => $selectedColumns,
            'scope' => $scope,
            'searchTerm' => $searchTerm,
            'generatedAt' => now(),
        ]);
    }

    protected function authorizeFacultyComplaintReportList(): void
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

    protected function buildFacultyComplaintReportExportQuery(Request $request, string $scope): Builder
    {
        $query = FacultyComplaintReport::with(['admission', 'faculty'])->select('faculty_complaint_report.*');

        if ($scope === 'all') {
            return $query;
        }

        $search = trim((string) $request->get('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                if (is_numeric($search) && \App\Models\Admission::where('id', $search)->exists()) {
                    $q->where('admission_id', $search);
                } else {
                    $q->whereHas('admission', function ($sq) use ($search) {
                        $sq->where('first_name', 'like', "%{$search}%")
                           ->orWhere('last_name', 'like', "%{$search}%")
                           ->orWhere('father_name', 'like', "%{$search}%")
                           ->orWhere('gr_no', 'like', "%{$search}%");
                    });
                }
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

    protected function formatColumnValueForExcel(FacultyComplaintReport $record, string $columnKey)
    {
        switch ($columnKey) {
            case 'gr_no':
                return $record->gr_no ?? '-';
            case 'student_name':
                if ($record->admission) {
                    $first = $record->admission->first_name ?? '';
                    $last = $record->admission->last_name ?? '';
                    $father = $record->admission->father_name ?? '';
                    return trim($first . ' ' . $last . ' ' . $father);
                }
                return '-';
            case 'date':
                return $record->date ? Helper::convert_date($record->date, 'Y-m-d', 'd-m-Y') : '-';
            case 'faculty_name':
                return $record->faculty ? $record->faculty->name : '-';
            case 'complaint':
                return $record->complaint ?? '-';
            default:
                return '';
        }
    }
}
