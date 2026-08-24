<?php

namespace App\Http\Controllers\admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Admission;
use App\Models\CourceRegistration;
use App\Models\ResultMaster;
use App\Models\StudentResult;
use App\Models\Master\MasterBatch;
use App\Models\Master\MasterClass;
use App\Models\Master\MasterCourse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\Facades\DataTables;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Log;

class ResultController extends Controller
{
    public $modules = [];

    public function __construct()
    {
        $this->modules = [
            'title' => 'Result Module',
            'folder_path' => 'software.module.result',
            'route' => 'result',
            'permission_prefix' => 'result',
        ];
    }

    // ==========================================
    // RESULT
    // ==========================================

    public function index(Request $request)
    {
        $modules = $this->modules;
        $modules['title'] = 'Result Master';
        $modules['route'] = 'result';
        
        View::share('modules', $modules);

        if ($request->ajax()) {
            $query = ResultMaster::with(['course', 'batch', 'class'])->orderByDesc('id');

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('course_name', function ($row) {
                    return $row->course->course_name ?? '-';
                })
                ->addColumn('batch_name', function ($row) {
                    return $row->batch->batch_name ?? '-';
                })
                ->addColumn('class_name', function ($row) {
                    return $row->class->class ?? '-';
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="d-flex gap-2 justify-content-center">';
                    $btn .= '<a href="' . route('result.entry.direct', ['course_id' => $row->course_id, 'batch_id' => $row->batch_id, 'class_id' => $row->class_id, 'semester' => $row->semester]) . '" class="btn btn-warning btn-icon" title="Enter Marks"><i class="bx bx-list-check"></i></a>';
                    $btn .= '<a href="' . route('result.edit', [$row->id]) . '" class="btn btn-info btn-icon"><i class="bx bx-edit-alt"></i></a>';
                    $btn .= '<a href="javascript:void(0)" data-id="' . $row->id . '" data-did="' . route('result.destroy', [$row->id]) . '" class="btn btn-danger btn-icon deletebutton"><i class="bx bx-trash"></i></a>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view($this->modules['folder_path'] . '.index');
    }

    public function create()
    {
        $modules = $this->modules;
        $modules['title'] = 'Create Result Master';
        $modules['route'] = 'result';
        View::share('modules', $modules);

        $courses = MasterCourse::where('status', 'active')->orderBy('course_name', 'asc')->get();
        $batches = MasterBatch::where('status', 'active')->orderBy('batch_name', 'asc')->get();
        $classes = MasterClass::where('status', 'active')->orderBy('class', 'asc')->get();

        return view($this->modules['folder_path'] . '.form', compact('courses', 'batches', 'classes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:master_course,id',
            'batch_id' => 'required|exists:master_batch,id',
            'class_ids' => 'required|array',
            'class_ids.*' => 'exists:master_class,id',
            'semester' => 'required|integer|min:1',
            'total_marks' => 'required|integer|min:1',
        ]);

        $userId = Auth::id();
        $classIds = $request->class_ids;
        $createdCount = 0;

        foreach ($classIds as $classId) {
            // Check if master already exists
            $exists = ResultMaster::where('course_id', $request->course_id)
                ->where('batch_id', $request->batch_id)
                ->where('class_id', $classId)
                ->where('semester', $request->semester)
                ->first();

            if (!$exists) {
                ResultMaster::create([
                    'course_id' => $request->course_id,
                    'batch_id' => $request->batch_id,
                    'class_id' => $classId,
                    'semester' => $request->semester,
                    'total_marks' => $request->total_marks,
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]);
                $createdCount++;
            }
        }

        if ($createdCount === 0) {
            return redirect()->back()->withErrors('Result Master configuration already exists for all selected classes.')->withInput();
        }

        return redirect()->route('result.index')->withSuccess('Result Master configuration(s) saved successfully.');
    }

    public function edit($id)
    {
        $modules = $this->modules;
        $modules['title'] = 'Edit Result Master';
        $modules['route'] = 'result';
        View::share('modules', $modules);

        $edit = ResultMaster::findOrFail($id);
        $courses = MasterCourse::where('status', 'active')->orderBy('course_name', 'asc')->get();
        $batches = MasterBatch::where('status', 'active')->orderBy('batch_name', 'asc')->get();
        $classes = MasterClass::where('status', 'active')->orderBy('class', 'asc')->get();

        return view($this->modules['folder_path'] . '.form', compact('edit', 'courses', 'batches', 'classes'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'course_id' => 'required|exists:master_course,id',
            'batch_id' => 'required|exists:master_batch,id',
            'class_ids' => 'required|array',
            'class_ids.*' => 'exists:master_class,id',
            'semester' => 'required|integer|min:1',
            'total_marks' => 'required|integer|min:1',
        ]);

        $edit = ResultMaster::findOrFail($id);
        $classIds = $request->class_ids;
        $firstClassId = array_shift($classIds);

        // Update the current record with the first class_id
        $edit->update([
            'course_id' => $request->course_id,
            'batch_id' => $request->batch_id,
            'class_id' => $firstClassId,
            'semester' => $request->semester,
            'total_marks' => $request->total_marks,
            'updated_by' => Auth::id(),
        ]);

        // Insert new records for any additional classes
        foreach ($classIds as $classId) {
            $exists = ResultMaster::where('course_id', $request->course_id)
                ->where('batch_id', $request->batch_id)
                ->where('class_id', $classId)
                ->where('semester', $request->semester)
                ->first();

            if (!$exists) {
                ResultMaster::create([
                    'course_id' => $request->course_id,
                    'batch_id' => $request->batch_id,
                    'class_id' => $classId,
                    'semester' => $request->semester,
                    'total_marks' => $request->total_marks,
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]);
            }
        }

        return redirect()->route('result.index')->withSuccess('Result Master updated successfully.');
    }

    public function destroy($id)
    {
        $master = ResultMaster::findOrFail($id);
        
        // Delete all student results associated with this result master
        StudentResult::where('result_id', $id)->delete();

        $master->delete();
        return response()->json([
            'success' => true,
            'message' => 'Result Master deleted successfully.'
        ]);
    }

    // ==========================================
    // RESULT ENTRY
    // ==========================================

    public function entryIndex(Request $request, $courseId = null, $batchId = null, $classId = null, $semester = null)
    {
        $modules = $this->modules;
        $modules['title'] = 'Result Entry';
        $modules['route'] = 'result.entry';
        View::share('modules', $modules);

        $courses = MasterCourse::where('status', 'active')->orderBy('course_name', 'asc')->get();
        $batches = MasterBatch::where('status', 'active')->orderBy('batch_name', 'asc')->get();
        $classes = MasterClass::where('status', 'active')->orderBy('class', 'asc')->get();

        return view($this->modules['folder_path'] . '.entry', compact('courses', 'batches', 'classes', 'courseId', 'batchId', 'classId', 'semester'));
    }

    public function entryGetStudents(Request $request)
    {
        $courseId = $request->course_id;
        $batchId = $request->batch_id;
        $classId = $request->class_id;
        $semester = $request->semester;

        if (!$courseId || !$batchId || !$classId || $semester === null) {
            return response()->json(['success' => false, 'message' => 'All parameters are required.'], 400);
        }

        // Get students
        $studentsQuery = DB::table('cource_registration as cr')
            ->join('admission as a', 'cr.register_id', '=', 'a.id')
            ->where('cr.course_id', $courseId)
            ->where('cr.batch_id', $batchId);

        if (is_array($classId)) {
            $studentsQuery->whereIn('cr.class_id', $classId);
        } else {
            $studentsQuery->where('cr.class_id', $classId);
        }

        $students = $studentsQuery->whereNotIn('cr.status', ['Cancel', 'cancel'])
            ->select('a.id', 'a.gr_no', DB::raw("CONCAT(a.first_name, ' ', a.last_name, ' ', a.father_name) as name"))
            ->orderBy('a.first_name', 'asc')
            ->get();

        // Get existing results
        $existingResults = StudentResult::whereIn('admission_id', $students->pluck('id'))
            ->where('semester', $semester)
            ->get()
            ->keyBy('admission_id');

        // Fetch HSC marks as well if semester is 1
        $hscResults = collect();
        if ((int)$semester === 1) {
            $hscResults = StudentResult::whereIn('admission_id', $students->pluck('id'))
                ->where('semester', 0)
                ->get()
                ->keyBy('admission_id');
        }

        // Get master config
        $totalMarks = 0;
        if ((int)$semester > 0) {
            $master = ResultMaster::where('course_id', $courseId)
                ->where('batch_id', $batchId)
                ->where('semester', $semester)
                ->first();
            if (!$master) {
                return response()->json([
                    'success' => false,
                    'error_type' => 'no_master',
                    'message' => 'First add that semester for that result'
                ]);
            }
            $totalMarks = $master->total_marks;
        } else {
            // HSC default total marks
            $totalMarks = 700;
        }

        $studentData = [];
        foreach ($students as $student) {
            $result = $existingResults->get($student->id);
            $hscResult = $hscResults->get($student->id);
            $studentData[] = [
                'id' => $student->id,
                'gr_no' => $student->gr_no ?? '-',
                'name' => $student->name,
                'obtained_marks' => $result ? $result->obtained_marks : '',
                'percentage' => $result ? $result->percentage : '',
                'hsc_marks' => $hscResult ? $hscResult->obtained_marks : '',
                'hsc_percentage' => $hscResult ? $hscResult->percentage : '',
            ];
        }

        return response()->json([
            'success' => true,
            'students' => $studentData,
            'total_marks' => $totalMarks,
            'semester' => $semester
        ]);
    }

    public function entryStore(Request $request)
    {
        $request->validate([
            'semester' => 'required|integer',
            'results' => 'required|array',
            'results.*.admission_id' => 'required|exists:admission,id',
            'results.*.obtained_marks' => 'nullable|numeric|min:0',
            'results.*.percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        $userId = Auth::id();
        $semester = $request->semester;

        DB::beginTransaction();
        try {
            foreach ($request->results as $res) {
                if ($res['obtained_marks'] === null || $res['obtained_marks'] === '') {
                    // If empty, delete existing result for this student & semester
                    StudentResult::where('admission_id', $res['admission_id'])
                        ->where('semester', $semester)
                        ->delete();
                    continue;
                }

                // Query cource_registration for this admission to find course, batch, class
                $registration = DB::table('cource_registration')
                    ->where('register_id', $res['admission_id'])
                    ->first();

                $resultId = null;
                if ($registration) {
                    $master = ResultMaster::where('course_id', $registration->course_id)
                        ->where('batch_id', $registration->batch_id)
                        ->where('class_id', $registration->class_id)
                        ->where('semester', $semester)
                        ->first();
                    if ($master) {
                        $resultId = $master->id;
                    }
                }

                StudentResult::updateOrCreate(
                    [
                        'admission_id' => $res['admission_id'],
                        'semester' => $semester,
                    ],
                    [
                        'result_id' => $resultId,
                        'obtained_marks' => $res['obtained_marks'],
                        'percentage' => $res['percentage'] ?? 0,
                        'created_by' => $userId,
                        'updated_by' => $userId,
                    ]
                );
            }
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Results saved successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ==========================================
    // RESULT REPORT (GRID)
    // ==========================================

    public function reportIndex(Request $request)
    {
        $modules = $this->modules;
        $modules['title'] = 'Student Results Report';
        $modules['route'] = 'result.report';
        View::share('modules', $modules);

        $courses = MasterCourse::where('status', 'active')->orderBy('course_name', 'asc')->get();
        $batches = MasterBatch::where('status', 'active')->orderBy('batch_name', 'asc')->get();
        $classes = MasterClass::where('status', 'active')->orderBy('class', 'asc')->get();

        return view($this->modules['folder_path'] . '.report', compact('courses', 'batches', 'classes'));
    }

    public function reportGet(Request $request)
    {
        $courseId = $request->course_id;
        $batchId = $request->batch_id;
        $classId = $request->class_id;

        if (!$courseId || !$batchId || !$classId) {
            return response()->json(['success' => false, 'message' => 'Please select all filters.'], 400);
        }

        // Get student list
        $studentsQuery = DB::table('cource_registration as cr')
            ->join('admission as a', 'cr.register_id', '=', 'a.id')
            ->where('cr.course_id', $courseId)
            ->where('cr.batch_id', $batchId);

        if (is_array($classId)) {
            $studentsQuery->whereIn('cr.class_id', $classId);
        } else {
            $studentsQuery->where('cr.class_id', $classId);
        }

        $students = $studentsQuery->whereNotIn('cr.status', ['Cancel', 'cancel'])
            ->select('a.id', 'a.gr_no', DB::raw("CONCAT(a.first_name, ' ', a.last_name, ' ', a.father_name) as name"))
            ->orderBy('a.first_name', 'asc')
            ->get();

        if ($students->isEmpty()) {
            return response()->json(['success' => true, 'students' => [], 'semesters' => []]);
        }

        // Get semesters that have results entered for these students
        $semestersList = StudentResult::whereIn('admission_id', $students->pluck('id'))
            ->pluck('semester')
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        // Get all results of these students
        $results = StudentResult::whereIn('admission_id', $students->pluck('id'))
            ->get()
            ->groupBy('admission_id');

        $studentData = [];
        foreach ($students as $student) {
            $studentRes = $results->get($student->id) ?? collect();
            
            $rowData = [
                'id' => $student->id,
                'gr_no' => $student->gr_no ?? '-',
                'name' => $student->name,
                'semesters' => []
            ];

            foreach ($semestersList as $sem) {
                $semRes = $studentRes->where('semester', $sem)->first();
                $rowData['semesters'][] = [
                    'semester' => $sem,
                    'marks' => $semRes ? number_format($semRes->obtained_marks, 0) : '-',
                    'percentage' => $semRes ? number_format($semRes->percentage, 2) . '%' : '-',
                ];
            }

            $studentData[] = $rowData;
        }

        return response()->json([
            'success' => true,
            'students' => $studentData,
            'semesters' => $semestersList
        ]);
    }

    public function exportExcel(Request $request)
    {
        try {
            $courseId = $request->course_id;
            $batchId = $request->batch_id;
            $classId = $request->class_id;

            if (!$courseId || !$batchId || !$classId) {
                return back()->withErrors('Please select all filters.');
            }

            // Get student list
            $studentsQuery = DB::table('cource_registration as cr')
                ->join('admission as a', 'cr.register_id', '=', 'a.id')
                ->where('cr.course_id', $courseId)
                ->where('cr.batch_id', $batchId);

            if (is_array($classId)) {
                $studentsQuery->whereIn('cr.class_id', $classId);
            } else {
                $studentsQuery->where('cr.class_id', $classId);
            }

            $students = $studentsQuery->whereNotIn('cr.status', ['Cancel', 'cancel'])
                ->select('a.id', 'a.gr_no', DB::raw("CONCAT(a.first_name, ' ', a.last_name, ' ', a.father_name) as name"))
                ->orderBy('a.first_name', 'asc')
                ->get();

            if ($students->isEmpty()) {
                return back()->withErrors('No student records found.');
            }

            // Get semesters that have results entered for these students
            $semestersList = StudentResult::whereIn('admission_id', $students->pluck('id'))
                ->pluck('semester')
                ->unique()
                ->sort()
                ->values()
                ->toArray();

            // Get all results of these students
            $results = StudentResult::whereIn('admission_id', $students->pluck('id'))
                ->get()
                ->groupBy('admission_id');

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $course = MasterCourse::find($courseId);
            $batch = MasterBatch::find($batchId);
            $title = ($course ? strtoupper($course->course_name) : '') . ' ' . ($batch ? $batch->batch_name : '') . ' BATCH ALL RESULTS';

            $totalSemesters = count($semestersList);
            $lastColNum = 2 + ($totalSemesters * 2); // Col 1: NO., Col 2: Name, then 2 columns per semester
            $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($lastColNum);

            // Row 1: Merged Title
            $sheet->mergeCells('A1:' . $lastColLetter . '1');
            $sheet->setCellValue('A1', $title);
            $sheet->getRowDimension(1)->setRowHeight(30);

            // Row 2: Headers
            $sheet->setCellValue('A2', 'NO.');
            $sheet->setCellValue('B2', 'Name');

            $colIndex = 3;
            foreach ($semestersList as $sem) {
                $firstColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
                $secondColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);

                $sheet->mergeCells($firstColLetter . '2:' . $secondColLetter . '2');
                $sheet->setCellValue($firstColLetter . '2', (int)$sem === 0 ? 'HSC' : 'Sem' . $sem);

                $colIndex += 2;
            }

            // Row 3 and onwards: Data rows
            $rowIdx = 3;
            foreach ($students as $index => $student) {
                $sheet->setCellValue('A' . $rowIdx, $index + 1);
                $sheet->setCellValue('B' . $rowIdx, $student->name);

                $studentRes = $results->get($student->id) ?? collect();
                $curColIndex = 3;

                foreach ($semestersList as $sem) {
                    $semRes = $studentRes->where('semester', $sem)->first();
                    
                    $marks = $semRes ? $semRes->obtained_marks : '-';
                    $percentage = $semRes ? number_format($semRes->percentage, 2) : '-';

                    $firstCell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($curColIndex);
                    $secondCell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($curColIndex + 1);

                    $sheet->setCellValue($firstCell . $rowIdx, $marks);
                    $sheet->setCellValue($secondCell . $rowIdx, $percentage);

                    $curColIndex += 2;
                }

                $rowIdx++;
            }

            // Autofit columns
            for ($col = 1; $col <= $lastColNum; $col++) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                $sheet->getColumnDimension($colLetter)->setAutoSize(true);
            }

            // Layout Styling
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A1')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

            $sheet->getStyle('A2:' . $lastColLetter . '2')->getFont()->setBold(true)->setSize(11);
            $sheet->getStyle('A2:' . $lastColLetter . '2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A2:' . $lastColLetter . '2')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

            $sheet->getStyle('A3:A' . ($rowIdx - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B3:B' . ($rowIdx - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('C3:' . $lastColLetter . ($rowIdx - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            // Thin gridlines
            $styleArray = [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['argb' => 'FF000000'],
                    ],
                ],
            ];
            $sheet->getStyle('A1:' . $lastColLetter . ($rowIdx - 1))->applyFromArray($styleArray);

            $writer = new Xlsx($spreadsheet);

            $response = new StreamedResponse(function () use ($writer) {
                $writer->save('php://output');
            });

            $fileName = ($course ? str_replace(' ', '_', $course->course_name) : 'Student') . '_' . ($batch ? $batch->batch_name : 'Results') . '_Report.xlsx';

            $disposition = $response->headers->makeDisposition(
                \Symfony\Component\HttpFoundation\ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                $fileName
            );

            $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            $response->headers->set('Content-Disposition', $disposition);

            return $response;

        } catch (\Exception $e) {
            Log::error('Result Report Export Excel Error: ' . $e->getMessage());
            return back()->withErrors('Something went wrong while exporting the Excel.');
        }
    }
}
