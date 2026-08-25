<?php

namespace App\Http\Controllers\admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\StudentTestMark;
use App\Models\Test;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Requests\TestRequest;
use Illuminate\Database\Eloquent\Builder;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class TestController extends Controller
{
    public $modules = [];
    protected array $exportableColumns = [
        'course_name'  => 'Course Name',
        'batch_name'   => 'Batch Name',
        'semester'     => 'Semester',
        'subject_name' => 'Subject Name',
        'test_type'    => 'Test Type',
        'unit_name'    => 'Unit Name',
        'mark'         => 'Mark',
        'date'         => 'Date',
        'status'       => 'Status',
    ];

    protected array $defaultExportColumns = [
        'course_name',
        'batch_name',
        'semester',
        'subject_name',
        'test_type',
        'unit_name',
        'mark',
        'date',
        'status',
    ];
    public function __construct()
    {
        $this->modules = [
            'title' => 'Test',
            'folder_path' => 'software.module.test',
            'route' => 'test',
            'table_name' => (new Test())->getTable(),
            'permission_prefix' => 'test',
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
                (object)['data' => "course.course_name", 'name' => 'course_id', 'td_label' => 'Course Name'],
                (object)['data' => "batch.batch_name", 'name' => 'batch_id', 'td_label' => 'Batch Name'],
                (object)['data' => "semester", 'name' => 'semester', 'td_label' => 'Semester'],
                (object)['data' => "subject_name", 'name' => 'subject_name', 'td_label' => 'Subject Name'],
                (object)['data' => "test_type", 'name' => 'test_type', 'td_label' => 'Test Type'],
                (object)['data' => "unit_name", 'name' => 'unit_name', 'td_label' => 'Unit Name'],
                (object)['data' => "mark", 'name' => 'mark', 'td_label' => 'Mark', 'className' =>  'w-5 text-center'],
                (object)['data' => "date", 'name' => 'date', 'td_label' => 'Date', 'className' =>  'w-5 text-center'],
                // (object)['data' => "add_marks", 'name' => 'add_marks', 'td_label' => 'Marks Entry', 'orderable' => false, 'searchable' => false, 'className' =>  'w-5 text-center'],
                (object)['data' => "status", 'name' => 'status', 'td_label' => 'Status', 'className' =>  'w-5 text-center', 'width' => '10%'],
                (object)['data' => "action", 'name' => 'action', 'td_label' => 'Action', 'orderable' => false, 'searchable' => false, 'className' =>  'w-10 text-center'],
            ];
            View::share("columns", $columns);

            $data = [];

            if ($request->ajax()) {
                // dd($request->all());
                $data = Test::select('*')
                    ->withTrashed()
                    ->with(['course', 'batch'])
                    ->orderBy('id', 'desc');

                $returnData = Datatables::of($data)
                    ->addIndexColumn()
                    ->filter(function ($query) use ($request) {
                        if ($request->has('search')) {
                            $query->where('subject_name', 'like', "%" . $request->search . "%");
                            // $query->orWhere('subject_name', 'like',"%".$request->search."%");
                        }
                    })
                    ->editColumn('date', function ($row) {
                        return $row->date ? \Carbon\Carbon::parse($row->date)->format('d-m-Y') : '';
                    })
                    //                 ->addColumn('add_marks', function ($row) use ($modules) {
                    //                     return '<a href="' . route($modules["route"] . '.add-marks', [$row["id"]]) . '" class="btn btn-warning btn-icon" data-bs-toggle="tooltip" title="Add Marks">
                    //     <i class="fa-solid fa-file-circle-check"></i>
                    // </a>';
                    //                 })


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

                        // ✅ Move Add Marks button before edit/delete
                        $btn .= '<a href="' . route($modules["route"] . '.add-marks', [$row["id"]]) . '" class="btn btn-warning btn-icon mx-1" data-bs-toggle="tooltip" title="Add Marks">
                                <i class="fa-solid fa-file-circle-check"></i>
                             </a>';

                        $btn .= '<a href="' . route($modules["route"] . '.print-marks', [$row["id"]]) . '" target="_blank" class="btn btn-primary btn-icon mx-1" data-bs-toggle="tooltip" title="Print Marks Report">
                                <i class="fa-solid fa-print"></i>
                             </a>';

                        if (!$row?->deleted_at) {
                            if ($modules['permission_edit']) {
                                $btn .= '<a href="' . route($modules["route"] . ".edit", [$row["id"]]) . '" class="btn btn-light btn-icon mx-1" title="Edit">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                     </a>';
                            }
                            if ($modules['permission_delete']) {
                                $btn .= '<a href="javascript:void(0)" data-id="' . $row->id . '" data-did="' . route($modules["route"] . ".destroy", [$row["id"]]) . '" class="btn btn-danger btn-icon deletebutton mx-1" title="Delete">
                                        <i class="fa-solid fa-trash"></i>
                                     </a>';
                            }
                                          $btn .= '<a href="javascript:void(0)"
                data-did="' . route($modules["route"] . ".permanent-delete", [$row["id"]]) . '"
                class="btn btn-danger btn-icon permanentDeleteButton mx-1"
                title="Permanent Delete">
                <i class="fa-solid fa-ban"></i>
             </a>';
                        } else {
                            $btn .= '<a href="javascript:void(0)" data-restore="' . route($modules["route"] . ".restore", ['id' => $row["id"]]) . '" class="btn btn-light mx-1 record-restore" data-bs-toggle="tooltip" title="Restore Data">
                                    <i class="ti ti-history"></i>
                                 </a>';

                        }

                        return $btn ?: '-';
                    })
                    ->rawColumns(['status', 'action', 'add_marks'])
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

    public function exportExcel(Request $request)
    {
        $this->authorizeTestList();

        $scope = strtolower($request->get('scope', 'filtered')) === 'all' ? 'all' : 'filtered';
        $selectedColumns = $this->resolveSelectedColumns($request->input('columns', []));
        $columnLabels = $this->mapColumnLabels($selectedColumns);

        $tests = $this->buildTestExportQuery($request, $scope)->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        foreach (array_values($columnLabels) as $index => $label) {
            $sheet->setCellValueByColumnAndRow($index + 1, 1, $label);
        }

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

        if (!empty($selectedColumns)) {
            for ($i = 1; $i <= count($selectedColumns); $i++) {
                $columnLetter = Coordinate::stringFromColumnIndex($i);
                $sheet->getColumnDimension($columnLetter)->setAutoSize(true);
            }
        }

        $fileName = 'test-master-' . now()->format('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function printView(Request $request)
    {
        $this->authorizeTestList();

        $scope = strtolower($request->get('scope', 'filtered')) === 'all' ? 'all' : 'filtered';
        $selectedColumns = $this->resolveSelectedColumns($request->input('columns', []));
        $columnLabels = $this->mapColumnLabels($selectedColumns);
        $searchTerm = $scope === 'filtered' ? trim((string) $request->get('search', '')) : null;

        $tests = $this->buildTestExportQuery($request, $scope)->get();

        return view($this->modules['folder_path'] . '.print', [
            'tests' => $tests,
            'columnLabels' => $columnLabels,
            'columnKeys' => $selectedColumns,
            'scope' => $scope,
            'searchTerm' => $searchTerm,
            'generatedAt' => now(),
        ]);
    }

    protected function authorizeTestList(): void
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

    protected function buildTestExportQuery(Request $request, string $scope): Builder
    {
        $query = Test::query()
            ->withTrashed()
            ->with(['course', 'batch'])
            ->orderByDesc('id');

        if ($scope === 'all') {
            return $query;
        }

        $search = trim((string) $request->get('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('subject_name', 'like', "%{$search}%")
                    ->orWhere('unit_name', 'like', "%{$search}%")
                    ->orWhere('mark', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");
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

    protected function formatColumnValueForExcel(Test $test, string $columnKey)
    {
        switch ($columnKey) {
            case 'course_name':
                return $test->course?->course_name ?? '-';
            case 'batch_name':
                return $test->batch?->batch_name ?? '-';
            case 'semester':
                return $test->semester ?? '-';
            case 'subject_name':
                return $test->subject_name ?? '-';
            case 'test_type':
                return $test->test_type ?? '-';
            case 'unit_name':
                return $test->unit_name ?? '-';
            case 'mark':
                return $test->mark ?? '-';
            case 'date':
                return $test->date
                    ? \Carbon\Carbon::parse($test->date)->format('d-m-Y')
                    : '-';
            case 'status':
                return $test->status ? ucfirst($test->status) : '-';
            default:
                return data_get($test, $columnKey, '-');
        }
    }


    public function create()
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
        View::share('modules', $modules);
        return view($modules['folder_path'] . '.form', compact('modules'));
    }

    public function store(TestRequest $request)
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
        View::share('modules', $modules);
        // return $request->all();
        $validated = $request->validated();

        try {
            $validated['created_by'] = Auth::user()->id;
            Test::create($validated);
            return Redirect::route($modules['route'] . '.index')->withSuccess($modules['title'] . ' Create Successfully');
        } catch (\Exception $e) {
            return Redirect::route('dashboard')->withErrors($e->getMessage());
        }
    }

    public function edit(string $id)
    {
        $modules = $this->modules;
        $modules['login_user_role'] = Helper::getLoginUserRole();
        $modules['permission_edit'] = Helper::directCan($modules['permission_prefix'] . '-edit');
        if (!$modules['permission_edit']) {
            if (isset($request) && $request->ajax()) {
                return $this->sendError('User does not have the right permissions.', [], [], 403);
            }
            abort(403, 'User does not have the right permissions.');
        }
        View::share('modules', $modules);
        try {

            $edit = Test::findOrFail($id);
            return view($modules['folder_path'] . '.form', compact('modules', 'edit'));
        } catch (\Exception $e) {
            return Redirect::route('dashboard')->withErrors($e->getMessage());
        }
    }

    public function update(TestRequest $request, string $id)
    {
        $modules = $this->modules;
        $modules['login_user_role'] = Helper::getLoginUserRole();
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
            $updateData = Test::findOrFail($id);
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
            $designation = Test::withTrashed()->findOrFail($request?->id);

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
            if (isset($request) && $request->ajax()) {
                return $this->sendError('User does not have the right permissions.', [], [], 403);
            }
            abort(403, 'User does not have the right permissions.');
        }
        $isAjax = $request->ajax();

        try {
            $dataDelete = Test::findOrFail($id);

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
        try {
            $restoreData = Test::withTrashed()->findOrFail($id);
            $restoreData->restore();

            return response()->json([
                'status'  => true,
                'message' => 'Record restored successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function addMarks($id)
    {
        $modules = $this->modules;
        View::share('modules', $modules);

        try {
            // Load test with batch, active students, and their admission details
            $test = Test::with([
                'batch.students' => function ($query) {
                    $query->whereNotIn('status', ['Cancel', 'cancel']);
                },
                'batch.students.admission'
            ])->findOrFail($id);
            $students = $test->batch->students ?? [];

            // Load previously saved marks for each student for this specific test
            foreach ($students as $student) {
                $student->admission->load([
                    'testMarks' => function ($query) use ($id) {
                        $query->where('test_id', $id);
                    }
                ]);
            }

            return view($modules['folder_path'] . '.add_marks', compact('test', 'students', 'modules'));
        } catch (\Exception $e) {
            return Redirect::route('software.dashboard')->withErrors($e->getMessage());
        }
    }



    public function saveMarksAjax(Request $request)
    {
        try {
            $validated = $request->validate([
                'test_id' => 'required|integer',
                'student_id' => 'required|integer',
                'mark' => 'nullable|numeric',
            ]);

            $userId = Auth::id();

            // Find existing or create new
            $markRecord = \App\Models\StudentTestMark::where([
                'test_id' => $validated['test_id'],
                'register_id' => $validated['student_id'],
            ])->first();

            if ($markRecord) {
                $markRecord->update([
                    'marks' => $validated['mark'] ?? 0,
                    'updated_by' => $userId,
                ]);
                $markRecord->load(['updatedByUser', 'createdByUser']);
            } else {
                $markRecord = \App\Models\StudentTestMark::create([
                    'test_id' => $validated['test_id'],
                    'register_id' => $validated['student_id'],
                    'marks' => $validated['mark'] ?? 0,
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]);
                $markRecord->load(['updatedByUser', 'createdByUser']);
            }

            return response()->json([
                'success' => true,
                'faculty_name' => $markRecord->updatedByUser?->name ?? $markRecord->createdByUser?->name ?? 'N/A',
                'time' => $markRecord->updated_at ? $markRecord->updated_at->format('d-m-Y h:i A') : '',
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    public function permanentDelete($id)
{
    try {
        $modules = $this->modules;

        $test = Test::withTrashed()->findOrFail($id);

        // 🔥 Delete all student marks first
        StudentTestMark::where('test_id', $test->id)->delete();

        // 🔥 Permanently delete test
        $test->forceDelete();

        return response()->json([
            'success' => true,
            'message' => $modules['title'] . ' and its student marks permanently deleted successfully.'
        ]);

    } catch (\Exception $e) {

        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}

    public function printMarks($id)
    {
        try {
            $test = Test::with([
                'batch.students' => function ($query) {
                    $query->whereNotIn('status', ['Cancel', 'cancel']);
                },
                'batch.students.admission'
            ])->findOrFail($id);
            $students = $test->batch->students ?? [];

            foreach ($students as $student) {
                $student->admission->load([
                    'testMarks' => function ($query) use ($id) {
                        $query->where('test_id', $id);
                    }
                ]);
            }

            return view($this->modules['folder_path'] . '.print_marks', compact('test', 'students'));
        } catch (\Exception $e) {
            return Redirect::route('test.index')->withErrors($e->getMessage());
        }
    }
}
