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
            (object) ['data' => 'installments', 'name' => 'installments', 'td_label' => 'Installment', 'orderable' => false, 'searchable' => false],
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

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'Fees Collection Report');
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
        $sheet->setCellValue('C4', 'Installment');

        $row = 5;
        foreach ($students as $index => $student) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $this->formatStudentName($student->admission));
            $sheet->setCellValue('C' . $row, $this->formatAllInstallments($student->installments));
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
            ->setVertical(Alignment::VERTICAL_TOP);

        $sheet->getStyle('A4:A' . $lastRow)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getStyle('B4:B' . $lastRow)->getAlignment()
            ->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getStyle('C4:C' . $lastRow)->getAlignment()
            ->setWrapText(true)
            ->setVertical(Alignment::VERTICAL_TOP);

        $sheet->getStyle('A4:C' . $lastRow)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        $sheet->getRowDimension(1)->setRowHeight(24);
        $sheet->getRowDimension(2)->setRowHeight(22);
        $sheet->getColumnDimension('A')->setWidth(8);
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

    private function formatFeeDate($date): string
    {
        if (!$date) {
            return '';
        }

        try {
            return Carbon::parse($date)->format('d-m-Y');
        } catch (\Exception $e) {
            return (string) $date;
        }
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

        return 'Count=' . $items->count() . ' ' . $items->implode(' ,');
    }

    private function formatInstallment($installment): string
    {
        if (!$installment) {
            return '';
        }

        $amount = number_format($installment->fees ?? 0, 2);
        $date = $this->formatFeeDate($installment->date);

        if ($amount === '' && $date === '') {
            return '';
        }

        return '{Amount= ' . $amount . ' Date=' . $date . '}';
    }
}
