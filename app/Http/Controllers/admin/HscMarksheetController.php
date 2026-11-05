<?php

namespace App\Http\Controllers\admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\CourceRegistration;
use App\Models\HscMarksheet;
use App\Models\Master\MasterCourse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yajra\DataTables\Facades\DataTables;

class HscMarksheetController extends Controller
{
    public array $modules = [];

    public function __construct()
    {
        $this->modules = [
            'title' => 'HSC Marksheet',
            'folder_path' => 'software.module.hsc-marksheet',
            'route' => 'hsc-marksheet',
            'permission_prefix' => 'hsc-marksheet',
        ];
    }

    private function hscCourseId(): ?int
    {
        $course = MasterCourse::query()
            ->where(function ($q) {
                $q->whereRaw('UPPER(course_name) = ?', ['HSC'])
                    ->orWhereRaw('UPPER(course_name) LIKE ?', ['HSC %'])
                    ->orWhereRaw('UPPER(course_name) LIKE ?', ['% HSC']);
            })
            ->first();

        return $course?->id ? (int) $course->id : 5;
    }

    private function calcResult(array $marksMap, bool $isAbsent = false): array
    {
        $subjects = HscMarksheet::subjects();
        $obtained = 0;
        $maximum = 0;
        $pass = true;
        $hasAb = false;

        foreach ($subjects as $subject) {
            $max = (float) $subject['max'];
            $raw = $marksMap[$subject['key']] ?? 0;
            $isAb = strtoupper(trim((string) $raw)) === 'AB';
            if ($isAb) {
                $hasAb = true;
                $val = 0.0;
                $pass = false;
            } else {
                $val = (float) $raw;
                if ($val < ($max * 0.33)) {
                    $pass = false;
                }
            }
            $obtained += $val;
            $maximum += $max;
        }

        if ($isAbsent || ($hasAb && collect($marksMap)->filter(fn ($v) => strtoupper(trim((string) $v)) === 'AB')->count() === count($subjects))) {
            return [
                'total_obtained' => 0,
                'total_maximum' => $maximum,
                'percentage' => 0,
                'grade' => 'AB',
                'result' => 'ABSENT',
            ];
        }

        $percentage = $maximum > 0 ? round(($obtained / $maximum) * 100, 2) : 0;
        $grade = 'F';
        if ($percentage >= 90) {
            $grade = 'A+';
        } elseif ($percentage >= 80) {
            $grade = 'A';
        } elseif ($percentage >= 70) {
            $grade = 'B+';
        } elseif ($percentage >= 60) {
            $grade = 'B';
        } elseif ($percentage >= 50) {
            $grade = 'C';
        } elseif ($percentage >= 33) {
            $grade = 'D';
        }

        return [
            'total_obtained' => $obtained,
            'total_maximum' => $maximum,
            'percentage' => $percentage,
            'grade' => $grade,
            'result' => $pass ? 'PASS' : 'FAIL',
        ];
    }

    public function index(Request $request)
    {
        $modules = $this->modules;
        $modules['permission_list'] = Helper::directCan($modules['permission_prefix'] . '-list');
        $modules['permission_add'] = Helper::directCan($modules['permission_prefix'] . '-create');
        $modules['permission_edit'] = Helper::directCan($modules['permission_prefix'] . '-edit');
        $modules['permission_delete'] = Helper::directCan($modules['permission_prefix'] . '-delete');

        if (!$modules['permission_list']) {
            abort(403, 'User does not have the right permissions.');
        }

        View::share('modules', $modules);

        $columns = [
            (object) ['data' => 'DT_RowIndex', 'name' => 'DT_RowIndex', 'td_label' => 'No', 'orderable' => false, 'searchable' => false],
            (object) ['data' => 'student_name', 'name' => 'student_name', 'td_label' => 'Student Name', 'orderable' => false],
            (object) ['data' => 'exam_title', 'name' => 'exam_title', 'td_label' => 'Exam'],
            (object) ['data' => 'total_obtained', 'name' => 'total_obtained', 'td_label' => 'Marks'],
            (object) ['data' => 'percentage', 'name' => 'percentage', 'td_label' => 'Percentage'],
            (object) ['data' => 'grade', 'name' => 'grade', 'td_label' => 'Grade'],
            (object) ['data' => 'rank', 'name' => 'rank', 'td_label' => 'Rank'],
            (object) ['data' => 'result', 'name' => 'result', 'td_label' => 'Result'],
            (object) ['data' => 'action', 'name' => 'action', 'td_label' => 'Action', 'orderable' => false, 'searchable' => false, 'className' => 'text-center'],
        ];
        View::share('columns', $columns);

        if ($request->ajax()) {
            $data = HscMarksheet::query()->with('admission')->latest('id');

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('student_name', function ($row) {
                    return $row->studentName();
                })
                ->editColumn('total_obtained', function ($row) {
                    return number_format((float) $row->total_obtained, 0) . ' / ' . number_format((float) $row->total_maximum, 0);
                })
                ->editColumn('percentage', function ($row) {
                    return number_format((float) $row->percentage, 2) . '%';
                })
                ->addColumn('action', function ($row) use ($modules) {
                    $btn = '<div class="d-flex gap-1 justify-content-center align-items-center">';
                    $btn .= '<a href="' . route('hsc-marksheet.show', $row->id) . '" class="btn btn-success btn-icon mx-1" title="View"><i class="bx bx-show"></i></a>';
                    if ($modules['permission_edit']) {
                        $btn .= '<a href="' . route('hsc-marksheet.edit', $row->id) . '" class="btn btn-info btn-icon mx-1" title="Edit"><i class="bx bx-edit-alt"></i></a>';
                    }
                    if ($modules['permission_delete']) {
                        $btn .= '<a href="javascript:void(0)" data-id="' . $row->id . '" data-did="' . route('hsc-marksheet.destroy', $row->id) . '" class="btn btn-danger btn-icon deletebutton mx-1" title="Delete"><i class="bx bx-trash"></i></a>';
                    }
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view($modules['folder_path'] . '.index', compact('modules'));
    }

    public function create()
    {
        $modules = $this->modules;
        $modules['permission_add'] = Helper::directCan($modules['permission_prefix'] . '-create');
        if (!$modules['permission_add']) {
            abort(403, 'User does not have the right permissions.');
        }
        View::share('modules', $modules);

        $students = $this->hscStudents();
        $subjects = HscMarksheet::subjects();

        return view($modules['folder_path'] . '.form', compact('modules', 'students', 'subjects'));
    }

    public function store(Request $request)
    {
        if (!Helper::directCan($this->modules['permission_prefix'] . '-create')) {
            abort(403, 'User does not have the right permissions.');
        }

        $validated = $this->validateMarksheet($request, null);
        $isAbsent = (bool) ($validated['is_absent'] ?? false);
        $calc = $this->calcResult($validated['marks'], $isAbsent);

        $registration = CourceRegistration::query()
            ->where('register_id', $validated['student_id'])
            ->where('course_id', $this->hscCourseId())
            ->whereNotIn('status', ['Cancel', 'cancel'])
            ->latest('id')
            ->first();

        HscMarksheet::create([
            'student_id' => $validated['student_id'],
            'registration_id' => $registration?->id,
            'exam_title' => $validated['exam_title'],
            'academic_year' => $validated['academic_year'],
            'standard' => $validated['standard'],
            'roll_number' => $validated['roll_number'] ?? null,
            'is_absent' => $isAbsent,
            'marks' => $validated['marks'],
            'total_obtained' => $calc['total_obtained'],
            'total_maximum' => $calc['total_maximum'],
            'percentage' => $calc['percentage'],
            'grade' => $calc['grade'],
            'rank' => null,
            'result' => $calc['result'],
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        return Redirect::route('hsc-marksheet.index')->with('success', 'HSC marksheet saved successfully.');
    }

    public function edit(string $id)
    {
        $modules = $this->modules;
        $modules['permission_edit'] = Helper::directCan($modules['permission_prefix'] . '-edit');
        if (!$modules['permission_edit']) {
            abort(403, 'User does not have the right permissions.');
        }
        View::share('modules', $modules);

        $edit = HscMarksheet::with('admission')->findOrFail($id);
        $students = $this->hscStudents();
        $subjects = HscMarksheet::subjects();

        return view($modules['folder_path'] . '.form', compact('modules', 'edit', 'students', 'subjects'));
    }

    public function update(Request $request, string $id)
    {
        if (!Helper::directCan($this->modules['permission_prefix'] . '-edit')) {
            abort(403, 'User does not have the right permissions.');
        }

        $edit = HscMarksheet::findOrFail($id);
        $validated = $this->validateMarksheet($request, $edit->id);
        $isAbsent = (bool) ($validated['is_absent'] ?? false);
        $calc = $this->calcResult($validated['marks'], $isAbsent);

        $registration = CourceRegistration::query()
            ->where('register_id', $validated['student_id'])
            ->where('course_id', $this->hscCourseId())
            ->whereNotIn('status', ['Cancel', 'cancel'])
            ->latest('id')
            ->first();

        $edit->update([
            'student_id' => $validated['student_id'],
            'registration_id' => $registration?->id,
            'exam_title' => $validated['exam_title'],
            'academic_year' => $validated['academic_year'],
            'standard' => $validated['standard'],
            'roll_number' => $validated['roll_number'] ?? null,
            'is_absent' => $isAbsent,
            'marks' => $validated['marks'],
            'total_obtained' => $calc['total_obtained'],
            'total_maximum' => $calc['total_maximum'],
            'percentage' => $calc['percentage'],
            'grade' => $calc['grade'],
            'rank' => null,
            'result' => $calc['result'],
            'updated_by' => Auth::id(),
        ]);

        return Redirect::route('hsc-marksheet.index')->with('success', 'HSC marksheet updated successfully.');
    }

    public function show(string $id)
    {
        $modules = $this->modules;
        if (!Helper::directCan($modules['permission_prefix'] . '-list')) {
            abort(403, 'User does not have the right permissions.');
        }
        View::share('modules', $modules);

        $marksheet = HscMarksheet::with('admission')->findOrFail($id);
        $subjects = HscMarksheet::subjects();

        return view($modules['folder_path'] . '.view', compact('modules', 'marksheet', 'subjects'));
    }

    public function destroy(string $id)
    {
        if (!Helper::directCan($this->modules['permission_prefix'] . '-delete')) {
            return response()->json([
                'success' => false,
                'message' => 'User does not have the right permissions.',
            ]);
        }

        try {
            $row = HscMarksheet::findOrFail($id);
            $row->delete();

            return response()->json([
                'success' => true,
                'message' => 'HSC marksheet deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function generateRanks()
    {
        if (
            !Helper::directCan($this->modules['permission_prefix'] . '-edit')
            && !Helper::directCan($this->modules['permission_prefix'] . '-create')
        ) {
            abort(403, 'User does not have the right permissions.');
        }

        $exams = HscMarksheet::query()
            ->select('exam_title')
            ->distinct()
            ->pluck('exam_title');

        foreach ($exams as $examTitle) {
            $this->recalculateRanks((string) $examTitle);
        }

        return Redirect::route('hsc-marksheet.index')->with('success', 'Ranks auto generated exam-wise successfully.');
    }

    public function exportExcel(Request $request)
    {
        if (!Helper::directCan($this->modules['permission_prefix'] . '-list')) {
            abort(403, 'User does not have the right permissions.');
        }

        $records = HscMarksheet::query()->with('admission')->latest('id')->get();
        $headers = ['No', 'Student Name', 'Roll Number', 'Exam', 'Academic Year', 'Standard', 'Marks', 'Percentage', 'Grade', 'Rank', 'Result'];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('HSC Marksheet');

        foreach ($headers as $i => $label) {
            $sheet->setCellValueByColumnAndRow($i + 1, 1, $label);
        }

        $rowNumber = 2;
        foreach ($records as $index => $row) {
            $values = [
                $index + 1,
                $row->studentName(),
                $row->roll_number ?? '',
                $row->exam_title ?? '',
                $row->academic_year ?? '',
                $row->standard ?? '',
                number_format((float) $row->total_obtained, 0) . ' / ' . number_format((float) $row->total_maximum, 0),
                number_format((float) $row->percentage, 2) . '%',
                $row->grade ?? '',
                $row->rank ?? '',
                $row->result ?? '',
            ];
            foreach ($values as $col => $value) {
                $sheet->setCellValueByColumnAndRow($col + 1, $rowNumber, $value);
            }
            $rowNumber++;
        }

        for ($i = 1; $i <= count($headers); $i++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
        }

        $fileName = 'hsc-marksheet-' . now()->format('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function printView(Request $request)
    {
        if (!Helper::directCan($this->modules['permission_prefix'] . '-list')) {
            abort(403, 'User does not have the right permissions.');
        }

        $records = HscMarksheet::query()->with('admission')->latest('id')->get();

        return view($this->modules['folder_path'] . '.print', [
            'records' => $records,
            'generatedAt' => now(),
            'page_title' => $this->modules['title'],
        ]);
    }

    private function recalculateRanks(string $examTitle): void
    {
        // All HSC marksheet entries for this exam get a rank (present first, absent last).
        $rows = HscMarksheet::query()
            ->where('exam_title', $examTitle)
            ->orderByRaw("CASE WHEN is_absent = 1 OR UPPER(COALESCE(result, '')) = 'ABSENT' THEN 1 ELSE 0 END")
            ->orderByDesc('percentage')
            ->orderByDesc('total_obtained')
            ->orderBy('id')
            ->get();

        $position = 0;
        $displayRank = 0;
        $lastKey = null;

        foreach ($rows as $row) {
            $position++;
            $isAbsent = $row->is_absent || strtoupper((string) $row->result) === 'ABSENT';
            $key = $isAbsent
                ? 'AB'
                : (number_format((float) $row->percentage, 2) . '|' . number_format((float) $row->total_obtained, 2));

            if ($key !== $lastKey) {
                $displayRank = $position;
                $lastKey = $key;
            }

            $row->rank = $this->ordinal($displayRank);
            $row->save();
        }
    }

    private function ordinal(int $number): string
    {
        $mod100 = $number % 100;
        if ($mod100 >= 11 && $mod100 <= 13) {
            return $number . 'th';
        }

        return match ($number % 10) {
            1 => $number . 'st',
            2 => $number . 'nd',
            3 => $number . 'rd',
            default => $number . 'th',
        };
    }

    private function validateMarksheet(Request $request, ?int $ignoreId = null): array
    {
        $subjectKeys = collect(HscMarksheet::subjects())->pluck('key')->all();
        $isAbsent = $request->boolean('is_absent');
        $request->merge([
            'roll_number' => filled($request->input('roll_number'))
                ? trim((string) $request->input('roll_number'))
                : null,
        ]);

        $rollUnique = Rule::unique('hsc_marksheets', 'roll_number')
            ->where(function ($q) use ($request) {
                $q->where('academic_year', $request->input('academic_year'))
                    ->where('exam_title', $request->input('exam_title'))
                    ->where('standard', $request->input('standard'))
                    ->whereNull('deleted_at');
            });
        if ($ignoreId) {
            $rollUnique->ignore($ignoreId);
        }

        $rules = [
            'student_id' => ['required', 'integer'],
            'exam_title' => ['required', 'string', 'max:255'],
            'academic_year' => ['required', 'string', 'max:50'],
            'standard' => ['required', 'string', 'max:100'],
            'roll_number' => ['nullable', 'string', 'max:50', $rollUnique],
            'is_absent' => ['nullable', 'boolean'],
            'marks' => ['required', 'array'],
        ];
        foreach ($subjectKeys as $key) {
            $rules['marks.' . $key] = ['nullable'];
        }

        $validated = $request->validate($rules, [
            'roll_number.unique' => 'This Roll Number already exists for this exam / year / standard.',
        ]);
        $validated['is_absent'] = $isAbsent;

        foreach ($subjectKeys as $key) {
            $raw = trim((string) ($validated['marks'][$key] ?? ''));
            if ($isAbsent || strtoupper($raw) === 'AB') {
                $validated['marks'][$key] = 'AB';
                continue;
            }
            if ($raw === '') {
                $validated['marks'][$key] = 0;
                continue;
            }
            if (!is_numeric($raw) || (float) $raw < 0 || (float) $raw > 50) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'marks.' . $key => 'Marks must be 0-50 or AB.',
                ]);
            }
            $validated['marks'][$key] = (float) $raw;
        }

        return $validated;
    }

    private function hscStudents()
    {
        $courseId = $this->hscCourseId();

        return CourceRegistration::query()
            ->with('admission')
            ->where('course_id', $courseId)
            ->whereNotIn('status', ['Cancel', 'cancel'])
            ->orderByDesc('id')
            ->get()
            ->map(function ($reg) {
                $a = $reg->admission;
                $name = $a
                    ? trim(($a->first_name ?? '') . ' ' . ($a->last_name ?? '') . ' ' . ($a->father_name ?? ''))
                    : '-';

                return (object) [
                    'student_id' => $reg->register_id,
                    'name' => $name,
                    'gr_no' => $a->gr_no ?? '',
                ];
            })
            ->unique('student_id')
            ->values();
    }
}
