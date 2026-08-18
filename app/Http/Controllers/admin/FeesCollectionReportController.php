<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CourceRegistration;
use App\Models\Master\MasterCourse;
use App\Models\Master\MasterBatch;
use App\Models\FeesCollection;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
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

            $result = $this->getCollectionReportStudents($course_id, $batch_id, $semester_id);
            $students = $result['students'];

            return Datatables::of($students)
                ->addIndexColumn()
                ->addColumn('student_name', function ($row) {
                    return $this->formatStudentName($row->admission);
                })
                ->addColumn('installments', function ($row) {
                    return $this->formatAllInstallments($row->installments);
                })
                ->make(true);
        }

        $columns = [
            (object) ['data' => 'DT_RowIndex', 'name' => 'id', 'td_label' => 'No', 'orderable' => false, 'searchable' => false],
            (object) ['data' => 'student_name', 'name' => 'student_name', 'td_label' => 'Student Name'],
            (object) ['data' => 'installments', 'name' => 'installments', 'td_label' => 'Installments', 'orderable' => false, 'searchable' => false],
        ];

        return view($modules['folder_path'] . '.index', compact('columns'));
    }

    public function printList(Request $request)
    {
        $result = $this->getCollectionReportStudents(
            $request->course_id,
            $request->batch_id,
            $request->semester_id
        );

        return view('software.module.fees-collection-report.print', [
            'students' => $result['students'],
            'course' => $result['course'],
            'batch' => $result['batch'],
            'semester_id' => $request->semester_id,
        ]);
    }

    public function exportExcel(Request $request)
    {
        $result = $this->getCollectionReportStudents(
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
            'Fees Collection Report | ' . $reportSubtitle
        );

        $sheet->setCellValue('A' . $tableHeaderRow, 'No');
        $sheet->setCellValue('B' . $tableHeaderRow, 'Student Name');
        $sheet->setCellValue('C' . $tableHeaderRow, 'Installments');

        $row = $tableHeaderRow + 1;
        foreach ($students as $index => $student) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, strtoupper($this->formatStudentName($student->admission)));
            $sheet->setCellValue('C' . $row, $this->formatAllInstallments($student->installments));
            $row++;
        }

        $lastRow = max($row - 1, $tableHeaderRow);

        $sheet->getStyle('A' . $tableHeaderRow . ':C' . $tableHeaderRow)->getFont()->setBold(true);

        $sheet->getStyle('A' . $tableHeaderRow . ':C' . $lastRow)->getAlignment()
            ->setVertical(Alignment::VERTICAL_TOP);

        $sheet->getStyle('A' . $tableHeaderRow . ':A' . $lastRow)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getStyle('B' . $tableHeaderRow . ':B' . $lastRow)->getAlignment()
            ->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getStyle('C' . $tableHeaderRow . ':C' . $lastRow)->getAlignment()
            ->setWrapText(true)
            ->setVertical(Alignment::VERTICAL_TOP);

        $sheet->getStyle('A' . $tableHeaderRow . ':C' . $lastRow)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        $sheet->getColumnDimension('A')->setWidth(10);
        $sheet->getColumnDimension('B')->setWidth(40);
        $sheet->getColumnDimension('C')->setWidth(85);

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Fees_Collection_Report.xlsx';

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

    private function getCollectionReportStudents($course_id, $batch_id, $semester_id): array
    {
        $course = MasterCourse::find($course_id);
        $batch = MasterBatch::find($batch_id);
        $students = collect();
        $maxInstallments = 0;

        if (!$course_id || !$batch_id || !$semester_id) {
            return [
                'students' => $students,
                'maxInstallments' => 0,
                'course' => $course,
                'batch' => $batch,
            ];
        }

        $sub = CourceRegistration::select(DB::raw('MAX(id) as id'))
            ->groupBy('register_id');

        $allStudents = CourceRegistration::whereIn('id', $sub)
            ->whereNotIn('status', ['Cancel', 'cancel'])
            ->with(['admission', 'course'])
            ->where('course_id', $course_id)
            ->where('batch_id', $batch_id)
            ->get();

        $feesCollections = FeesCollection::whereIn('student_id', $allStudents->pluck('register_id'))
            ->where('course_id', $course_id)
            ->get()
            ->groupBy('student_id');

        $reportStudents = [];

        foreach ($allStudents as $reg) {
            if ($reg->is_lateral_entry == 1 && $semester_id < $reg->joining_semester) {
                continue;
            }
            $installments = $this->getSemesterInstallments(
                $reg->register_id,
                $semester_id,
                $feesCollections
            );

            $reg->installments = $installments;
            $reportStudents[] = $reg;
            $maxInstallments = max($maxInstallments, $installments->count());
        }

        $students = collect($reportStudents)
            ->sortBy(function ($reg) {
                return $reg->admission->first_name ?? '';
            })
            ->values();

        return [
            'students' => $students,
            'maxInstallments' => $maxInstallments,
            'course' => $course,
            'batch' => $batch,
        ];
    }

    private function getSemesterInstallments($studentId, $semesterId, Collection $feesCollectionsByStudent): Collection
    {
        $studentFees = $feesCollectionsByStudent->get($studentId, collect());

        return $studentFees
            ->filter(function ($fee) use ($semesterId) {
                return strtolower(trim((string) $fee->year_semester)) === strtolower(trim((string) $semesterId))
                    || trim((string) $fee->year_semester) === trim((string) $semesterId);
            })
            ->sortBy(function ($fee) {
                return $fee->date ?? $fee->created_at;
            })
            ->values();
    }

    private function formatStudentName($admission): string
    {
        if (!$admission) {
            return '';
        }

        $first = $admission->first_name ?? '';
        $last = $admission->last_name ?? '';
        $father = $admission->father_name ?? '';

        return trim("{$first} {$last} {$father}");
    }

    private function formatAllInstallments(Collection $installments): string
    {
        $items = $installments
            ->map(function ($installment) {
                return $this->formatInstallment($installment);
            })
            ->filter()
            ->values();

        if ($items->isEmpty()) {
            return '';
        }

        return $items->implode(',');
    }

    private function formatInstallment($installment): string
    {
        if (!$installment) {
            return '';
        }

        $amount = number_format((float) ($installment->fees ?? 0), 0, '', '');
        $date = $this->formatInstallmentDate($installment->date);

        if ($amount === '' && $date === '') {
            return '';
        }

        return $amount . '(' . $date . ')';
    }

    private function formatInstallmentDate($date): string
    {
        if (empty($date)) {
            return '';
        }

        try {
            return Carbon::parse($date)->format('j-n-Y');
        } catch (\Exception $e) {
            return (string) $date;
        }
    }

    private function applyExcelReportHeader($sheet, string $reportTitleLine): int
    {
        $logoPath = public_path('uploads/logo/report_logo.png');
        if (!is_file($logoPath)) {
            $logoPath = public_path('admin/assets/images/report_logo.png');
        }

        $logoCol = 'A';
        $textStartCol = 'B';
        $headerEndCol = 'C';
        $collegeRow = 1;
        $reportTitleRow = 2;

        $sheet->getColumnDimension($logoCol)->setWidth(13);
        $sheet->getColumnDimension('B')->setWidth(40);
        $sheet->getColumnDimension('C')->setWidth(85);

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
