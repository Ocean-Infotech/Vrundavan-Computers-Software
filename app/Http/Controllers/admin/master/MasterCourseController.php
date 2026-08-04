<?php

namespace App\Http\Controllers\admin\master;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\FeesReceipt;
use App\Models\Master\MasterCourse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Requests\MasterCourceRequest;
use App\Models\Admission;
use App\Models\CourceRegistration;
use App\Models\FeesCollection;
use App\Models\StudentRequest;
use App\Models\Timetable;
use App\Traits\BioMetricTrait;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Database\Eloquent\Builder;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class MasterCourseController extends Controller
{

    use BioMetricTrait;

    public $modules = [];
    protected array $exportableColumns = [
        'course_name' => 'Course Name',
        'course_fees' => 'Course Fees',
        'course_year' => 'Course Duration (Years)',
        'semester' => 'Total Semesters',
        'status' => 'Status',
    ];
    protected array $defaultExportColumns = [
        'course_name',
        'course_fees',
        'course_year',
        'semester',
        'status',
    ];
    public function __construct()
    {
        $this->modules = [
            'title' => 'Course',
            'folder_path' => 'software.module.master.course',
            'route' => 'course',
            'table_name' => (new MasterCourse())->getTable(),
            'permission_prefix' => 'course',
        ];

        // $this->middleware('permission:'.$this->modules['permission_prefix'].'-list', ['only' => ['index','show']]);
        // $this->middleware('permission:'.$this->modules['permission_prefix'].'-create', ['only' => ['create','store']]);
        // $this->middleware('permission:'.$this->modules['permission_prefix'].'-edit', ['only' => ['edit','update']]);
        // $this->middleware('permission:'.$this->modules['permission_prefix'].'-delete', ['only' => ['destroy']]);

        View::share('course_year_type', MasterCourse::course_year_type());
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
                (object)['data' => "course_name", 'name' => 'course_name', 'td_label' => 'Course Name'],
                (object)['data' => "course_fees", 'name' => 'course_fees', 'td_label' => 'Course Fees'],
                (object)['data' => "course_year", 'name' => 'course_year', 'td_label' => 'Course Year'],
                (object)['data' => "semester", 'name' => 'semester', 'td_label' => 'Course Semester'],

                (object)['data' => "status", 'name' => 'status', 'td_label' => 'Status', 'className' =>  'w-5 text-center', 'width' => '10%'],
                (object)['data' => "action", 'name' => 'action', 'td_label' => 'Action', 'orderable' => false, 'searchable' => false, 'className' =>  'w-10 text-center'],
            ];
            View::share("columns", $columns);

            $data = [];

            if ($request->ajax()) {
                $data = MasterCourse::select('*');
                $data = $data->withTrashed();

                $returnData = Datatables::of($data)
                    ->addIndexColumn()
                    ->filter(function ($query) use ($request) {
                        if ($request->has('search')) {
                            $query->where('course_name', 'like', "%" . $request->search . "%");
                            $query->orWhere('course_fees', 'like', "%" . $request->search . "%");
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

    // ===============================
    // CASE 1: Not Deleted (Normal Row)
    // ===============================
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

    // ===============================
    // CASE 2: Deleted Row (Show Restore Only)
    // ===============================
    else {
        $btn .= '<a href="javascript:void(0)"
                    data-restore="' . route($modules["route"] . ".restore", ['id' => $row["id"]]) . '"
                    class="btn btn-light mx-1 record-restore"
                    data-bs-toggle="tooltip"
                    data-bs-placement="top"
                    data-bs-original-title="Restore Data">
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

    public function store(MasterCourceRequest $request)
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

        $token = Session::get('external_api_token');

        try {
            $data = $request->validated();
            $loginUserId = Auth::user()->id;
            $data['created_by'] = $loginUserId;

            $mastercourse = MasterCourse::create($data);

            if ($mastercourse?->biometric_id ==  null) {
                /*
                $apiPayload = [
                    'Name' => $mastercourse->course_name,
                ];

                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $token,
                ])->post('http://192.168.1.88:88/api/Department', $apiPayload);

                if ($response->successful()) {
                    $responseData = $response->json();

                    if (isset($responseData['Id'])) {
                        $mastercourse->update([
                            'biometric_id' => $responseData['Id']
                        ]);
                    }
                } else {
                    return Redirect::route('software.dashboard')
                        ->withErrors('Biometric API Error: ' . $response->body());
                }
                        */
            }
            if ($request->action == "save_next") {
                return Redirect::route($modules['route'] . '.create')
                    ->withSuccess($modules['title'] . ' added successfully. Please add next record.');
            }

            return Redirect::route($modules['route'] . '.index')
                ->withSuccess($modules['title'] . ' added successfully.');
        } catch (\Exception $e) {
            return Redirect::route('software.dashboard')->withErrors($e->getMessage());
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

            $edit = MasterCourse::findOrFail($id);
            return view($modules['folder_path'] . '.form', compact('modules', 'edit'));
        } catch (\Exception $e) {
            return Redirect::route('software.dashboard')->withErrors($e->getMessage());
        }
    }

    public function update(MasterCourceRequest $request, string $id)
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
            $validated = $request->validated();
            $validated['updated_by'] = Auth::user()->id;

            $updateData = MasterCourse::findOrFail($id);
            $updateData->update($validated);

            // if ($updateData?->biometric_id != "") {
            //     $token = Session::get('external_api_token');

            //     $apiResponse = Http::withHeaders([
            //         'Content-Type' => 'application/json',
            //         'Authorization' => 'Bearer ' . $token
            //     ])->post('http://192.168.1.88:88/api/Department', [
            //         "ID" => (int)$updateData->biometric_id,
            //         "Name" => $updateData->course_name
            //     ]);

            //     if ($apiResponse->successful()) {
            //         $responseData = $apiResponse->json();

            //         if (isset($responseData['Id'])) {
            //             $updateData->update([
            //                 'biometric_id' => $responseData['Id']
            //             ]);
            //         } else {
            //             return Redirect::route('software.dashboard')->withErrors('Biometric API did not return a valid ID.');
            //         }
            //     } else {
            //         return Redirect::route('software.dashboard')->withErrors('Biometric API error: ' . $apiResponse->status());
            //     }
            // }
            return Redirect::route($modules['route'] . '.index')
                ->withSuccess($modules['title'] . ' updated successfully');
        } catch (\Exception $e) {
            return Redirect::route('software.dashboard')->withErrors($e->getMessage());
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
            $designation = MasterCourse::withTrashed()->findOrFail($request?->id);

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
            $course = MasterCourse::findOrFail($id);

            // ✅ Use correct column names for all related modules
            $isUsed =
                $course->batches()->exists() ||

                FeesCollection::where('course_id', $course->id)->exists() ||  // corrected

                CourceRegistration::where('course_id', $course->id)->exists() || // corrected
                Timetable::where('course_id', $course->id)->exists();       // corrected


            if ($isUsed) {
                $message = 'This course cannot be deleted because it is already assigned in others Data.';
                if ($isAjax) {
                    return response()->json(['success' => false, 'message' => $message], 400);
                }
                return redirect()->route($modules['route'] . '.index')->withErrors($message);
            }

            // Soft delete
            $course->deleted_by = Auth::id();
            $course->save();
            $course->delete();

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
            $course = MasterCourse::withTrashed()->findOrFail($id);

            $isUsed =
                $course->batches()->exists() ||
                FeesCollection::where('course_id', $course->id)->exists() ||
                CourceRegistration::where('course_id', $course->id)->exists() ||
                Timetable::where('course_id', $course->id)->exists();

            if ($isUsed) {
                return response()->json([
                    'success' => false,
                    'message' => 'This course cannot be restored because it is already assigned in others data.'
                ], 400);  // important
            }

            $course->restore();

            return response()->json([
                'success' => true,
                'message' => 'Course restored successfully.'
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
        $modules = $this->modules;
        try {
            $course = MasterCourse::withTrashed()->findOrFail($id);

            $isUsed =
                $course->batches()->exists() ||
                FeesCollection::where('course_id', $course->id)->exists() ||
                CourceRegistration::where('course_id', $course->id)->exists() ||
                Timetable::where('course_id', $course->id)->exists();

            if ($isUsed) {
                return response()->json([
                    'success' => false,
                    'message' => 'This course cannot be permanently deleted because it is already assigned in others data.'
                ], 400);
            }

            $course->forceDelete();

            return response()->json([
                'success' => true,
                'message' => 'Course permanently deleted successfully.'
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
        $this->authorizeCourseList();

        $scope = strtolower($request->get('scope', 'filtered')) === 'all' ? 'all' : 'filtered';
        $selectedColumns = $this->resolveSelectedColumns($request->input('columns', []));
        $columnLabels = $this->mapColumnLabels($selectedColumns);

        $courses = $this->buildCourseExportQuery($request, $scope)->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        foreach (array_values($columnLabels) as $index => $label) {
            $sheet->setCellValueByColumnAndRow($index + 1, 1, $label);
        }

        $rowNumber = 2;
        foreach ($courses as $course) {
            foreach ($selectedColumns as $columnIndex => $columnKey) {
                $sheet->setCellValueByColumnAndRow(
                    $columnIndex + 1,
                    $rowNumber,
                    $this->formatColumnValueForExcel($course, $columnKey)
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

        $fileName = 'course-master-' . now()->format('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function printView(Request $request)
    {
        $this->authorizeCourseList();

        $scope = strtolower($request->get('scope', 'filtered')) === 'all' ? 'all' : 'filtered';
        $selectedColumns = $this->resolveSelectedColumns($request->input('columns', []));
        $columnLabels = $this->mapColumnLabels($selectedColumns);
        $searchTerm = $scope === 'filtered' ? trim((string) $request->get('search', '')) : null;

        $courses = $this->buildCourseExportQuery($request, $scope)->get();

        return view($this->modules['folder_path'] . '.print', [
            'courses' => $courses,
            'columnLabels' => $columnLabels,
            'columnKeys' => $selectedColumns,
            'scope' => $scope,
            'searchTerm' => $searchTerm,
            'generatedAt' => now(),
        ]);
    }

    protected function authorizeCourseList(): void
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

    protected function buildCourseExportQuery(Request $request, string $scope): Builder
    {
        $query = MasterCourse::query()->withTrashed()->orderByDesc('id');

        if ($scope === 'all') {
            return $query;
        }

        $search = trim((string) $request->get('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('course_name', 'like', "%{$search}%")
                    ->orWhere('course_fees', 'like', "%{$search}%")
                    ->orWhere('course_year', 'like', "%{$search}%")
                    ->orWhere('semester', 'like', "%{$search}%")
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

    protected function formatColumnValueForExcel(MasterCourse $course, string $columnKey)
    {
        switch ($columnKey) {
            case 'course_fees':
                return $course->course_fees !== null ? (float) $course->course_fees : null;
            case 'course_year':
                return $course->course_year !== null ? (int) $course->course_year : null;
            case 'semester':
                return $course->semester !== null ? (int) $course->semester : null;
            case 'status':
                return $course->status ? ucfirst($course->status) : '-';
            default:
                return $course->{$columnKey} ?? '-';
        }
    }
}
