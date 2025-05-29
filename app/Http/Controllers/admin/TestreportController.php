<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Test;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Requests\TestRequest;
use Illuminate\Support\Facades\DB;

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
            'permisstion_prefix' => 'Test Report',
        ];

        // $this->middleware('permission:'.$this->modules['permisstion_prefix'].'-list', ['only' => ['index','show']]);
        // $this->middleware('permission:'.$this->modules['permisstion_prefix'].'-create', ['only' => ['create','store']]);
        // $this->middleware('permission:'.$this->modules['permisstion_prefix'].'-edit', ['only' => ['edit','update']]);
        // $this->middleware('permission:'.$this->modules['permisstion_prefix'].'-delete', ['only' => ['destroy']]);
    }


    public function index(Request $request)
    {
        $modules = $this->modules;
        View::share('modules', $modules);

        // Columns for datatable
        $columns = [
            (object)['data' => "student_name", 'name' => 'admission.first_name', 'td_label' => 'Student Name'],
            (object)['data' => "batch_name", 'name' => 'master_batch.batch_name', 'td_label' => 'Batch'],
            (object)['data' => "course_name", 'name' => 'master_course.course_name', 'td_label' => 'Course Name'],
            (object)['data' => "subject_name", 'name' => 'test.subject_name', 'td_label' => 'Subject'],
            (object)['data' => "cr_date", 'name' => 'cource_registration.date', 'td_label' => 'Date'],
            (object)['data' => "input", 'name' => 'test.marks', 'td_label' => 'Marks'],
        ];
        View::share("columns", $columns);

        // Fetch distinct subject names from `test` table (excluding deleted records)
        $subjects = DB::table('test')
            ->whereNull('deleted_at')
            ->distinct()
            ->orderBy('subject_name', 'asc')
            ->pluck('subject_name');

        // Share subjects to the view
        View::share('subjects', $subjects);

        if ($request->ajax()) {
            $query = DB::table('test')
                ->select(
                    'test.*',
                    'cource_registration.fee',
                    'cource_registration.date as cr_date',
                    'cource_registration.register_id',
                    DB::raw("CONCAT(admission.first_name, ' ', admission.last_name) AS student_name"),
                    'master_batch.batch_name',
                    'master_course.course_name',
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
                    // Check if any filter is applied or global search exists
                    $hasFilter = false;

                    if (!empty($request->search['value'])) {
                        $hasFilter = true;
                    }

                    if ($request->filled('course_id') || $request->filled('batch_id') || $request->filled('subject_name') || $request->filled('date')) {
                        $hasFilter = true;
                    }

                    if (!$hasFilter) {
                        // No filter or search applied - return no rows
                        $query->whereRaw('0=1');
                        return;
                    }

                    // Existing filters...
                    if (!empty($request->search['value'])) {
                        $search = $request->search['value'];
                        $query->where(function ($q) use ($search) {
                            $q->where(DB::raw("CONCAT(admission.first_name, ' ', admission.last_name)"), 'like', "%{$search}%")
                                ->orWhere('master_course.course_name', 'like', "%{$search}%")
                                ->orWhere('test.marks', 'like', "%{$search}%")
                                ->orWhere('test.subject_name', 'like', "%{$search}%");
                        });
                    }

                    if ($request->filled('course_id')) {
                        $query->where('test.course_id', $request->course_id);
                    }

                    if ($request->filled('batch_id')) {
                        $query->where('test.batch_id', $request->batch_id);
                    }

                    if ($request->filled('subject_name')) {
                        $query->where('test.subject_name', $request->subject_name);
                    }

                    if ($request->filled('date')) {
                        $query->whereDate('test.date', $request->date);
                    }
                })


                ->editColumn('cr_date', function ($row) {
                    return $row->cr_date ? date('d-m-Y', strtotime($row->cr_date)) : '-';
                })
                ->editColumn('subject_name', function ($row) {
                    return $row->subject_name ?? '-';
                })
                ->addColumn('input', function ($row) {
                    $value = htmlspecialchars($row->student_mark ?? '', ENT_QUOTES, 'UTF-8');
                    $max = htmlspecialchars($row->marks ?? '', ENT_QUOTES, 'UTF-8');
                    return '<input
                        type="text"
                        name="marks' . ($row->student_mark_id ?? $row->id) . '"
                        value="' . $value . '"
                        min="0"
                        max="' . $max . '"
                        placeholder="Max: ' . $max . '"
                        data-id="' . $row->id . '"
                        class="form-control w-50 mark-input"
                        required
                        oninput="this.value = this.value.replace(/[^0-9]/g, \'\');" />';
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
        $request->validate([
            'test_id' => 'required|integer|exists:test,id',
            'marks' => 'required|numeric|min:0',
        ]);

        $test = DB::table('test')->where('id', $request->test_id)->first();

        if (!$test) {
            return response()->json(['message' => 'Test not found'], 404);
        }

        if ($request->marks > $test->mark) {
            return response()->json(['message' => 'Marks cannot exceed maximum allowed'], 422);
        }

        // Get students registered in that batch + course
        $students = DB::table('cource_registration')
            ->where('course_id', $test->course_id)
            ->where('batch_id', $test->batch_id)
            ->pluck('register_id');

        if ($students->isEmpty()) {
            return response()->json(['message' => 'No students found for this test.'], 404);
        }

        foreach ($students as $registerId) {
            DB::table('student_test_marks')->updateOrInsert(
                [
                    'test_id' => $request->test_id,
                    'register_id' => $registerId
                ],
                [
                    'marks' => $request->marks,
                    'updated_at' => now()
                ]
            );
        }

        return response()->json(['message' => 'Marks saved for all students.']);
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
