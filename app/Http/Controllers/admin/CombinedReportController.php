<?php

namespace App\Http\Controllers\admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\CourceRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\Facades\DataTables;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class CombinedReportController extends Controller
{
    public $modules = [];

    public function __construct()
    {
        $this->modules = [
            'title' => 'Combined Report',
            'folder_path' => 'software.module.combined-report',
            'route' => 'combined-report',
            'permission_prefix' => 'combined-report',
        ];
    }

    public function index(Request $request)
    {
        $modules = $this->modules;
        View::share('modules', $modules);
        return view($modules['folder_path'] . '.index');
    }

    public function getDataAjax(Request $request)
    {
        $courseId = $request->input('course_id');
        $batchId = $request->input('batch_id');
        $semester = $request->input('semester');

        if (empty($courseId) || empty($batchId)) {
            return response()->json([
                'success' => false,
                'students' => [],
                'weekly_tests' => [],
                'full_tests' => [],
                'assignments' => []
            ]);
        }

        // 1. Get all active & Running students
        $students = CourceRegistration::with(['admission'])
            ->where('course_id', $courseId)
            ->where('batch_id', $batchId)
            ->whereIn('status', ['active', 'Running'])
            ->get();

        // 2. Get all tests in this Course & Batch
        $allTestsQuery = \App\Models\Test::where('course_id', $courseId)
            ->where('batch_id', $batchId)
            ->whereNull('deleted_at');

        if (!empty($semester)) {
            $allTestsQuery->where('semester', $semester);
        }

        $allTests = $allTestsQuery->orderBy('date', 'asc')->get();

        // Group tests by test_type and subject_name
        $weeklyTests = [];
        $fullTests = [];

        foreach ($allTests as $test) {
            $type = strtolower($test->test_type ?? '');
            if (str_contains($type, 'weekly') || $test->test_type == 'Weekly') {
                $weeklyTests[$test->subject_name][] = [
                    'id' => $test->id,
                    'unit_name' => $test->unit_name,
                    'mark' => $test->mark
                ];
            } else {
                $fullTests[$test->subject_name][] = [
                    'id' => $test->id,
                    'unit_name' => $test->unit_name,
                    'mark' => $test->mark
                ];
            }
        }

        // 3. Get all assignments in this Course & Batch
        $allAssignmentsQuery = \App\Models\Assignment::where('course_id', $courseId)
            ->where('batch_id', $batchId)
            ->whereNull('deleted_at');

        if (!empty($semester)) {
            $semesterSubjects = $allTests->pluck('subject_name')->unique()->toArray();
            $allAssignmentsQuery->whereIn('subject', $semesterSubjects);
        }

        $allAssignments = $allAssignmentsQuery->get();

        // Group assignments by subject to get counts
        $assignmentsConfig = [];
        foreach ($allAssignments as $assignment) {
            if (!isset($assignmentsConfig[$assignment->subject])) {
                $assignmentsConfig[$assignment->subject] = 0;
            }
            $assignmentsConfig[$assignment->subject]++;
        }

        // 4. Fetch marks and reports for each student
        $studentData = [];
        foreach ($students as $student) {
            if (!$student->admission) continue;

            $registerId = $student->register_id; // Admission ID

            // Get test marks
            $studentMarks = \App\Models\StudentTestMark::where('register_id', $registerId)
                ->whereIn('test_id', $allTests->pluck('id'))
                ->get()
                ->keyBy('test_id');

            // Calculate test marks structure
            $weeklyMarks = [];
            foreach ($weeklyTests as $subject => $testsList) {
                $subjectTotal = 0;
                foreach ($testsList as $testInfo) {
                    $testId = $testInfo['id'];
                    $obtained = isset($studentMarks[$testId]) ? floatval($studentMarks[$testId]->marks) : null;
                    $weeklyMarks[$subject]['tests'][$testId] = ($obtained !== null) ? $obtained : '-';
                    if ($obtained !== null) {
                        $subjectTotal += $obtained;
                    }
                }
                $weeklyMarks[$subject]['total'] = $subjectTotal;
            }

            $fullMarks = [];
            foreach ($fullTests as $subject => $testsList) {
                $subjectTotal = 0;
                foreach ($testsList as $testInfo) {
                    $testId = $testInfo['id'];
                    $obtained = isset($studentMarks[$testId]) ? floatval($studentMarks[$testId]->marks) : null;
                    $fullMarks[$subject]['tests'][$testId] = ($obtained !== null) ? $obtained : '-';
                    if ($obtained !== null) {
                        $subjectTotal += $obtained;
                    }
                }
                $fullMarks[$subject]['total'] = $subjectTotal;
            }

            // Get completed assignment counts
            $assignmentCounts = [];
            foreach ($assignmentsConfig as $subject => $totalCount) {
                $grNo = $student->admission->gr_no ?? 0;
                $completed = \App\Models\StudentAssignmentReport::where('gr_no', $grNo)
                    ->whereIn('assignment_id', $allAssignments->where('subject', $subject)->pluck('id'))
                    ->where('status', 'complete')
                    ->count();

                $assignmentCounts[$subject] = $completed;
            }

            $studentData[] = [
                'gr_no' => $student->admission->gr_no ?? '-',
                'student_name' => trim(($student->admission->first_name ?? '') . ' ' . 
                                       ($student->admission->last_name ?? '') . ' ' . 
                                       ($student->admission->father_name ?? '')),
                'weekly_marks' => $weeklyMarks,
                'full_marks' => $fullMarks,
                'assignment_counts' => $assignmentCounts
            ];
        }

        return response()->json([
            'success' => true,
            'students' => $studentData,
            'weekly_tests' => $weeklyTests,
            'full_tests' => $fullTests,
            'assignments' => $assignmentsConfig
        ]);
    }

    public function exportExcel(Request $request)
    {
        $courseId = $request->input('course_id');
        $batchId = $request->input('batch_id');
        $semester = $request->input('semester');

        if (empty($courseId) || empty($batchId)) {
            return back()->withErrors('Course and Batch are required for export.');
        }

        // 1. Get Course and Batch Names for Title
        $courseName = \App\Models\Master\MasterCourse::where('id', $courseId)->value('course_name') ?? 'N/A';
        $batchName = \App\Models\Master\MasterBatch::where('id', $batchId)->value('batch_name') ?? 'N/A';

        // 2. Get students, tests, assignments
        $students = CourceRegistration::with(['admission'])
            ->where('course_id', $courseId)
            ->where('batch_id', $batchId)
            ->where('status', 'active')
            ->get();

        $allTestsQuery = \App\Models\Test::where('course_id', $courseId)
            ->where('batch_id', $batchId)
            ->whereNull('deleted_at');

        if (!empty($semester)) {
            $allTestsQuery->where('semester', $semester);
        }

        $allTests = $allTestsQuery->orderBy('date', 'asc')->get();

        $weeklyTests = [];
        $fullTests = [];
        foreach ($allTests as $test) {
            $type = strtolower($test->test_type ?? '');
            if (str_contains($type, 'weekly') || $test->test_type == 'Weekly') {
                $weeklyTests[$test->subject_name][] = $test;
            } else {
                $fullTests[$test->subject_name][] = $test;
            }
        }

        $allAssignmentsQuery = \App\Models\Assignment::where('course_id', $courseId)
            ->where('batch_id', $batchId)
            ->whereNull('deleted_at');

        if (!empty($semester)) {
            $semesterSubjects = $allTests->pluck('subject_name')->unique()->toArray();
            $allAssignmentsQuery->whereIn('subject', $semesterSubjects);
        }

        $allAssignments = $allAssignmentsQuery->get();

        $assignmentsConfig = [];
        foreach ($allAssignments as $assignment) {
            if (!isset($assignmentsConfig[$assignment->subject])) {
                $assignmentsConfig[$assignment->subject] = 0;
            }
            $assignmentsConfig[$assignment->subject]++;
        }

        // 3. Setup Spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Combined Report');

        // A1:A3 merged for Gr.no
        // B1:B3 merged for Student Name
        $sheet->mergeCells('A1:A3');
        $sheet->setCellValue('A1', 'Gr.no');
        $sheet->mergeCells('B1:B3');
        $sheet->setCellValue('B1', 'Student Name');

        // Calculate Colspans for Row 1
        $colIdx = 3; // Starts at column 3 (C)

        $weeklyColStart = $colIdx;
        $weeklyColspan = 0;
        foreach ($weeklyTests as $sub => $tests) {
            $weeklyColspan += count($tests) + 1;
        }
        if ($weeklyColspan > 0) {
            $endColIdx = $colIdx + $weeklyColspan - 1;
            $sheet->mergeCells($this->getColLetter($colIdx) . '1:' . $this->getColLetter($endColIdx) . '1');
            $sheet->setCellValue($this->getColLetter($colIdx) . '1', 'Weekly TEST Report');
            $colIdx += $weeklyColspan;
        }

        $fullColStart = $colIdx;
        $fullColspan = 0;
        foreach ($fullTests as $sub => $tests) {
            $fullColspan += count($tests) + 1;
        }
        if ($fullColspan > 0) {
            $endColIdx = $colIdx + $fullColspan - 1;
            $sheet->mergeCells($this->getColLetter($colIdx) . '1:' . $this->getColLetter($endColIdx) . '1');
            $sheet->setCellValue($this->getColLetter($colIdx) . '1', 'Full Test Report');
            $colIdx += $fullColspan;
        }

        $assignColStart = $colIdx;
        $assignColspan = count($assignmentsConfig);
        if ($assignColspan > 0) {
            $endColIdx = $colIdx + $assignColspan - 1;
            $sheet->mergeCells($this->getColLetter($colIdx) . '1:' . $this->getColLetter($endColIdx) . '1');
            $sheet->setCellValue($this->getColLetter($colIdx) . '1', 'Assignment Report');
            $colIdx += $assignColspan;
        }

        // Build Header Row 2 (Subjects)
        $colIdx = 3;
        foreach ($weeklyTests as $subject => $tests) {
            $span = count($tests) + 1;
            $endColIdx = $colIdx + $span - 1;
            $sheet->mergeCells($this->getColLetter($colIdx) . '2:' . $this->getColLetter($endColIdx) . '2');
            $sheet->setCellValue($this->getColLetter($colIdx) . '2', $subject);
            $colIdx += $span;
        }
        foreach ($fullTests as $subject => $tests) {
            $span = count($tests) + 1;
            $endColIdx = $colIdx + $span - 1;
            $sheet->mergeCells($this->getColLetter($colIdx) . '2:' . $this->getColLetter($endColIdx) . '2');
            $sheet->setCellValue($this->getColLetter($colIdx) . '2', $subject);
            $colIdx += $span;
        }
        foreach ($assignmentsConfig as $subject => $count) {
            $sheet->setCellValue($this->getColLetter($colIdx) . '2', $subject);
            $colIdx++;
        }

        // Build Header Row 3 (Units & Max Marks / Assignment Totals)
        $colIdx = 3;
        foreach ($weeklyTests as $subject => $tests) {
            $totalMax = 0;
            foreach ($tests as $test) {
                $sheet->setCellValue($this->getColLetter($colIdx) . '3', $subject . ' ' . $this->formatUnitName($test->unit_name) . ' (' . $test->mark . ')');
                $totalMax += floatval($test->mark);
                $colIdx++;
            }
            $sheet->setCellValue($this->getColLetter($colIdx) . '3', $subject . ' Total (' . $totalMax . ')');
            $colIdx++;
        }
        foreach ($fullTests as $subject => $tests) {
            $totalMax = 0;
            foreach ($tests as $test) {
                $sheet->setCellValue($this->getColLetter($colIdx) . '3', $subject . ' ' . $this->formatUnitName($test->unit_name) . ' (' . $test->mark . ')');
                $totalMax += floatval($test->mark);
                $colIdx++;
            }
            $sheet->setCellValue($this->getColLetter($colIdx) . '3', $subject . ' Total (' . $totalMax . ')');
            $colIdx++;
        }
        foreach ($assignmentsConfig as $subject => $count) {
            $sheet->setCellValue($this->getColLetter($colIdx) . '3', $subject . ' TOTAL (' . $count . ')');
            $colIdx++;
        }

        // Build Data Rows
        $rowIdx = 4;
        foreach ($students as $student) {
            if (!$student->admission) continue;

            $registerId = $student->register_id;
            $sheet->setCellValue('A' . $rowIdx, $student->admission->gr_no ?? '-');
            $sheet->setCellValue('B' . $rowIdx, trim(($student->admission->first_name ?? '') . ' ' . ($student->admission->last_name ?? '') . ' ' . ($student->admission->father_name ?? '')));

            $studentMarks = \App\Models\StudentTestMark::where('register_id', $registerId)
                ->whereIn('test_id', $allTests->pluck('id'))
                ->get()
                ->keyBy('test_id');

            $colIdx = 3;
            // Weekly Marks
            foreach ($weeklyTests as $subject => $tests) {
                $subjectTotal = 0;
                foreach ($tests as $test) {
                    $obtained = isset($studentMarks[$test->id]) ? floatval($studentMarks[$test->id]->marks) : null;
                    $val = ($obtained !== null) ? $obtained : '-';
                    $sheet->setCellValue($this->getColLetter($colIdx) . $rowIdx, $val);
                    if ($obtained !== null) {
                        $subjectTotal += $obtained;
                    }
                    $colIdx++;
                }
                $sheet->setCellValue($this->getColLetter($colIdx) . $rowIdx, $subjectTotal);
                $colIdx++;
            }

            // Full Marks
            foreach ($fullTests as $subject => $tests) {
                $subjectTotal = 0;
                foreach ($tests as $test) {
                    $obtained = isset($studentMarks[$test->id]) ? floatval($studentMarks[$test->id]->marks) : null;
                    $val = ($obtained !== null) ? $obtained : '-';
                    $sheet->setCellValue($this->getColLetter($colIdx) . $rowIdx, $val);
                    if ($obtained !== null) {
                        $subjectTotal += $obtained;
                    }
                    $colIdx++;
                }
                $sheet->setCellValue($this->getColLetter($colIdx) . $rowIdx, $subjectTotal);
                $colIdx++;
            }

            // Assignment completion
            foreach ($assignmentsConfig as $subject => $totalCount) {
                $grNo = $student->admission->gr_no ?? 0;
                $completed = \App\Models\StudentAssignmentReport::where('gr_no', $grNo)
                    ->whereIn('assignment_id', $allAssignments->where('subject', $subject)->pluck('id'))
                    ->where('status', 'complete')
                    ->count();

                $sheet->setCellValue($this->getColLetter($colIdx) . $rowIdx, $completed);
                $colIdx++;
            }

            $rowIdx++;
        }

        // Apply Styles
        $lastColLetter = $this->getColLetter($colIdx - 1);
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ]
        ];
        $sheet->getStyle('A1:' . $lastColLetter . ($rowIdx - 1))->applyFromArray($styleArray);

        // Bold headers
        $sheet->getStyle('A1:' . $lastColLetter . '3')->getFont()->setBold(true);

        // Auto width for columns
        foreach (range(1, $colIdx - 1) as $col) {
            $sheet->getColumnDimension($this->getColLetter($col))->setAutoSize(true);
        }

        $fileName = 'combined-report-' . str_replace(' ', '_', $courseName) . '-' . str_replace(' ', '_', $batchName) . '-' . now()->format('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function getColLetter($colIdx)
    {
        return Coordinate::stringFromColumnIndex($colIdx);
    }

    private function formatUnitName($unitName)
    {
        $name = trim($unitName);
        if (preg_match('/^\d+(,\d+)*$/', $name)) {
            return "Unit " . $name;
        }
        return ucfirst(strtolower($name));
    }
}
