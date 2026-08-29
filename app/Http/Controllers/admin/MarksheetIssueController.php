<?php

namespace App\Http\Controllers\admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admission;
use App\Models\StudentMarksheetIssue;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\Facades\DataTables;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Models\CourceRegistration;
use App\Models\Master\MasterCourse;
use App\Models\Master\MasterBatch;
use App\Models\Master\MasterClass;

class MarksheetIssueController extends Controller
{
    public $modules = [];
    protected array $exportableColumns = [
        'gr_no' => 'GR No.',
        'student_name' => 'Student Name',
        'semester' => 'Semester',
        'date' => 'Date',
        'series' => 'Series',
        'note' => 'Note',
    ];

    protected array $defaultExportColumns = [
        'gr_no',
        'student_name',
        'semester',
        'date',
        'series',
        'note',
    ];

    public function __construct()
    {
        $this->modules = [
            'title' => 'Marksheet Issue',
            'folder_path' => 'software.module.marksheet-issue',
            'route' => 'marksheet-issue',
            'table_name' => (new StudentMarksheetIssue())->getTable(),
            'permission_prefix' => 'marksheet-issue',
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

            if ($request->ajax()) {
                $data = StudentMarksheetIssue::with('admission')->select('student_marksheet_issues.*');

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
                    ->addColumn('student_name', function ($row) {
                        if ($row->admission) {
                            $first  = $row->admission->first_name ?? '';
                            $last   = $row->admission->last_name ?? '';
                            $father = $row->admission->father_name ?? '';
                            return trim(preg_replace('/\s+/', ' ', $first . ' ' . $last . ' ' . $father));
                        }
                        return '-';
                    })
                    ->editColumn('date', function ($row) {
                        return $row->date ? \Carbon\Carbon::parse($row->date)->format('d-m-Y') : '-';
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
                (object)['data' => 'semester', 'name' => 'semester', 'td_label' => 'Semester'],
                (object)['data' => 'date', 'name' => 'date', 'td_label' => 'Date'],
                (object)['data' => 'series', 'name' => 'series', 'td_label' => 'Series'],
                (object)['data' => 'note', 'name' => 'note', 'td_label' => 'Note'],
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
            return Redirect::route($modules['route'] . '.index')->withErrors('User does not have the right permissions.');
        }

        $students = Admission::whereIn('status', ['active', 'created'])
            ->with('courses.course')
            ->select('id', 'gr_no', 'first_name', 'last_name', 'father_name')
            ->orderBy('id', 'asc')
            ->get();

        $courses = MasterCourse::where('status', 'active')->orderBy('course_name', 'asc')->get();
        $batches = MasterBatch::where('status', 'active')->orderBy('batch_name', 'asc')->get();
        $classes = MasterClass::where('status', 'active')->orderBy('class', 'asc')->get();

        return view($modules['folder_path'] . '.form', compact('modules', 'students', 'courses', 'batches', 'classes'));
    }

    public function store(Request $request)
    {
        $modules = $this->modules;
        View::share('modules', $modules);

        $canCreate = Helper::directCan($modules['permission_prefix'] . '-create');
        if (!$canCreate) {
            return Redirect::route($modules['route'] . '.index')->withErrors('User does not have the right permissions.');
        }

        if ($request->has('issues') && is_array($request->issues)) {
            $request->validate([
                'issues' => 'required|array',
                'issues.*.admission_id' => 'required|exists:admission,id',
                'issues.*.semester' => 'required|integer',
                'issues.*.date' => 'required|date',
                'issues.*.series' => 'nullable|string|max:255',
                'issues.*.note' => 'nullable|string',
            ]);

            DB::beginTransaction();
            try {
                foreach ($request->issues as $issue) {
                    $admission = Admission::find($issue['admission_id']);
                    if ($admission) {
                        StudentMarksheetIssue::create([
                            'admission_id' => $issue['admission_id'],
                            'gr_no'        => $admission->gr_no ?? null,
                            'date'         => $issue['date'],
                            'semester'     => $issue['semester'],
                            'series'       => $issue['series'] ?? null,
                            'note'         => $issue['note'] ?? null,
                            'created_by'   => Auth::id(),
                        ]);
                    }
                }
                DB::commit();
                return Redirect::route($modules['route'] . '.index')->withSuccess($modules['title'] . ' records created successfully.');
            } catch (\Exception $e) {
                DB::rollBack();
                return Redirect::back()->withErrors($e->getMessage())->withInput();
            }
        }

        $request->validate([
            'admission_id' => 'required',
            'semester'     => 'required|integer',
            'date'         => 'required|date',
            'gr_no'        => 'nullable|string|max:255',
            'series'       => 'nullable|string|max:255',
            'note'         => 'nullable|string',
        ]);

        $admissionIds = is_array($request->admission_id) ? $request->admission_id : [$request->admission_id];

        DB::beginTransaction();
        try {
            foreach ($admissionIds as $id) {
                $admission = Admission::find($id);
                if ($admission) {
                    StudentMarksheetIssue::create([
                        'admission_id' => $id,
                        'gr_no'        => (count($admissionIds) > 1) ? ($admission->gr_no ?? null) : ($request->gr_no ?: ($admission->gr_no ?? null)),
                        'date'         => $request->date,
                        'semester'     => $request->semester,
                        'series'       => $request->series,
                        'note'         => $request->note,
                        'created_by'   => Auth::id(),
                    ]);
                }
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
            $edit = StudentMarksheetIssue::findOrFail($id);

            $students = Admission::whereIn('status', ['active', 'created'])
                ->with('courses.course')
                ->select('id', 'gr_no', 'first_name', 'last_name', 'father_name')
                ->orderBy('id', 'asc')
                ->get();

            $courses = MasterCourse::where('status', 'active')->orderBy('course_name', 'asc')->get();
            $batches = MasterBatch::where('status', 'active')->orderBy('batch_name', 'asc')->get();
            $classes = MasterClass::where('status', 'active')->orderBy('class', 'asc')->get();

            return view($modules['folder_path'] . '.form', compact('modules', 'edit', 'students', 'courses', 'batches', 'classes'));
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
            return Redirect::route($modules['route'] . '.index')->withErrors('User does not have the right permissions.');
        }

        $request->validate([
            'admission_id' => 'required|exists:admission,id',
            'semester'     => 'required|integer',
            'date'         => 'required|date',
            'gr_no'        => 'nullable|string|max:255',
            'series'       => 'nullable|string|max:255',
            'note'         => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $record = StudentMarksheetIssue::findOrFail($id);
            $admission = Admission::find($request->admission_id);

            $record->update([
                'admission_id' => $request->admission_id,
                'gr_no'        => $request->gr_no ?: ($admission ? $admission->gr_no : null),
                'date'         => $request->date,
                'semester'     => $request->semester,
                'series'       => $request->series,
                'note'         => $request->note,
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
            $record = StudentMarksheetIssue::findOrFail($id);
            $record->delete();
            return response()->json(['success' => true, 'message' => $modules['title'] . ' deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function toggle(Request $request)
    {
        $canEdit = Helper::directCan($this->modules['permission_prefix'] . '-edit');
        if (!$canEdit) {
            return response()->json(['success' => false, 'message' => 'User does not have the right permissions.']);
        }

        $admissionId = $request->input('admission_id');
        $semester = $request->input('semester');
        $isIssued = $request->input('is_issued');

        if ($isIssued) {
            StudentMarksheetIssue::firstOrCreate([
                'admission_id' => $admissionId,
                'semester'     => $semester,
            ], [
                'created_by'   => \Illuminate\Support\Facades\Auth::user()->id ?? null,
            ]);
        } else {
            StudentMarksheetIssue::where('admission_id', $admissionId)
                ->where('semester', $semester)
                ->delete();
        }

        return response()->json(['success' => true]);
    }

    public function exportExcel(Request $request)
    {
        $this->authorizeMarksheetList();

        $scope = strtolower($request->get('scope', 'filtered')) === 'all' ? 'all' : 'filtered';
        $selectedColumns = $this->resolveSelectedColumns($request->input('columns', []));
        $columnLabels = $this->mapColumnLabels($selectedColumns);

        $records = $this->buildMarksheetExportQuery($request, $scope)->get();

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

        $fileName = 'marksheet-issue-' . now()->format('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function printView(Request $request)
    {
        $this->authorizeMarksheetList();

        $scope = strtolower($request->get('scope', 'filtered')) === 'all' ? 'all' : 'filtered';
        $selectedColumns = $this->resolveSelectedColumns($request->input('columns', []));
        $columnLabels = $this->mapColumnLabels($selectedColumns);
        $searchTerm = $scope === 'filtered' ? trim((string) $request->get('search', '')) : null;

        $records = $this->buildMarksheetExportQuery($request, $scope)->get();

        return view($this->modules['folder_path'] . '.print', [
            'records' => $records,
            'columnLabels' => $columnLabels,
            'columnKeys' => $selectedColumns,
            'scope' => $scope,
            'searchTerm' => $searchTerm,
            'generatedAt' => now(),
        ]);
    }

    protected function authorizeMarksheetList(): void
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

    protected function formatColumnValueForExcel(StudentMarksheetIssue $record, string $columnKey)
    {
        switch ($columnKey) {
            case 'gr_no':
                return $record->gr_no ?? '-';
            case 'student_name':
                if ($record->admission) {
                    $first  = $record->admission->first_name ?? '';
                    $last   = $record->admission->last_name ?? '';
                    $father = $record->admission->father_name ?? '';
                    return trim(preg_replace('/\s+/', ' ', $first . ' ' . $last . ' ' . $father));
                }
                return '-';
            case 'semester':
                return $record->semester ?? '-';
            case 'date':
                return $record->date ? \Carbon\Carbon::parse($record->date)->format('d-m-Y') : '-';
            case 'series':
                return $record->series ?? '-';
            case 'note':
                return $record->note ?? '-';
            default:
                return '';
        }
    }

    protected function buildMarksheetExportQuery(Request $request, string $scope): Builder
    {
        $query = StudentMarksheetIssue::with('admission')->orderByDesc('id');

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

    public function getStudents(Request $request)
    {
        $course_id = $request->input('course_id');
        $batch_id = $request->input('batch_id');
        $class_id = $request->input('class_id');

        $query = CourceRegistration::query();

        if ($course_id) {
            $query->where('course_id', $course_id);
        }
        if ($batch_id) {
            $query->where('batch_id', $batch_id);
        }
        if ($class_id) {
            if (is_array($class_id)) {
                $query->whereIn('class_id', $class_id);
            } else {
                $query->where('class_id', $class_id);
            }
        }

        $studentIds = $query->pluck('register_id');

        $students = Admission::whereIn('id', $studentIds)
            ->whereIn('status', ['active', 'created'])
            ->select('id', 'gr_no', 'first_name', 'last_name', 'father_name')
            ->orderBy('id', 'asc')
            ->get();

        $students = $students->map(function ($student) {
            $courseReg = CourceRegistration::where('register_id', $student->id)
                ->with('course')
                ->first();
            $student->semester_count = $courseReg && $courseReg->course ? $courseReg->course->semester : 0;
            $student->full_name = trim(($student->father_name ?? '') . ' ' . ($student->first_name ?? '') . ' ' . ($student->last_name ?? ''));
            return $student;
        });

        return response()->json([
            'success' => true,
            'data' => $students
        ]);
    }
}
