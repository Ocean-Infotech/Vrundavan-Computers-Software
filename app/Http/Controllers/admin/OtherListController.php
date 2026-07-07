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
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Yajra\DataTables\Facades\DataTables;

class OtherListController extends Controller
{
    public $modules = [];

    public function __construct()
    {
        $this->modules = [
            'title' => 'Other List',
            'folder_path' => 'software.module.other-list',
            'route' => 'other-list',
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
                ->addColumn('student_name', function ($row) {
                    return $row->admission->full_name ?? '-';
                })
                ->addColumn('gender', function ($row) {
                    return $row->admission->gender ?? '-';
                })
                ->addColumn('fees', function ($row) {
                    return '';
                })
                ->addColumn('note', function ($row) {
                    return '';
                })
                ->make(true);
        }

        $columns = [
            (object) ['data' => 'DT_RowIndex', 'name' => 'id', 'td_label' => 'No', 'orderable' => false, 'searchable' => false],
            (object) ['data' => 'student_name', 'name' => 'student_name', 'td_label' => 'Name'],
            (object) ['data' => 'gender', 'name' => 'gender', 'td_label' => 'Gender'],
            (object) ['data' => 'fees', 'name' => 'fees', 'td_label' => 'Fees', 'orderable' => false, 'searchable' => false],
            (object) ['data' => 'note', 'name' => 'note', 'td_label' => 'Note', 'orderable' => false, 'searchable' => false],
        ];

        return view($modules['folder_path'] . '.index', compact('columns'));
    }

    public function printList(Request $request)
    {
        $this->authorizeAccess();

        $course_id = $request->course_id;
        $batch_id = $request->batch_id;
        $semester_id = $request->semester_id;
        $page_heading = trim((string) $request->page_heading);

        $course = MasterCourse::find($course_id);
        $batch = MasterBatch::find($batch_id);
        $students = collect();

        if ($course_id && $batch_id && $semester_id) {
            $students = $this->getStudents($course_id, $batch_id);
        }

        $this->storeHistory($request, 'print', $students->count(), 'other-list');

        $blankRows = max(5, 10 - $students->count());

        return view('software.module.other-list.print', compact(
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

        $course = MasterCourse::find($course_id);
        $batch = MasterBatch::find($batch_id);
        $students = collect();

        if ($course_id && $batch_id && $semester_id) {
            $students = $this->getStudents($course_id, $batch_id);
        }

        $this->storeHistory($request, 'excel', $students->count(), 'other-list');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', $page_heading ?: 'Other List');
        $sheet->mergeCells('A1:E1');

        $sheet->setCellValue(
            'A2',
            sprintf(
                '%s - %s | Semester - %s',
                $course?->course_name ?? 'Course',
                $batch?->batch_name ?? 'Batch',
                $semester_id ?? '-'
            )
        );
        $sheet->mergeCells('A2:E2');

        $sheet->setCellValue('A4', 'No');
        $sheet->setCellValue('B4', 'Name');
        $sheet->setCellValue('C4', 'Gender');
        $sheet->setCellValue('D4', 'Fees');
        $sheet->setCellValue('E4', 'Note');

        $row = 5;
        foreach ($students as $index => $student) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $student->admission->full_name ?? '-');
            $sheet->setCellValue('C' . $row, $student->admission->gender ?? '-');
            $sheet->setCellValue('D' . $row, '');
            $sheet->setCellValue('E' . $row, '');
            $row++;
        }

        $lastRow = $row - 1;

        $sheet->getStyle('A1:E1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A2:E2')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A4:E4')->getFont()->setBold(true);

        $sheet->getStyle('A1:E2')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getStyle('A4:E' . $lastRow)->getAlignment()
            ->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getStyle('A4:A' . $lastRow)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getStyle('C4:E' . $lastRow)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getStyle('A4:E' . $lastRow)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        $sheet->getRowDimension(1)->setRowHeight(24);
        $sheet->getRowDimension(2)->setRowHeight(22);
        $sheet->getColumnDimension('A')->setWidth(8);
        $sheet->getColumnDimension('B')->setWidth(35);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(30);

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Other_List.xlsx';

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
        if (!Helper::directCan('other-list-list')) {
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

    private function getStudents($course_id, $batch_id)
    {
        $sub = CourceRegistration::select(DB::raw('MAX(id) as id'))
            ->groupBy('register_id');

        return CourceRegistration::whereIn('id', $sub)
            ->with('admission')
            ->where('course_id', $course_id)
            ->where('batch_id', $batch_id)
            ->get()
            ->filter(function ($registration) {
                return !is_null($registration->admission);
            })
            ->sortBy(function ($registration) {
                return $registration->admission->first_name ?? '';
            })
            ->values();
    }
}
