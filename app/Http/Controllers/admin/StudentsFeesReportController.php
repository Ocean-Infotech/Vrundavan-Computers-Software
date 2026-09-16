<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Admission;
use App\Models\CourceRegistration;
use App\Models\FeesCollection;
use App\Models\FeesReceipt;
use App\Models\Master\MasterCourse;
use App\Models\Master\MasterFeeDetail;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\Facades\DataTables;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentsFeesReportController extends Controller
{
    public $modules = [];
    public function __construct()
    {
        $this->modules = [
            'title' => 'Students Fees Report',
            'folder_path' => 'software.module.students-fees-report',
            'route' => 'students-fees-report',
            'table_name' => (new FeesCollection())->getTable(),
            'permisstion_prefix' => 'students-fees-report',
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

        $columns = [
            (object)['data' => 'year_semester', 'name' => 'year_semester', 'td_label' => 'Semester'],
            (object)['data' => 'total_fee', 'name' => 'total_fee', 'td_label' => 'Total Fees'],
            (object)['data' => 'paid_fee', 'name' => 'paid_fee', 'td_label' => 'Paid Fees'],
            (object)['data' => 'pending_fee', 'name' => 'pending_fee', 'td_label' => 'Pending Fees'],
            (object)['data' => 'action', 'name' => 'action', 'td_label' => 'Action'],

        ];
        View::share("columns", $columns);

        if ($request->ajax()) {
            $student_id = $request->search_id;
            $course_id = $request->course_id;

            // Get course and fee data
            $courseRegistration = CourceRegistration::where('register_id', $student_id)
                ->where('course_id', $course_id)
                ->with(['course'])
                ->first();

            if (!$courseRegistration || !$courseRegistration->course) {
                return DataTables::of([])->make(true);
            }
            // dd("ln-376", "course - ", $courseRegistration->course->course_fees, "samaster - ",$courseRegistration->course->semester);
            $semester_fee = ($courseRegistration->course->course_fees && $courseRegistration->course->semester)
                ? $courseRegistration->course->course_fees / $courseRegistration->course->semester
                : 0;

            // dd($semester_fee);
            // Get fees paid per semester
            $feesCollections = FeesCollection::where('admission_id', $student_id)
                ->where('course_id', $course_id)
                ->get();

            $semesterPaid = [];
            foreach ($feesCollections as $fee) {
                $sem = $fee->year_semester;
                $semesterPaid[$sem] = ($semesterPaid[$sem] ?? 0) + $fee->fees;
            }

            // Create flat array per semester
            $data = [];
            $total_semesters = $courseRegistration->course->semester;

            for ($sem = 1; $sem <= $total_semesters; $sem++) {
                $paid = $semesterPaid[$sem] ?? 0;
                $pending = $semester_fee - $paid;


                $data[] = [
                    'id' => $feesCollections->where('year_semester', $sem)->first()->id ?? null,
                    'year_semester' => "Semester $sem",
                    'total_fee' => $semester_fee,
                    'paid_fee' => $paid,
                    'pending_fee' => max($pending, 0),
                ];
            }
            // Send data to DataTables
            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('total_fee', function ($row) {
                    return '₹' . number_format($row['total_fee'], 2);
                })
                ->editColumn('paid_fee', function ($row) {
                    return '₹' . number_format($row['paid_fee'], 2);
                })
                ->editColumn('pending_fee', function ($row) {
                    return '₹' . number_format($row['pending_fee'], 2);
                })
                ->addColumn('action', function ($row) use ($modules, $student_id) {
                    if ($row['pending_fee'] == 0) {
                        // extract only number from "Semester 1"
                        $semesterNumber = preg_replace('/\D/', '', $row['year_semester']);

                        $url = route($modules['route'] . '.print', [
                            'student_id' => $student_id,
                            'semester'   => $semesterNumber,
                        ]);

                        return '<a href="' . $url . '" class="btn btn-sm btn-primary" target="_blank">Print</a>';
                    }
                    return '-';
                })


                ->rawColumns(['action'])


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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        // dd("show method called with id: $id");
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
            $student_id = $request->search_id;
            $course_id = $request->course_id;

            $courseRegistration = CourceRegistration::where('register_id', $student_id)
                ->where('course_id', $course_id)
                ->with('course')
                ->first();

            if (!$courseRegistration || !$courseRegistration->course) {
                return back()->withErrors('Course or student not found.');
            }

            $semester_fee = ($courseRegistration->course->course_fees && $courseRegistration->course->semester)
                ? $courseRegistration->course->course_fees / $courseRegistration->course->semester
                : 0;

            $feesCollections = FeesCollection::where('admission_id', $student_id)
                ->where('course_id', $course_id)
                ->get();

            $semesterPaid = [];
            foreach ($feesCollections as $fee) {
                $sem = $fee->year_semester;
                $semesterPaid[$sem] = ($semesterPaid[$sem] ?? 0) + $fee->fees;
            }

            $data = [];
            $total_semesters = $courseRegistration->course->semester;

            for ($sem = 1; $sem <= $total_semesters; $sem++) {
                $paid = $semesterPaid[$sem] ?? 0;
                $pending = $semester_fee - $paid;

                $data[] = [
                    'semester' => "Semester $sem",
                    'total_fee' => number_format($semester_fee, 2),
                    'paid_fee' => number_format($paid, 2),
                    'pending_fee' => number_format(max($pending, 0), 2),
                ];
            }

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();


            $sheet->setCellValue('A1', 'Semester');
            $sheet->setCellValue('B1', 'Total Fee');
            $sheet->setCellValue('C1', 'Paid Fee');
            $sheet->setCellValue('D1', 'Pending Fee');

            $row = 2;

            foreach ($data as $item) {
                $sheet->setCellValue('A' . $row, $item['semester']);
                $sheet->setCellValue('B' . $row, $item['total_fee']);
                $sheet->setCellValue('C' . $row, $item['paid_fee']);
                $sheet->setCellValue('D' . $row, $item['pending_fee']);
                $row++;
            }

            $writer = new Xlsx($spreadsheet);

            // Laravel stream response - safe and clean
            $response = new StreamedResponse(function () use ($writer) {
                $writer->save('php://output');
            });

            $fileName = 'Student_Fees_Report_Filtered.xlsx';

            $disposition = $response->headers->makeDisposition(
                \Symfony\Component\HttpFoundation\ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                $fileName
            );

            $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            $response->headers->set('Content-Disposition', $disposition);

            return $response;
        } catch (\Exception $e) {
            Log::error('Export Excel Error: ' . $e->getMessage());
            return back()->withErrors('Something went wrong while exporting the Excel.');
        }
    }

    public function printView(Request $request)
    {
        $studentId = $request->student_id;
        $semester  = $request->semester;

        // Course registration + course
        $courseRegistration = CourceRegistration::where('register_id', $studentId)
            ->with(['course', 'batch'])
            ->first();

        if (!$courseRegistration || !$courseRegistration->course) {
            abort(404, 'Course not found');
        }

        // Per semester fee
        $semester_fee = ($courseRegistration->course->course_fees && $courseRegistration->course->semester)
            ? $courseRegistration->course->course_fees / $courseRegistration->course->semester
            : 0;

        // Paid fees for this semester
       $paidFee = FeesCollection::where('student_id', $studentId)   // FIXED

            ->where('course_id', $courseRegistration->course_id)
            ->where('year_semester', $semester)
            ->sum('fees');

        $pendingFee = max($semester_fee - $paidFee, 0);

        // --- Fetch or Create Receipt ---
        $receipt = FeesReceipt::where('student_id', $studentId)
            ->where('semester', $semester)
            ->first();

        if (!$receipt) {
            $lastReceipt = FeesReceipt::withTrashed()->orderBy('id', 'desc')->first();
            $nextNumber = 1;
            if ($lastReceipt && preg_match('/PVM-(\d+)/', $lastReceipt->receipt_no, $matches)) {
                $nextNumber = (int)$matches[1] + 1;
            }

            $receiptNo = 'PVM-' . str_pad($nextNumber, 2, '0', STR_PAD_LEFT);

            $receipt = FeesReceipt::create([
                'student_id' => $studentId,
                'semester' => $semester,
                'receipt_no' => $receiptNo,
            ]);
        }

        $feesDetails = MasterFeeDetail::select('fee_name as name', 'amount')->get()->toArray();


        // Get one fees record for student info
      $feesData = FeesCollection::with(['admission', 'course'])
    ->where('student_id', $studentId)   // FIXED
    ->where('year_semester', $semester)
    ->first();


        if (!$feesData) {
            abort(404, 'No fees paid yet for this semester');
        }



        return view('software.module.students-fees-report.print', compact(
            'feesData',
            'feesDetails',
            'semester_fee',
            'paidFee',
            'pendingFee',
            'receipt',
            'courseRegistration'
             // <-- Pass receipt here
        ))->with('modules', $this->modules);
    }
}
