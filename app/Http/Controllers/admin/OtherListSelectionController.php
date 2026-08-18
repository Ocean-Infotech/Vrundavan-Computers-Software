<?php

namespace App\Http\Controllers\admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\CourceRegistration;
use App\Models\Master\MasterBatch;
use App\Models\Master\MasterCourse;
use App\Models\OtherListHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Yajra\DataTables\Facades\DataTables;

class OtherListSelectionController extends Controller
{
    public $modules = [];

    public function __construct()
    {
        $this->modules = [
            'title' => 'Other List Selection',
            'folder_path' => 'software.module.other-list-selection',
            'route' => 'other-list-selection',
        ];
    }

    public function index(Request $request)
    {
        $this->authorizeAccess();

        $modules = $this->modules;
        View::share('modules', $modules);

        if ($request->ajax()) {
            $course_id = $request->course_id;
            $batch_id = $request->batch_id;
            $semester_id = $request->semester_id;

            if (!$course_id || !$batch_id || !$semester_id) {
                return DataTables::of(collect())->make(true);
            }

            $students = $this->getStudents($course_id, $batch_id);

            return DataTables::of($students)
                ->addIndexColumn()
                ->addColumn('select', function ($row) {
                    return '<input type="checkbox" class="student-checkbox" value="' . $row->register_id . '">';
                })
                ->addColumn('student_name', function ($row) {
                    return $row->admission->first_name . ' ' . $row->admission->last_name . ' ' . $row->admission->father_name ?? '-';
                })
                ->addColumn('gender', function ($row) {
                    return $row->admission->gender ?? '-';
                })
                ->rawColumns(['select'])
                ->make(true);
        }

        $columns = [
            (object) ['data' => 'select', 'name' => 'select', 'td_label' => '<input type="checkbox" id="selectAllStudents" title="Select All">', 'orderable' => false, 'searchable' => false, 'className' => 'text-center'],
            (object) ['data' => 'DT_RowIndex', 'name' => 'id', 'td_label' => 'No', 'orderable' => false, 'searchable' => false],
            (object) ['data' => 'student_name', 'name' => 'student_name', 'td_label' => 'Name'],
            (object) ['data' => 'gender', 'name' => 'gender', 'td_label' => 'Gender'],
        ];

        return view($modules['folder_path'] . '.index', compact('columns'));
    }

    public function getStudentOptions(Request $request)
    {
        $this->authorizeAccess();

        $course_id = $request->course_id;
        $batch_id = $request->batch_id;

        if (!$course_id || !$batch_id) {
            return response()->json([
                'status' => true,
                'data' => [],
            ]);
        }

        $students = $this->getStudents($course_id, $batch_id)
            ->map(function ($registration) {
                return [
                    'id' => $registration->register_id,
                    'name' => $registration->admission->full_name ?? '-',
                ];
            })
            ->values();

        return response()->json([
            'status' => true,
            'data' => $students,
        ]);
    }

    public function printList(Request $request)
    {
        $this->authorizeAccess();

        $course_id = $request->course_id;
        $batch_id = $request->batch_id;
        $semester_id = $request->semester_id;
        $page_heading = trim((string) $request->page_heading);
        $studentIds = $request->input('student_ids', []);

        $course = MasterCourse::find($course_id);
        $batch = MasterBatch::find($batch_id);
        $students = collect();

        if ($course_id && $batch_id && $semester_id && $this->hasStudentSelection($studentIds)) {
            $students = $this->getStudents($course_id, $batch_id, $studentIds);
        }

        $this->storeHistory($request, 'print', $students->count(), 'other-list-selection');

        $blankRows = max(5, 10 - $students->count());

        return view('software.module.other-list-selection.print', compact(
            'students',
            'course',
            'batch',
            'semester_id',
            'page_heading',
            'blankRows'
        ));
    }

    public function exportExcel(Request $request)
    {
        $this->authorizeAccess();

        $course_id = $request->course_id;
        $batch_id = $request->batch_id;
        $semester_id = $request->semester_id;
        $page_heading = trim((string) $request->page_heading);
        $studentIds = $request->input('student_ids', []);

        $course = MasterCourse::find($course_id);
        $batch = MasterBatch::find($batch_id);
        $students = collect();

        if ($course_id && $batch_id && $semester_id && $this->hasStudentSelection($studentIds)) {
            $students = $this->getStudents($course_id, $batch_id, $studentIds);
        }

        $this->storeHistory($request, 'excel', $students->count(), 'other-list-selection');

        $reportSubtitle = sprintf(
            '%s - %s | Sem - %s',
            $course?->course_name ?? 'Course',
            $batch?->batch_name ?? 'Batch',
            $semester_id ?? '-'
        );

        $reportTitleLine = ($page_heading ?: 'Other List Selection') . ' | ' . $reportSubtitle;

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $tableHeaderRow = $this->applyExcelReportHeader($sheet, $reportTitleLine);

        $sheet->setCellValue('A' . $tableHeaderRow, 'No');
        $sheet->setCellValue('B' . $tableHeaderRow, 'Name');
        $sheet->setCellValue('C' . $tableHeaderRow, 'Gender');
        $sheet->setCellValue('D' . $tableHeaderRow, 'Fees');
        $sheet->setCellValue('E' . $tableHeaderRow, 'Note');

        $row = $tableHeaderRow + 1;
        foreach ($students as $index => $student) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, strtoupper($student->admission->full_name ?? '-'));
            $sheet->setCellValue('C' . $row, $student->admission->gender ?? '-');
            $sheet->setCellValue('D' . $row, '');
            $sheet->setCellValue('E' . $row, '');
            $row++;
        }

        $lastRow = max($row - 1, $tableHeaderRow);

        $sheet->getStyle('A' . $tableHeaderRow . ':E' . $tableHeaderRow)->getFont()->setBold(true);

        $sheet->getStyle('A' . $tableHeaderRow . ':E' . $lastRow)->getAlignment()
            ->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getStyle('A' . $tableHeaderRow . ':A' . $lastRow)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getStyle('C' . $tableHeaderRow . ':E' . $lastRow)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getStyle('A' . $tableHeaderRow . ':E' . $lastRow)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        $sheet->getColumnDimension('A')->setWidth(8);
        $sheet->getColumnDimension('B')->setWidth(35);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(30);

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Other_List_Selection.xlsx';

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

    private function authorizeAccess(): void
    {
        if (!Helper::directCan('other-list-selection-list')) {
            abort(403, 'User does not have the right permissions.');
        }
    }

    private function storeHistory(Request $request, string $actionType, int $studentCount, string $moduleCode): void
    {
        if (!Schema::hasTable('other_list_histories')) {
            return;
        }

        if (!$request->course_id || !$request->batch_id || !$request->semester_id) {
            return;
        }

        OtherListHistory::create([
            'module_code' => $moduleCode,
            'page_heading' => trim((string) $request->page_heading),
            'course_id' => $request->course_id,
            'batch_id' => $request->batch_id,
            'semester_id' => $request->semester_id,
            'action_type' => $actionType,
            'student_count' => $studentCount,
            'created_by' => optional(auth()->user())->id,
        ]);
    }

    private function getStudents($course_id, $batch_id, $studentIds = [])
    {
        $studentIds = $this->normalizeStudentIds($studentIds);

        $sub = CourceRegistration::select(DB::raw('MAX(id) as id'))
            ->groupBy('register_id');

        $query = CourceRegistration::whereIn('id', $sub)
            ->with('admission')
            ->where('course_id', $course_id)
            ->where('batch_id', $batch_id)
            ->whereNotIn('status', ['Cancel', 'cancel']);

        if (!empty($studentIds)) {
            $query->whereIn('register_id', $studentIds);
        }

        return $query
            ->get()
            ->filter(function ($registration) {
                return !is_null($registration->admission);
            })
            ->sortBy(function ($registration) {
                return $registration->admission->first_name ?? '';
            })
            ->values();
    }

    private function hasStudentSelection($studentIds): bool
    {
        if (!is_array($studentIds)) {
            $studentIds = array_filter(explode(',', (string) $studentIds));
        }

        $studentIds = array_values(array_filter($studentIds, function ($studentId) {
            return $studentId !== null && $studentId !== '';
        }));

        return count($studentIds) > 0;
    }

    private function normalizeStudentIds($studentIds): array
    {
        if (!is_array($studentIds)) {
            $studentIds = array_filter(explode(',', (string) $studentIds));
        }

        $studentIds = array_values(array_filter($studentIds, function ($studentId) {
            return $studentId !== null && $studentId !== '';
        }));

        if (in_array('all', $studentIds, true)) {
            return [];
        }

        return $studentIds;
    }

    private function applyExcelReportHeader($sheet, string $reportTitleLine): int
    {
        $logoPath = public_path('uploads/logo/report_logo.png');
        if (!is_file($logoPath)) {
            $logoPath = public_path('admin/assets/images/report_logo.png');
        }

        $logoCol = 'A';
        $textStartCol = 'B';
        $headerEndCol = 'E';
        $collegeRow = 1;
        $reportTitleRow = 2;

        $sheet->getColumnDimension($logoCol)->setWidth(13);
        $sheet->getColumnDimension('B')->setWidth(35);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(30);

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
