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
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
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
                ->whereNotIn('status', ['Cancel', 'cancel'])
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
                    if ($reg->is_lateral_entry == 1 && $semester_id < $reg->joining_semester) {
                        continue;
                    }
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
                ->addColumn('student_name', function ($row) use ($semester_id) {
                    $first = $row->admission->first_name ?? '';
                    $last = $row->admission->last_name ?? '';
                    $father = $row->admission->father_name ?? '';
                    $fullName = trim("{$first} {$last} {$father}");
                    return '<a href="javascript:void(0);" class="pending-fee-link" data-student-id="' . $row->register_id . '" data-semester="' . $semester_id . '">' . $fullName . '</a>';
                })
                ->addColumn('pending_fees', function ($row) {
                    return $row->pending_fees ?? 0;
                })
                ->rawColumns(['student_name'])
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

        $reportSubtitle = sprintf(
            '%s - %s | Sem - %s',
            $course?->course_name ?? 'Course',
            $batch?->batch_name ?? 'Batch',
            $semester_id ?? '-'
        );

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $tableHeaderRow = $this->applyExcelReportHeader(
            $sheet,
            'Fees Pending Report | ' . $reportSubtitle
        );

        $sheet->setCellValue('A' . $tableHeaderRow, 'No');
        $sheet->setCellValue('B' . $tableHeaderRow, 'Student Name');
        $sheet->mergeCells('B' . $tableHeaderRow . ':C' . $tableHeaderRow);
        $sheet->setCellValue('D' . $tableHeaderRow, 'Pending Fees');

        $row = $tableHeaderRow + 1;
        foreach ($students as $index => $student) {
            $first = $student->admission->first_name ?? '';
            $last = $student->admission->last_name ?? '';
            $father = $student->admission->father_name ?? '';

            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, strtoupper(trim("{$first} {$last} {$father}")));
            $sheet->mergeCells('B' . $row . ':C' . $row);
            $sheet->setCellValue('D' . $row, number_format($student->pending_fees ?? 0, 2));
            $row++;
        }

        $lastRow = max($row - 1, $tableHeaderRow);

        $sheet->getStyle('A' . $tableHeaderRow . ':D' . $tableHeaderRow)->getFont()->setBold(true);

        $sheet->getStyle('A' . $tableHeaderRow . ':D' . $lastRow)->getAlignment()
            ->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getStyle('A' . $tableHeaderRow . ':A' . $lastRow)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getStyle('D' . $tableHeaderRow . ':D' . $lastRow)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        $sheet->getStyle('A' . $tableHeaderRow . ':D' . $lastRow)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        $sheet->getColumnDimension('A')->setWidth(10);
        $sheet->getColumnDimension('B')->setWidth(24);
        $sheet->getColumnDimension('C')->setWidth(24);
        $sheet->getColumnDimension('D')->setWidth(16);

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
            ->whereNotIn('status', ['Cancel', 'cancel'])
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
                if ($reg->is_lateral_entry == 1 && $semester_id < $reg->joining_semester) {
                    continue;
                }
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
                $courseFee = CourceRegistration::calculateTotalFee($reg);
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

    private function applyExcelReportHeader($sheet, string $reportTitleLine): int
    {
        $logoPath = public_path('uploads/logo/report_logo.png');
        if (!is_file($logoPath)) {
            $logoPath = public_path('admin/assets/images/report_logo.png');
        }

        $logoCol = 'A';
        $textStartCol = 'B';
        $headerEndCol = 'D';
        $collegeRow = 1;
        $reportTitleRow = 2;

        $sheet->getColumnDimension($logoCol)->setWidth(13);
        $sheet->getColumnDimension('B')->setWidth(26);
        $sheet->getColumnDimension('C')->setWidth(26);
        $sheet->getColumnDimension('D')->setWidth(18);

        $sheet->mergeCells($logoCol . $collegeRow . ':' . $logoCol . $reportTitleRow);

        $sheet->setCellValue($textStartCol . $collegeRow, 'Shree Patel Vidhya Mandir Science College');
        $sheet->mergeCells($textStartCol . $collegeRow . ':' . $headerEndCol . $collegeRow);

        $sheet->setCellValue($textStartCol . $reportTitleRow, $reportTitleLine);
        $sheet->mergeCells($textStartCol . $reportTitleRow . ':' . $headerEndCol . $reportTitleRow);

        $sheet->getRowDimension($collegeRow)->setRowHeight(34);
        $sheet->getRowDimension($reportTitleRow)->setRowHeight(28);

        $sheet->getStyle($textStartCol . $collegeRow . ':' . $headerEndCol . $collegeRow)->getFont()
            ->setBold(true)
            ->setSize(16);
        $sheet->getStyle($textStartCol . $collegeRow . ':' . $headerEndCol . $collegeRow)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getStyle($textStartCol . $reportTitleRow . ':' . $headerEndCol . $reportTitleRow)->getFont()
            ->setBold(true)
            ->setSize(13);
        $sheet->getStyle($textStartCol . $reportTitleRow . ':' . $headerEndCol . $reportTitleRow)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getStyle($logoCol . $collegeRow . ':' . $logoCol . $reportTitleRow)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        if (is_file($logoPath)) {
            $drawing = new Drawing();
            $drawing->setName('College Logo');
            $drawing->setDescription('College Logo');
            $drawing->setPath($logoPath);
            $drawing->setResizeProportional(true);
            $drawing->setHeight(72);
            $drawing->setWidth(72);
            $drawing->setCoordinates($logoCol . '1');
            $drawing->setOffsetX(8);
            $drawing->setOffsetY(6);
            $drawing->setWorksheet($sheet);
        }

        return $reportTitleRow + 1;
    }
}
