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
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Yajra\DataTables\Facades\DataTables;

class FeesPendingReportController extends Controller
{
    public $modules = [];

    public function __construct()
    {
        $this->modules = [
            'title' => 'Fees Pending Report',
            'folder_path' => 'software.module.fees-pending-report',
            'route' => 'fees-pending-report',
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
                        $reg->pending_fees = $pending;
                        $students[] = $reg;
                    }
                }
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
                    return trim("{$first} {$last} {$father}");
                })
                ->addColumn('pending_fees', function ($row) {
                    return $row->pending_fees ?? 0;
                })
                ->make(true);
        }

        $columns = [
            (object)['data' => 'DT_RowIndex', 'name' => 'id', 'td_label' => 'No', 'orderable' => false, 'searchable' => false],
            (object)['data' => 'student_name', 'name' => 'student_name', 'td_label' => 'Student Name'],
            (object)['data' => 'pending_fees', 'name' => 'pending_fees', 'td_label' => 'Pending Fees'],
        ];

        return view($modules['folder_path'] . '.index', compact('columns'));
    }

    public function printList(Request $request)
    {
        $result = $this->getPendingStudents(
            $request->course_id,
            $request->batch_id,
            $request->semester_id
        );

        return view('software.module.fees-pending-report.print', [
            'students' => $result['students'],
            'course' => $result['course'],
            'batch' => $result['batch'],
            'semester_id' => $request->semester_id,
        ]);
    }

    public function exportExcel(Request $request)
    {
        $result = $this->getPendingStudents(
            $request->course_id,
            $request->batch_id,
            $request->semester_id
        );

        $students = $result['students'];
        $course = $result['course'];
        $batch = $result['batch'];
        $semester_id = $request->semester_id;

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'Fees Pending Report');
        $sheet->mergeCells('A1:C1');

        $sheet->setCellValue(
            'A2',
            sprintf(
                '%s - %s | Sem - %s',
                $course?->course_name ?? 'Course',
                $batch?->batch_name ?? 'Batch',
                $semester_id ?? '-'
            )
        );
        $sheet->mergeCells('A2:C2');

        $sheet->setCellValue('A4', 'No');
        $sheet->setCellValue('B4', 'Student Name');
        $sheet->setCellValue('C4', 'Pending Fees');

        $row = 5;
        foreach ($students as $index => $student) {
            $first = $student->admission->first_name ?? '';
            $last = $student->admission->last_name ?? '';
            $father = $student->admission->father_name ?? '';

            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, trim("{$first} {$last} {$father}"));
            $sheet->setCellValue('C' . $row, number_format($student->pending_fees ?? 0, 2));
            $row++;
        }

        $lastRow = max($row - 1, 4);

        $sheet->getStyle('A1:C1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A2:C2')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A4:C4')->getFont()->setBold(true);

        $sheet->getStyle('A1:C2')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getStyle('A4:C' . $lastRow)->getAlignment()
            ->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getStyle('A4:A' . $lastRow)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getStyle('C4:C' . $lastRow)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        $sheet->getStyle('A4:C' . $lastRow)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        $sheet->getRowDimension(1)->setRowHeight(24);
        $sheet->getRowDimension(2)->setRowHeight(22);
        $sheet->getColumnDimension('A')->setWidth(8);
        $sheet->getColumnDimension('B')->setWidth(40);
        $sheet->getColumnDimension('C')->setWidth(18);

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Fees_Pending_Report.xlsx';

        $response = new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        });

        $disposition = $response->headers->makeDisposition(
            \Symfony\Component\HttpFoundation\ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $fileName
        );

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    private function getPendingStudents($course_id, $batch_id, $semester_id): array
    {
        $course = MasterCourse::find($course_id);
        $batch = MasterBatch::find($batch_id);

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
                    $reg->pending_fees = $pending;
                    $students[] = $reg;
                }
            }
        } else {
            $feesCollections = FeesCollection::whereIn('student_id', $allStudents->pluck('register_id'))
                ->get()
                ->groupBy('student_id');

            foreach ($allStudents as $reg) {
                $courseFee = $reg->course->course_fees ?? 0;
                $studentFees = $feesCollections->get($reg->register_id, collect());
                $paid = $studentFees->sum('fees');
                $pending = max($courseFee - $paid, 0);

                if ($pending > 0) {
                    $reg->pending_fees = $pending;
                    $students[] = $reg;
                }
            }
        }

        $students = collect($students)->sortBy(function ($reg) {
            return $reg->admission->first_name ?? '';
        })->values();

        return [
            'students' => $students,
            'course' => $course,
            'batch' => $batch,
        ];
    }
}
