<?php

namespace App\Http\Controllers\admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Test;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class TestreportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public $modules = [];
    protected array $exportableColumns = [
        'subject_name' => 'Subject',
        'unit_name'    => 'Unit',
        'cr_date'      => 'Date',
        'mark'         => 'Total Mark',
        'student_mark' => 'Obtained Mark',
    ];

    protected array $defaultExportColumns = [
        'subject_name',
        'unit_name',
        'cr_date',
        'mark',
        'student_mark',
    ];
    public function __construct()
    {
        $this->modules = [
            'title' => 'Test',
            'folder_path' => 'software.module.test_report',
            'route' => 'test_report',
            'table_name' => (new Test())->getTable(),
            'permission_prefix' => 'test_report',
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

        if (!$modules['permission_list']) {
            if (isset($request) && $request->ajax()) {
                return $this->sendError('User does not have the right permissions.', [], [], 403);
            }
            abort(403, 'User does not have the right permissions.');
        }
        View::share('modules', $modules);

        // Columns for datatable
        $columns = [
            // (object)['data' => "student_name", 'name' => 'admission.first_name', 'td_label' => 'Student Name'],
            // (object)['data' => "batch_name", 'name' => 'master_batch.batch_name', 'td_label' => 'Batch'],
            // (object)['data' => "course_name", 'name' => 'master_course.course_name', 'td_label' => 'Course Name'],
            (object)['data' => "subject_name", 'name' => 'subject_name', 'td_label' => 'Subject'],
            (object)['data' => "unit_name", 'name' => 'unit_name', 'td_label' => 'Unit'],
            (object)['data' => "cr_date", 'name' => 'cr_date', 'td_label' => 'Date'],
            (object)['data' => "mark", 'name' => 'mark', 'td_label' => 'Total Mark'],
            (object)['data' => "input", 'name' => 'student_mark', 'td_label' => 'Marks'],
        ];
        View::share("columns", $columns);

        $subjects = DB::table('test')
            ->whereNull('deleted_at')
            ->distinct()
            ->orderBy('subject_name', 'asc')
            ->pluck('subject_name');

        View::share('subjects', $subjects);

     if ($request->ajax()) {

        /** -------------------------------------------
         * 🟢 FETCH STUDENT DETAILS FOR TOP SECTION
         --------------------------------------------*/
        $studentSearch = trim($request->student_search ?? '');
        $student_details = null;
        $currentSemester = null;
        $startDateStr = null;

        if (!empty($studentSearch)) {
            $student_details = DB::table('cource_registration')
                ->leftJoin('admission', 'cource_registration.register_id', '=', 'admission.id')
                ->leftJoin('master_course', 'cource_registration.course_id', '=', 'master_course.id')
                ->select(
                    'admission.id as admission_id',
                    'cource_registration.id as student_id',
                    DB::raw("CONCAT(admission.first_name,' ',admission.last_name,' ',admission.father_name) as student_name"),
                    'master_course.course_name',
                    'cource_registration.register_id',
                    'cource_registration.course_id',
                    'cource_registration.is_lateral_entry',
                    'cource_registration.joining_semester'
                )
                ->where(function ($q) use ($studentSearch) {
                    if (is_numeric($studentSearch)) {
                        $q->where('cource_registration.id', $studentSearch)
                          ->orWhere('admission.id', $studentSearch);
                    } else {
                        $q->where(DB::raw("CONCAT(admission.first_name,' ',admission.last_name,' ',admission.father_name)"),
                            'like', "%{$studentSearch}%");
                    }
                })
                ->first();

            if ($student_details) {
                // Determine current semester
                $currentSemester = 1;
                if ($student_details->is_lateral_entry == 1 && $student_details->joining_semester) {
                    $currentSemester = (int)$student_details->joining_semester;
                }
                $maxPaidSem = DB::table('fees_collections')
                    ->where('student_id', $student_details->register_id)
                    ->where('course_id', $student_details->course_id)
                    ->max('year_semester');
                if ($maxPaidSem && $maxPaidSem > $currentSemester) {
                    $currentSemester = (int)$maxPaidSem;
                }

                // Determine June start date of current academic cycle
                $now = \Carbon\Carbon::now();
                $startOfAttendance = \Carbon\Carbon::create($now->year, 6, 1)->startOfDay();
                if ($now->month < 6) {
                    $startOfAttendance->subYear();
                }
                $startDateStr = $startOfAttendance->format('Y-m-d');
            }
        }


        /** -------------------------------------------
         * 🟢 MAIN QUERY FOR DATATABLE
         * --------------------------------------------*/
        $query = $this->buildTestReportBaseQuery();

        return DataTables::of($query)
            ->addIndexColumn()
            ->filter(function ($query) use ($request, $studentSearch, $student_details, $currentSemester, $startDateStr) {

                $searchValue = $request->search['value'] ?? null;

                if (!empty($studentSearch) && $student_details) {
                    $query->where(function ($q) use ($studentSearch) {
                        if (is_numeric($studentSearch)) {
                            $q->where('cource_registration.id', $studentSearch)
                              ->orWhere('admission.id', $studentSearch);
                        } else {
                            $q->where(DB::raw("CONCAT(admission.first_name, ' ', admission.last_name, ' ', admission.father_name)"),
                                'like', "%{$studentSearch}%");
                        }
                    });

                    // Only show tests matching current semester and current cycle (June onwards)
                    $query->where('test.semester', $currentSemester);
                    $query->where('test.date', '>=', $startDateStr);
                }

                if (!empty($searchValue)) {
                    $query->where(function ($q) use ($searchValue) {
                        $q->where(DB::raw("CONCAT(admission.first_name, ' ', admission.last_name)"), 'like', "%{$searchValue}%")
                          ->orWhere('test.subject_name', 'like', "%{$searchValue}%")
                          ->orWhere('test.mark', 'like', "%{$searchValue}%")
                          ->orWhere('student_test_marks.marks', 'like', "%{$searchValue}%");
                    });
                }
            })

            ->editColumn('cr_date', function ($row) {
                return $row->cr_date ? date('d-m-Y', strtotime($row->cr_date)) : '-';
            })

            ->addColumn('input', function ($row) {
                $value = htmlspecialchars($row->student_mark ?? 0, ENT_QUOTES, 'UTF-8');
                return '<span>' . ($value ?: 0) . '</span>';
            })

            ->rawColumns(['input'])

            /** 🟢 SEND STUDENT DETAILS TO FRONTEND */
            ->with('student_details', $student_details)

            ->make(true);
    }

    $availableExportColumns = $this->exportableColumns;
    $defaultExportColumns = $this->defaultExportColumns;

    return view($modules['folder_path'] . '.index', compact('availableExportColumns', 'defaultExportColumns'));
}

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $modules = $this->modules;
        $modules['login_user_role'] = Helper::getLoginUserRole();
        $modules['permission_add'] = Helper::directCan($modules['permission_prefix'] . '-create');
        if (!$modules['permission_add']) {
            if (isset($request) && $request->ajax()) {
                return $this->sendError('User does not have the right permissions.', [], [], 403);
            }
            abort(403, 'User does not have the right permissions.');
        }

        $request->validate([
            'test_id' => 'required|integer|exists:test,id',
            'register_id' => 'required|integer|exists:cource_registration,register_id',
            'marks' => 'required|numeric|min:0',
        ]);

        $test = DB::table('test')->where('id', $request->test_id)->first();

        if (!$test) {
            return response()->json(['message' => 'Test not found'], 404);
        }

        if ($request->marks > $test->mark) {
            return response()->json(['message' => 'Marks cannot exceed maximum allowed'], 422);
        }

        // Verify student registration in course and batch
        $isRegistered = DB::table('cource_registration')
            ->where('course_id', $test->course_id)
            ->where('batch_id', $test->batch_id)
            ->where('register_id', $request->register_id)
            ->exists();

        if (!$isRegistered) {
            return response()->json(['message' => 'Student is not registered for this test.'], 404);
        }

        DB::table('student_test_marks')->updateOrInsert(
            [
                'test_id' => $request->test_id,
                'register_id' => $request->register_id
            ],
            [
                'marks' => $request->marks,
                'updated_at' => now()
            ]
        );

        return response()->json(['message' => 'Marks saved for the student.']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Export test report data to Excel.
     */
    public function exportExcel(Request $request)
    {
        try {
            $this->authorizeTestReportList();

            $scope = strtolower($request->get('scope', 'filtered')) === 'all' ? 'all' : 'filtered';
            $selectedColumns = $this->resolveSelectedColumns($request->input('columns', []));
            $columnLabels = $this->mapColumnLabels($selectedColumns);

            $tests = $this->buildTestReportExportQuery($request, $scope)->get();

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Header row
            foreach (array_values($columnLabels) as $index => $label) {
                $sheet->setCellValueByColumnAndRow($index + 1, 1, $label);
            }

            // Data rows
            $rowNumber = 2;
            foreach ($tests as $test) {
                foreach ($selectedColumns as $columnIndex => $columnKey) {
                    $sheet->setCellValueByColumnAndRow(
                        $columnIndex + 1,
                        $rowNumber,
                        $this->formatColumnValueForExcel($test, $columnKey)
                    );
                }
                $rowNumber++;
            }

            $fileName = 'test-report-' . now()->format('Ymd_His') . '.xlsx';

            return response()->streamDownload(function () use ($spreadsheet) {
                $writer = new Xlsx($spreadsheet);
                $writer->save('php://output');
            }, $fileName, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
        } catch (\Exception $e) {
            Log::error('Test Report Export Error: ' . $e->getMessage());
            return back()->withErrors('Something went wrong while exporting the test report.');
        }
    }

    /**
     * Print view for test report.
     */
    public function printView(Request $request)
    {
        $this->authorizeTestReportList();

        $scope = strtolower($request->get('scope', 'filtered')) === 'all' ? 'all' : 'filtered';
        $selectedColumns = $this->resolveSelectedColumns($request->input('columns', []));
        $columnLabels = $this->mapColumnLabels($selectedColumns);

        $studentSearch = trim($request->student_search ?? '');
        $student_details = null;

        if (!empty($studentSearch)) {
            $student_details = DB::table('cource_registration')
                ->leftJoin('admission', 'cource_registration.register_id', '=', 'admission.id')
                ->leftJoin('master_course', 'cource_registration.course_id', '=', 'master_course.id')
                ->select(
                    'admission.id as admission_id',
                    'cource_registration.id as student_id',
                    DB::raw("CONCAT(admission.first_name,' ',admission.last_name,' ',admission.father_name) as student_name"),
                    'master_course.course_name',
                    'cource_registration.register_id',
                    'cource_registration.course_id',
                    'cource_registration.is_lateral_entry',
                    'cource_registration.joining_semester'
                )
                ->where(function ($q) use ($studentSearch) {
                    if (is_numeric($studentSearch)) {
                        $q->where('cource_registration.id', $studentSearch)
                          ->orWhere('admission.id', $studentSearch);
                    } else {
                        $q->where(DB::raw("CONCAT(admission.first_name,' ',admission.last_name,' ',admission.father_name)"),
                            'like', "%{$studentSearch}%");
                    }
                })
                ->first();

            if ($student_details) {
                // Determine current semester
                $currentSemester = 1;
                if ($student_details->is_lateral_entry == 1 && $student_details->joining_semester) {
                    $currentSemester = (int)$student_details->joining_semester;
                }
                $maxPaidSem = DB::table('fees_collections')
                    ->where('student_id', $student_details->register_id)
                    ->where('course_id', $student_details->course_id)
                    ->max('year_semester');
                if ($maxPaidSem && $maxPaidSem > $currentSemester) {
                    $currentSemester = (int)$maxPaidSem;
                }
                $student_details->current_semester = $currentSemester;
            }
        }

        $tests = $this->buildTestReportExportQuery($request, $scope)->get();

        return view($this->modules['folder_path'] . '.print', [
            'tests' => $tests,
            'studentDetails' => $student_details,
            'columnLabels' => $columnLabels,
            'columnKeys' => $selectedColumns,
            'scope' => $scope,
            'searchTerm' => $studentSearch,
            'generatedAt' => now(),
        ]);
    }

    /**
     * Base query for test report (shared between DataTable and export/print).
     */
    protected function buildTestReportBaseQuery()
    {
        return DB::table('test')
            ->select(
                'test.*',
                'cource_registration.fee',
                'test.date as cr_date',
                'cource_registration.register_id',
                'cource_registration.id as admission_id',
                DB::raw("CONCAT(admission.first_name, ' ', admission.last_name, ' ', admission.father_name) AS student_name"),
                'master_course.course_name as course_name',
                'student_test_marks.marks as student_mark',
                'student_test_marks.id as student_mark_id'
            )
            ->join('cource_registration', function ($join) {
                $join->on('test.course_id', '=', 'cource_registration.course_id')
                     ->on('test.batch_id', '=', 'cource_registration.batch_id');
            })
            ->leftJoin('admission', 'cource_registration.register_id', '=', 'admission.id')
            ->leftJoin('master_batch', 'test.batch_id', '=', 'master_batch.id')
            ->leftJoin('master_course', 'test.course_id', '=', 'master_course.id')
            ->leftJoin('student_test_marks', function ($join) {
                $join->on('test.id', '=', 'student_test_marks.test_id')
                     ->on('cource_registration.register_id', '=', 'student_test_marks.register_id');
            })
            ->whereNull('test.deleted_at')
            ->orderBy('test.id', 'desc');
    }

    /**
     * Build filtered query for export/print.
     */
    protected function buildTestReportExportQuery(Request $request, string $scope = 'filtered')
    {
        $query = $this->buildTestReportBaseQuery();

        if ($scope === 'filtered') {
            $studentSearch = trim($request->student_search ?? '');
            if (!empty($studentSearch)) {
                $query->where(function ($q) use ($studentSearch) {
                    if (is_numeric($studentSearch)) {
                        $q->where('cource_registration.id', $studentSearch)
                          ->orWhere('admission.id', $studentSearch);
                    } else {
                        $q->where(DB::raw("CONCAT(admission.first_name, ' ', admission.last_name, ' ', admission.father_name)"),
                            'like', "%{$studentSearch}%");
                    }
                });

                // Get details to filter by current semester and date
                $student_details = DB::table('cource_registration')
                    ->leftJoin('admission', 'cource_registration.register_id', '=', 'admission.id')
                    ->select(
                        'cource_registration.register_id',
                        'cource_registration.course_id',
                        'cource_registration.is_lateral_entry',
                        'cource_registration.joining_semester'
                    )
                    ->where(function ($q) use ($studentSearch) {
                        if (is_numeric($studentSearch)) {
                            $q->where('cource_registration.id', $studentSearch)
                              ->orWhere('admission.id', $studentSearch);
                        } else {
                            $q->where(DB::raw("CONCAT(admission.first_name,' ',admission.last_name,' ',admission.father_name)"),
                                'like', "%{$studentSearch}%");
                        }
                    })
                    ->first();

                if ($student_details) {
                    $currentSemester = 1;
                    if ($student_details->is_lateral_entry == 1 && $student_details->joining_semester) {
                        $currentSemester = (int)$student_details->joining_semester;
                    }
                    $maxPaidSem = DB::table('fees_collections')
                        ->where('student_id', $student_details->register_id)
                        ->where('course_id', $student_details->course_id)
                        ->max('year_semester');
                    if ($maxPaidSem && $maxPaidSem > $currentSemester) {
                        $currentSemester = (int)$maxPaidSem;
                    }

                    $now = \Carbon\Carbon::now();
                    $startOfAttendance = \Carbon\Carbon::create($now->year, 6, 1)->startOfDay();
                    if ($now->month < 6) {
                        $startOfAttendance->subYear();
                    }
                    $startDateStr = $startOfAttendance->format('Y-m-d');

                    $query->where('test.semester', $currentSemester);
                    $query->where('test.date', '>=', $startDateStr);
                }
            }

            $searchValue = $request->get('search');
            if (!empty($searchValue)) {
                $query->where(function ($q) use ($searchValue) {
                    $q->where(DB::raw("CONCAT(admission.first_name, ' ', admission.last_name)"), 'like', "%{$searchValue}%")
                      ->orWhere('test.subject_name', 'like', "%{$searchValue}%")
                      ->orWhere('test.mark', 'like', "%{$searchValue}%")
                      ->orWhere('student_test_marks.marks', 'like', "%{$searchValue}%");
                });
            }
        }

        return $query;
    }

    /**
     * Permission check for test report list.
     */
    protected function authorizeTestReportList(): void
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

    protected function formatColumnValueForExcel($test, string $columnKey)
    {
        switch ($columnKey) {
            case 'subject_name':
                return $test->subject_name ?? '-';
            case 'unit_name':
                return $test->unit_name ?? '-';
            case 'cr_date':
                return $test->cr_date ? date('d-m-Y', strtotime($test->cr_date)) : '-';
            case 'mark':
                return $test->mark ?? '-';
            case 'student_mark':
                return $test->student_mark ?? 0;
            default:
                return '';
        }
    }
}
