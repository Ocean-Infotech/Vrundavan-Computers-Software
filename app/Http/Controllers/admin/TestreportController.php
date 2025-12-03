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

class TestreportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public $modules = [];
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
            (object)['data' => "subject_name", 'name' => 'test.subject_name', 'td_label' => 'Subject'],
            (object)['data' => "unit_name", 'name' => 'test.unit_name', 'td_label' => 'Unit'],
            (object)['data' => "cr_date", 'name' => 'cource_registration.date', 'td_label' => 'Date'],
            (object)['data' => "mark", 'name' => 'test.mark', 'td_label' => 'Total Mark'],
            (object)['data' => "input", 'name' => 'test.marks', 'td_label' => 'Marks'],
        ];
        View::share("columns", $columns);

        $subjects = DB::table('test')
            ->whereNull('deleted_at')
            ->distinct()
            ->orderBy('subject_name', 'asc')
            ->pluck('subject_name');

        View::share('subjects', $subjects);

        if ($request->ajax()) {


            $query = DB::table('test')
                ->select(
                    'test.*',
                    'cource_registration.fee',
                    'cource_registration.date as cr_date',
                    'cource_registration.register_id',
                    'cource_registration.id as admission_id',
                    DB::raw("CONCAT(admission.first_name, ' ', admission.last_name, ' ', admission.father_name) AS student_name"),

                    // 'master_batch.batch_name',
                    // 'master_course.course_name',
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

            return DataTables::of($query)
                ->addIndexColumn()
                ->filter(function ($query) use ($request) {

                $studentSearch = trim($request->student_search ?? '');
                $searchValue = $request->search['value'] ?? null;

                // ----------------------------------------
                // CASE 1: Student Search → Show only his data
                // ----------------------------------------
                if (!empty($studentSearch)) {

                    $query->where(function ($q) use ($studentSearch) {

                        if (is_numeric($studentSearch)) {

                            // 🟢 Typed ID → Exact match
                            $q->where('cource_registration.id', $studentSearch)
                              ->orWhere('admission.id', $studentSearch);

                        } else {

                            // 🟢 Typed Name → LIKE match
                            $q->where(DB::raw("
                                CONCAT(admission.first_name, ' ', admission.last_name, ' ', admission.father_name)
                            "), 'like', "%{$studentSearch}%");
                        }

                    });

                    return; // IMPORTANT
                }

                // ----------------------------------------
                // CASE 2: Normal datatable search
                // ----------------------------------------
                if (!empty($searchValue)) {
                    $query->where(function ($q) use ($searchValue) {
                        $q->where(DB::raw("CONCAT(admission.first_name, ' ', admission.last_name)"), 'like', "%{$searchValue}%")
                          ->orWhere('test.subject_name', 'like', "%{$searchValue}%")
                          ->orWhere('test.marks', 'like', "%{$searchValue}%");
                    });
                }
            })


                ->editColumn('cr_date', function ($row) {
                    return $row->cr_date ? date('d-m-Y', strtotime($row->cr_date)) : '-';
                })
                ->editColumn('subject_name', function ($row) {
                    return $row->subject_name ?? '-';
                })
                ->addColumn('input', function ($row) {
                    $value = htmlspecialchars($row->student_mark ?? 0, ENT_QUOTES, 'UTF-8'); // ✅ default 0
                    if ($value === '' || $value === null) {
                        $value = 0; // ✅ handles empty string also
                    }
                    return '<span>' . $value . '</span>';
                })


                ->rawColumns(['input'])
                ->make(true);
        }

        return view($modules['folder_path'] . '.index');
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

        if ($request->marks > $test->marks) {
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
}
