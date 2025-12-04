<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Admission;
use App\Models\CourceRegistration;
use App\Models\FeesCollection;
use App\Models\Master\MasterBatch;
use App\Models\Master\MasterClass;
use App\Models\Master\MasterCourse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class FeescollectionpendingController extends Controller
{
    public $modules = [];
    public function __construct()
    {
        $this->modules = [
            'title' => 'Fees Pedding',
            'folder_path' => 'software.module.fees-collection-pending',
            'route' => 'fees-collection-pending',
            'table_name' => (new FeesCollection())->getTable(),
            'permisstion_prefix' => 'fees-collection-pending',
        ];

        // $this->middleware('permission:'.$this->modules['permisstion_prefix'].'-list', ['only' => ['index','show']]);
        // $this->middleware('permission:'.$this->modules['permisstion_prefix'].'-create', ['only' => ['create','store']]);
        // $this->middleware('permission:'.$this->modules['permisstion_prefix'].'-edit', ['only' => ['edit','update']]);
        // $this->middleware('permission:'.$this->modules['permisstion_prefix'].'-delete', ['only' => ['destroy']]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $modules = $this->modules;
        View::share('modules', $modules);

        try {
            // Define DataTable columns
            $columns = [
                (object)['data' => 'register_id', 'name' => 'register_id', 'td_label' => 'Student ID'],
                (object)['data' => 'student_name', 'name' => 'register_id', 'td_label' => 'Student Name'],
                (object)['data' => 'contact_no', 'name' => 'contact_no', 'td_label' => 'Contact No'],
                (object)['data' => 'course.course_name', 'name' => 'course_id', 'td_label' => 'Course Name'],
                (object)['data' => 'status', 'name' => 'status', 'td_label' => 'Status'],
                (object)['data' => 'total_fee', 'name' => 'total_fee', 'td_label' => 'Total Fees'],
                (object)['data' => 'paid_fee', 'name' => 'paid_fee', 'td_label' => 'Paid Fees'],
                (object)['data' => 'pending_fee', 'name' => 'pending_fee', 'td_label' => 'Pending Fees'],
                (object)['data' => 'action', 'name' => 'action', 'td_label' => 'Action'],
            ];
            View::share("columns", $columns);

            $admissions = Admission::whereIn('status', ['created', 'completed'])->get();
            $courses = MasterCourse::all();
            $batches = MasterBatch::all(); // assuming you have a table for batches
            $classes = MasterClass::all();
            $semesters = range(1, 8); // or fetch from DB if dynamic


            // dd('hello');
            if ($request->ajax()) {

                // dd($request->all());
                $sub = CourceRegistration::select(DB::raw('MAX(id) as id'))
                    // ->where('status', 'active')
                    ->groupBy('register_id');

                $dataQuery = CourceRegistration::whereIn('id', $sub)
                    ->with(['course', 'admission', 'batch', 'class', 'shift', 'feescollection'])
                    ->withTrashed()
                    ->orderBy('created_at', 'desc');

                // FILTERING

                // Filter by student - search in ID, name or contact_no
                if ($request->filled('student_filter')) {
                    $studentFilter = $request->student_filter;

                    // Use whereHas for relation 'admission' to filter name or contact_no
                    $dataQuery->where(function ($q) use ($studentFilter) {
                        $q->where('register_id', $studentFilter)
                            ->orWhereHas('admission', function ($q2) use ($studentFilter) {
                                $q2->where(DB::raw("CONCAT(first_name,' ',last_name,' ',father_name)"), 'like', "%$studentFilter%")
                                    ->orWhere('mobile_no', 'like', "%$studentFilter%");
                            });
                    });
                }

                // Filter by course_id
                if ($request->filled('course_id')) {
                    $courseId = $request->course_id;
                    $dataQuery->where('course_id', $courseId);
                }

                if ($request->filled('batch_id')) {
                    $dataQuery->where('batch_id', $request->batch_id);
                }

                if ($request->filled('class_id')) {
                    $dataQuery->where('class_id', $request->class_id);
                }

                if ($request->filled('semester_id')) {
                    $dataQuery->whereHas('feescollection', function ($q) use ($request) {
                        $q->where('year_semester', $request->semester_id);
                    });
                }


                $data = $dataQuery->get();

                $allRegistrations = CourceRegistration::with(['course', 'class', 'shift', 'batch'])
                    ->whereIn('register_id', $data->pluck('register_id'))
                    ->get()
                    ->groupBy('register_id');

                $feesCollection = FeesCollection::select('student_id', DB::raw('SUM(fees) as total_paid'))
                    ->whereIn('student_id', $data->pluck('register_id'))
                    ->groupBy('student_id')
                    ->pluck('total_paid', 'student_id');

                return Datatables::of($data)
                    ->addIndexColumn()
                    ->editColumn('student_name', function ($row) {
                        $first = $row->admission->first_name ?? '';

                        $last = $row->admission->last_name ?? '';
                        $father = $row->admission->father_name ?? '';
                        return trim("{$first}  {$last} {$father}");
                    })
                    ->addColumn('contact_no', function ($row) {
                        return $row->admission->mobile_no ?? '-';
                    })
                    ->addColumn('action', function ($row) {
                        $url = route('fees-collection-pending.view-fee', $row->register_id);
                        return '<a href="' . $url . '" class="btn btn-sm btn-primary">View Fee</a>';
                    })

                    ->editColumn('course_name', function ($row) use ($allRegistrations) {
                        $registrations = $allRegistrations->get($row->register_id, collect());
                        return $registrations->map(function ($item) {
                            return $item->course->course_name . ' (' . number_format((float) $item->fee, 2) . ')';
                        })->implode('<br> ');
                    })


                    ->addColumn('total_fee', function ($row) use ($allRegistrations) {
                        $total_fee = (float) $allRegistrations->get($row->register_id, collect())->sum('fee');
                        return '₹' . number_format($total_fee, 2);
                    })
                    ->addColumn('paid_fee', function ($row) use ($feesCollection) {
                        $paid_fee = $feesCollection[$row->register_id] ?? 0;
                        return '₹' . number_format($paid_fee, 2);
                    })


                    ->addColumn('pending_fee', function ($row) use ($allRegistrations, $feesCollection) {
                        $total_fee = (float) $allRegistrations->get($row->register_id, collect())->sum('fee');
                        $paid_fee = (float) ($feesCollection[$row->register_id] ?? 0);
                        $pending = $total_fee - $paid_fee;
                        return '₹' . number_format($pending, 2);
                    })
                    ->rawColumns(['total_fee', 'course_name', 'date', 'action'])
                    ->make(true);
            }

            return view($modules['folder_path'] . '.index', compact('admissions', 'courses'));
        } catch (\Exception $e) {
            return Redirect::route('software.dashboard')->withErrors($e->getMessage());
        }
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
        //
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

    public function exportExcel(Request $request)
    {
        try {
            $sub = CourceRegistration::select(DB::raw('MAX(id) as id'))
                // ->where('status', 'active')
                ->groupBy('register_id');

            $dataQuery = CourceRegistration::whereIn('id', $sub)
                ->with(['course', 'admission', 'feescollection']);

            if ($request->filled('student_filter')) {
                $studentFilter = $request->student_filter;
                $dataQuery->where(function ($q) use ($studentFilter) {
                    $q->where('register_id', $studentFilter)
                        ->orWhereHas('admission', function ($q2) use ($studentFilter) {
                            $q2->where(DB::raw("CONCAT(first_name,' ',father_name,' ',last_name)"), 'like', "%$studentFilter%")
                                ->orWhere('mobile_no', 'like', "%$studentFilter%");
                        });
                });
            }

            if ($request->filled('course_id')) {
                $courseId = $request->course_id;
                $dataQuery->where('course_id', $courseId);
            }

            if ($request->filled('batch_id')) {
                $dataQuery->where('batch_id', $request->batch_id);
            }

            if ($request->filled('class_id')) {
                $dataQuery->where('class_id', $request->class_id);
            }

            if ($request->filled('semester_id')) {
                $dataQuery->whereHas('feescollection', function ($q) use ($request) {
                    $q->where('year_semester', $request->semester_id);
                });
            }

            $data = $dataQuery->get();

            $allRegistrations = CourceRegistration::with(['course', 'class', 'shift', 'batch'])
                ->whereIn('register_id', $data->pluck('register_id'))
                ->get()
                ->groupBy('register_id');


            $feesCollection = FeesCollection::select(
                'student_id',
                'year_semester',
                DB::raw('SUM(fees) as total_paid')
            )
                ->whereIn('student_id', $data->pluck('register_id'))
                ->groupBy('student_id', 'year_semester') // group by both fields
                ->get();

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $sheet->setCellValue('A1', 'Course Name');
            $sheet->setCellValue('B1', 'Semester');
            $sheet->setCellValue('C1', 'Class');
            $sheet->setCellValue('D1', 'Student Name');
            $sheet->setCellValue('E1', 'Total Fee');
            $sheet->setCellValue('F1', 'Pending Fee');

            $row = 2;
            foreach ($data as $item) {
                $reg_id = $item->register_id;
                $name = trim(($item->admission->first_name ?? '') . ' ' . ($item->admission->father_name ?? '') . ' ' . ($item->admission->last_name ?? ''));

                $courses = $allRegistrations[$reg_id] ?? collect();
                $course_names = $courses->map(fn($c) => $c->course->course_name ?? '')->implode(', ');

                $total_fee = $courses->sum('fee');
                // $paid_fee = $feesCollection[$reg_id] ?? 0;
                // $pending_fee = $total_fee - $paid_fee;
                // $year_semester = $feesCollection[$reg_id] ?? 0;

                $paid_fee = $feesCollection->where('student_id', $reg_id)->sum('total_paid');
                $pending_fee = $total_fee - $paid_fee;

                $year_semester = optional($feesCollection->firstWhere('student_id', $reg_id))->year_semester ?? '-';


                $class = $item->class->class ?? '-';


                $sheet->setCellValue('A' . $row, $course_names);
                $sheet->setCellValue('B' . $row, $year_semester);
                $sheet->setCellValue('C' . $row, $class);
                $sheet->setCellValue('D' . $row, $name);
                $sheet->setCellValue('E' . $row, $total_fee);
                $sheet->setCellValue('F' . $row, $pending_fee);
                $row++;
            }

            $writer = new Xlsx($spreadsheet);
            $fileName = 'Pending_Fees_Report_Filtered.xlsx';

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header("Content-Disposition: attachment; filename=\"$fileName\"");
            $writer->save("php://output");
            exit;
        } catch (\Exception $e) {
            return Redirect::back()->withErrors($e->getMessage());
        }
    }
    public function printView(Request $request)
    {
        $sub = CourceRegistration::select(DB::raw('MAX(id) as id'))
            // ->where('status', 'active')
            ->groupBy('register_id');

        $dataQuery = CourceRegistration::whereIn('id', $sub)
            ->with(['course', 'admission', 'feescollection']);

        if ($request->filled('student_filter')) {
            $studentFilter = $request->student_filter;
            $dataQuery->where(function ($q) use ($studentFilter) {
                $q->where('register_id', $studentFilter)
                    ->orWhereHas('admission', function ($q2) use ($studentFilter) {
                        $q2->where(DB::raw("CONCAT(first_name,' ',father_name,' ',last_name)"), 'like', "%$studentFilter%")
                            ->orWhere('mobile_no', 'like', "%$studentFilter%");
                    });
            });
        }

        if ($request->filled('course_id')) {
            $courseId = $request->course_id;
            $dataQuery->where('course_id', $courseId);
        }

        if ($request->filled('batch_id')) {
            $dataQuery->where('batch_id', $request->batch_id);
        }

        if ($request->filled('class_id')) {
            $dataQuery->where('class_id', $request->class_id);
        }

        if ($request->filled('semester_id')) {
            $dataQuery->whereHas('feescollection', function ($q) use ($request) {
                $q->where('year_semester', $request->semester_id);
            });
        }

        $data = $dataQuery->get();

        $allRegistrations = CourceRegistration::with(['course', 'class', 'shift', 'batch'])
            ->whereIn('register_id', $data->pluck('register_id'))
            ->get()
            ->groupBy('register_id');

        $feesCollection = FeesCollection::select('student_id', DB::raw('SUM(fees) as total_paid'))
            ->whereIn('student_id', $data->pluck('register_id'))
            ->groupBy('student_id')
            ->pluck('total_paid', 'student_id');

        return view('software.module.fees-collection-pending.print', compact('data', 'allRegistrations', 'feesCollection'));
    }
    public function viewFee($id)
    {
        try {
            // Student fetch
            $student = Admission::findOrFail($id);

            // Active course registration with course details
            $courseReg = CourceRegistration::where('register_id', $id)
                // ->where('status', 'active')
                ->with('course')
                ->firstOrFail();

            if (!$courseReg || !$courseReg->course) {
                return back()->withErrors('Course not found for this student.');
            }

            // Semester wise fee calculation
            $totalSemesters = $courseReg->course->semester ?? 0;
            $semesterFee    = ($courseReg->course->course_fees && $totalSemesters > 0)
                ? $courseReg->course->course_fees / $totalSemesters
                : 0;

            // Get fees paid grouped by semester
            $feesCollections = FeesCollection::where('student_id', $id)
                ->where('course_id', $courseReg->course_id)
                ->get();

            $semesterPaid = [];
            foreach ($feesCollections as $fee) {
                $sem = $fee->year_semester;
                $semesterPaid[$sem] = ($semesterPaid[$sem] ?? 0) + $fee->fees;
            }

            // Prepare final data
            $feesData = [];
            for ($sem = 1; $sem <= $totalSemesters; $sem++) {
                $paid    = $semesterPaid[$sem] ?? 0;
                $pending = max($semesterFee - $paid, 0);

                $feesData[] = [
                    'semester'    => "Semester $sem",
                    'total_fee'   => $semesterFee,
                    'paid_fee'    => $paid,
                    'pending_fee' => $pending,
                ];
            }

            return view('software.module.fees-collection-pending.view-fee', compact('student', 'courseReg', 'feesData'));
        } catch (\Exception $e) {
            return back()->withErrors($e->getMessage());
        }
    }
}
