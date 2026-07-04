<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CourceRegistration;
use App\Models\Master\MasterCourse;
use App\Models\Master\MasterBatch;
use App\Models\FeesCollection;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class FeesCollectionReportController extends Controller
{
    public $modules = [];

    public function __construct()
    {
        $this->modules = [
            'title' => 'Fees Collection Report',
            'folder_path' => 'software.module.fees-collection-report',
            'route' => 'fees-collection-report',
        ];
    }

    public function index(Request $request)
    {
        $modules = $this->modules;
        View::share('modules', $modules);

        if ($request->ajax()) {
            $course_id = $request->course_id;
            $batch_id = $request->batch_id;
            $semester_id = $request->semester_id;

            // removed required check

            $course = MasterCourse::find($course_id);
            
            $sub = CourceRegistration::select(DB::raw('MAX(id) as id'))
                ->groupBy('register_id');

            $query = CourceRegistration::whereIn('id', $sub)
                ->with(['admission', 'course']);

            if ($course_id) {
                $query->where('course_id', $course_id);
            }
            if ($batch_id) {
                $query->where('batch_id', $batch_id);
            }

            $allStudents = $query->get();
            $students = [];

            if ($semester_id && $course) {
                $totalSemesters = $course->semester ?? 0;
                $semesterFee = ($course->course_fees && $totalSemesters > 0)
                    ? $course->course_fees / $totalSemesters
                    : 0;

                $feesCollections = FeesCollection::whereIn('student_id', $allStudents->pluck('register_id'))
                    ->where('course_id', $course_id)
                    ->get()
                    ->groupBy('student_id');

                foreach ($allStudents as $reg) {
                    $studentFees = $feesCollections->get($reg->register_id, collect());
                    $paidForSemester = 0;

                    foreach ($studentFees as $fee) {
                        if (strtolower(trim($fee->year_semester)) == strtolower(trim($semester_id)) || trim($fee->year_semester) == trim($semester_id)) {
                            $paidForSemester += $fee->fees;
                        }
                    }

                    $pending = max($semesterFee - $paidForSemester, 0);

                    if ($pending > 0) {
                        $students[] = $reg;
                    }
                }
            } else {
                $students = $allStudents->all();
            }

            $students = collect($students)->sortBy(function($reg) {
                return $reg->admission->first_name ?? '';
            });

            return Datatables::of($students)
                ->addIndexColumn()
                ->addColumn('student_name', function ($row) {
                    $first = $row->admission->first_name ?? '';
                    $last = $row->admission->last_name ?? '';
                    $father = $row->admission->father_name ?? '';
                    return trim("{$last} {$first} {$father}");
                })
                ->addColumn('date_1', function ($row) { return ''; })
                ->addColumn('amount_1', function ($row) { return ''; })
                ->addColumn('date_2', function ($row) { return ''; })
                ->addColumn('amount_2', function ($row) { return ''; })
                ->make(true);
        }

        $columns = [
            (object)['data' => 'DT_RowIndex', 'name' => 'id', 'td_label' => 'No', 'orderable' => false, 'searchable' => false],
            (object)['data' => 'student_name', 'name' => 'student_name', 'td_label' => 'Student Name'],
            (object)['data' => 'date_1', 'name' => 'date_1', 'td_label' => 'Date'],
            (object)['data' => 'amount_1', 'name' => 'amount_1', 'td_label' => 'Amount'],
            (object)['data' => 'date_2', 'name' => 'date_2', 'td_label' => 'Date'],
            (object)['data' => 'amount_2', 'name' => 'amount_2', 'td_label' => 'Amount'],
        ];

        return view($modules['folder_path'] . '.index', compact('columns'));
    }

    public function printList(Request $request)
    {
        $course_id = $request->course_id;
        $batch_id = $request->batch_id;
        $semester_id = $request->semester_id;

        $course = MasterCourse::find($course_id);
        $batch = MasterBatch::find($batch_id);

        // removed required check

        $sub = CourceRegistration::select(DB::raw('MAX(id) as id'))
            ->groupBy('register_id');

        $query = CourceRegistration::whereIn('id', $sub)
            ->with(['admission', 'course']);

        if ($course_id) {
            $query->where('course_id', $course_id);
        }
        if ($batch_id) {
            $query->where('batch_id', $batch_id);
        }

        $allStudents = $query->get();
        $students = [];

        if ($semester_id && $course) {
            $totalSemesters = $course->semester ?? 0;
            $semesterFee = ($course->course_fees && $totalSemesters > 0)
                ? $course->course_fees / $totalSemesters
                : 0;

            $feesCollections = FeesCollection::whereIn('student_id', $allStudents->pluck('register_id'))
                ->where('course_id', $course_id)
                ->get()
                ->groupBy('student_id');

            foreach ($allStudents as $reg) {
                $studentFees = $feesCollections->get($reg->register_id, collect());
                $paidForSemester = 0;

                foreach ($studentFees as $fee) {
                    if (strtolower(trim($fee->year_semester)) == strtolower(trim($semester_id)) || trim($fee->year_semester) == trim($semester_id)) {
                        $paidForSemester += $fee->fees;
                    }
                }

                $pending = max($semesterFee - $paidForSemester, 0);

                if ($pending > 0) {
                    $students[] = $reg;
                }
            }
        } else {
            $students = $allStudents->all();
        }

        $students = collect($students)->sortBy(function($reg) {
            return $reg->admission->first_name ?? '';
        });

        return view('software.module.fees-collection-report.print', compact('students', 'course', 'batch', 'semester_id'));
    }
}
