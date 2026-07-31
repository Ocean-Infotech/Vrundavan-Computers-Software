<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Attedance;
use App\Models\Admission;
use App\Models\CourceRegistration;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yajra\DataTables\Facades\DataTables;

class AttedanceReportController extends Controller
{
    public $modules = [];

    public function __construct()
    {
        $this->modules = [
            'title' => 'Attendance Report',
            'folder_path' => 'software.module.attedance-report',
            'route' => 'attedance-report',
        ];
    }

    private function getAttendanceCollection(Request $request)
    {
        $startDateStr = $request->input('start_date') ?: date('Y-m-d');
        $endDateStr = $request->input('end_date') ?: date('Y-m-d');

        $startDate = Carbon::parse($startDateStr);
        $endDate = Carbon::parse($endDateStr);

        // Limit range to prevent memory overflow (e.g. max 31 days)
        if ($startDate->diffInDays($endDate) > 31) {
            $endDate = $startDate->copy()->addDays(31);
        }

        // Generate date list
        $dates = [];
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dates[] = $date->format('Y-m-d');
        }

        $totalDays = count($dates);

        // Fetch all active/mapped students
        $studentsQuery = Admission::whereNotNull('biometric_id')
            ->where('biometric_id', '!=', '');

        if ($request->filled('course_id') || $request->filled('batch_id') || $request->filled('class_id')) {
            $studentsQuery->whereHas('courses', function ($q) use ($request) {
                if ($request->filled('course_id')) {
                    $q->where('course_id', $request->course_id);
                }
                if ($request->filled('batch_id')) {
                    $q->where('batch_id', $request->batch_id);
                }
                if ($request->filled('class_id')) {
                    $q->where('class_id', $request->class_id);
                }
            });
        }

        $students = $studentsQuery->get();

        // Get all attendance logs for the range grouped by date and biometric ID
        $attendanceLogs = Attedance::whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get()
            ->groupBy(function ($item) {
                return $item->date . '_' . $item->biometric_id;
            });

        // Resolve all course registrations for student info mapping
        $studentIds = $students->pluck('id')->toArray();
        $registrations = CourceRegistration::whereIn('register_id', $studentIds)
            ->with(['course', 'batch', 'class'])
            ->get()
            ->keyBy('register_id');

        $data = [];
        foreach ($students as $student) {
            $present = 0;
            foreach ($dates as $d) {
                if (isset($attendanceLogs[$d . '_' . $student->biometric_id])) {
                    $present++;
                }
            }
            $absent = $totalDays - $present;

            $reg = $registrations->get($student->id);
            $cbc = '-';
            if ($reg) {
                $c = $reg->course->course_name ?? '';
                $b = $reg->batch->batch_name ?? '';
                $cl = $reg->class->class ?? '';
                $cbc = "{$c} - {$b} - {$cl}";
            }

            $data[] = [
                'biometric_id' => $student->biometric_id,
                'student_name' => strtoupper(trim("{$student->first_name} {$student->last_name} {$student->father_name}")),
                'course_batch_class' => $cbc,
                'present_days' => $present,
                'absent_days' => $absent,
            ];
        }

        return collect($data);
    }

    public function index(Request $request)
    {
        $modules = $this->modules;
        View::share('modules', $modules);

        if ($request->ajax()) {
            if (!$request->filled('start_date')) {
                return Datatables::of(collect([]))
                    ->with([
                        'present_count' => 0,
                        'absent_count' => 0,
                    ])
                    ->make(true);
            }
            $collection = $this->getAttendanceCollection($request);

            // Simple search filter if search value is passed by DataTable
            if ($request->filled('search.value')) {
                $search = strtolower($request->input('search.value'));
                $collection = $collection->filter(function ($item) use ($search) {
                    return str_contains(strtolower($item['student_name']), $search) ||
                        str_contains(strtolower($item['biometric_id']), $search) ||
                        str_contains(strtolower($item['course_batch_class']), $search);
                });
            }

            $presentCount = $collection->sum('present_days');
            $absentCount = $collection->sum('absent_days');

            return Datatables::of($collection)
                ->addIndexColumn()
                ->addColumn('present_badge', function ($row) {
                    return '<span class="badge bg-success">' . $row['present_days'] . '</span>';
                })
                ->addColumn('absent_badge', function ($row) {
                    return '<span class="badge bg-danger">' . $row['absent_days'] . '</span>';
                })
                ->rawColumns(['present_badge', 'absent_badge'])
                ->with([
                    'present_count' => $presentCount,
                    'absent_count' => $absentCount,
                ])
                ->make(true);
        }

        $columns = [
            (object) ['data' => 'DT_RowIndex', 'name' => 'id', 'td_label' => 'No', 'orderable' => false, 'searchable' => false],
            (object) ['data' => 'biometric_id', 'name' => 'biometric_id', 'td_label' => 'Biometric ID'],
            (object) ['data' => 'student_name', 'name' => 'student_name', 'td_label' => 'Student Name'],
            (object) ['data' => 'course_batch_class', 'name' => 'course_batch_class', 'td_label' => 'Course - Batch - Class', 'orderable' => false, 'searchable' => false],
            (object) ['data' => 'present_badge', 'name' => 'present_days', 'td_label' => 'Present', 'orderable' => false, 'searchable' => false],
            (object) ['data' => 'absent_badge', 'name' => 'absent_days', 'td_label' => 'Absent', 'orderable' => false, 'searchable' => false],
        ];

        return view($modules['folder_path'] . '.index', compact('columns'));
    }

    public function printList(Request $request)
    {
        $collection = $this->getAttendanceCollection($request);

        return view('software.module.attedance-report.print', [
            'records' => $collection,
        ]);
    }

    public function exportExcel(Request $request)
    {
        $records = $this->getAttendanceCollection($request);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Attendance Report');

        // Heading
        $sheet->mergeCells('A1:F1');
        $sheet->setCellValue('A1', 'Attendance Report');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $headerRow = 3;

        // Table Headers
        $headers = ['No', 'Biometric ID', 'Student Name', 'Course - Batch - Class', 'Present Days', 'Absent Days'];
        foreach ($headers as $colIndex => $header) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->setCellValue($colLetter . $headerRow, $header);
        }

        $sheet->getStyle('A' . $headerRow . ':F' . $headerRow)->getFont()->setBold(true);
        $sheet->getStyle('A' . $headerRow . ':F' . $headerRow)->getFill()
            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFDCBEAF');
        $sheet->getStyle('A' . $headerRow . ':F' . $headerRow)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $rowNumber = $headerRow + 1;
        foreach ($records as $index => $record) {
            $sheet->setCellValue('A' . $rowNumber, $index + 1);
            $sheet->setCellValue('B' . $rowNumber, $record['biometric_id'] ?: '-');
            $sheet->setCellValue('C' . $rowNumber, $record['student_name']);
            $sheet->setCellValue('D' . $rowNumber, $record['course_batch_class']);
            $sheet->setCellValue('E' . $rowNumber, $record['present_days']);
            $sheet->setCellValue('F' . $rowNumber, $record['absent_days']);

            $rowNumber++;
        }

        // Add summary row at the bottom
        $totalPresent = $records->sum('present_days');
        $totalAbsent = $records->sum('absent_days');

        $sheet->setCellValue('B' . $rowNumber, 'Total Present:');
        $sheet->setCellValue('C' . $rowNumber, $totalPresent);
        $sheet->setCellValue('E' . $rowNumber, 'Total Absent:');
        $sheet->setCellValue('F' . $rowNumber, $totalAbsent);
        $sheet->getStyle('B' . $rowNumber . ':F' . $rowNumber)->getFont()->setBold(true);

        $lastRow = $rowNumber;
        $sheet->getStyle('A' . $headerRow . ':F' . $lastRow)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A' . ($headerRow + 1) . ':B' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('E' . ($headerRow + 1) . ':F' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getColumnDimension('A')->setWidth(8);
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getColumnDimension('C')->setWidth(30);
        $sheet->getColumnDimension('D')->setWidth(35);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(15);

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 'attendance-report.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
