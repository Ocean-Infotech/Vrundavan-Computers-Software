<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Helpers\Helper;
use App\Models\Admission;
use App\Models\CourceRegistration;
use App\Models\FeesCollection;
use App\Models\Attedance;
use App\Models\Test;
use App\Models\StudentTestMark;
use App\Models\FacultyComplaintReport;
use App\Models\Assignment;
use App\Models\StudentAssignmentReport;
use App\Models\Assessment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Carbon\Carbon;

class StudentReportController extends Controller
{
    public $modules = [];

    public function __construct()
    {
        $this->modules = [
            'title' => 'Student Report',
            'folder_path' => 'software.module.student_report',
            'route' => 'student_report',
            'permission_prefix' => 'student_report',
        ];
    }

    public function index(Request $request)
    {
        $modules = $this->modules;
        $modules['login_user_role'] = Helper::getLoginUserRole();
        $modules['permission_list'] = Helper::directCan($modules['permission_prefix'] . '-list');
        
        if (!$modules['permission_list']) {
            abort(403, 'User does not have the right permissions.');
        }

        View::share('modules', $modules);
        return view($modules['folder_path'] . '.index');
    }

    public function getReportData(Request $request)
    {
        $studentId = $request->student_id;
        if (!$studentId) {
            return response()->json(['success' => false, 'message' => 'Student ID is required.'], 400);
        }

        $data = $this->gatherReportData($studentId);
        if (!$data) {
            return response()->json(['success' => false, 'message' => 'Student not found.'], 404);
        }

        return response()->json(array_merge(['success' => true], $data));
    }

    public function printReport(Request $request)
    {
        $studentId = $request->student_id;
        if (!$studentId) {
            abort(400, 'Student ID is required.');
        }

        $data = $this->gatherReportData($studentId);
        if (!$data) {
            abort(404, 'Student not found.');
        }

        return view('software.module.student_report.print', $data);
    }

    private function gatherReportData($studentId)
    {
        // 1. Personal Details
        $student = Admission::find($studentId);
        if (!$student) {
            return null;
        }

        // 2. Academic Details
        $courseReg = CourceRegistration::where('register_id', $student->id)
            ->with(['course', 'batch', 'class'])
            ->first();

        // Determine current semester
        $currentSemester = 1;
        if ($courseReg) {
            if ($courseReg->is_lateral_entry == 1 && $courseReg->joining_semester) {
                $currentSemester = $courseReg->joining_semester;
            }
            $maxPaidSem = FeesCollection::where('student_id', $student->id)
                ->where('course_id', $courseReg->course_id)
                ->max('year_semester');
            if ($maxPaidSem && $maxPaidSem > $currentSemester) {
                $currentSemester = (int)$maxPaidSem;
            }
        }

        // 3. Fees Details (All Semesters)
        $feesList = [];
        if ($courseReg && $courseReg->course) {
            $totalSemesters = $courseReg->course->semester;
            $courseFees = $courseReg->course->course_fees;
            $semesterFee = $totalSemesters ? ($courseFees / $totalSemesters) : 0;

            // Group paid fees by semester
            $paidFeesBySem = FeesCollection::where('student_id', $student->id)
                ->where('course_id', $courseReg->course_id)
                ->select('year_semester', DB::raw('SUM(fees) as total_paid'))
                ->groupBy('year_semester')
                ->pluck('total_paid', 'year_semester')
                ->toArray();

            for ($sem = 1; $sem <= $totalSemesters; $sem++) {
                // If lateral entry and semester is before joining semester, they don't pay
                if ($courseReg->is_lateral_entry == 1 && $sem < $courseReg->joining_semester) {
                    continue;
                }
                $paid = $paidFeesBySem[$sem] ?? 0;
                if ($paid <= 0) {
                    continue;
                }
                $feesList[] = [
                    'semester' => 'Semester ' . $sem,
                    'total_fees' => number_format($semesterFee, 2),
                    'paid_fees' => number_format($paid, 2)
                ];
            }
        }

        // 4. Attendance Details (current Semester)
        // Calculate attendance starting from June of the current academic cycle
        $attendanceList = [];
        $now = Carbon::now();
        $startOfAttendance = Carbon::create($now->year, 6, 1)->startOfDay();
        if ($now->month < 6) {
            $startOfAttendance->subYear();
        }
        $monthsToCalculate = $startOfAttendance->diffInMonths($now->copy()->startOfMonth()) + 1;

        for ($i = 0; $i < $monthsToCalculate; $i++) {
            $dateCursor = $now->copy()->subMonths($i);
            $month = $dateCursor->month;
            $year = $dateCursor->year;

            // Fetch punches for this month
            $attendanceRecords = Attedance::where(function ($query) use ($student) {
                $query->where('admission_id', $student->id);
                if (!empty($student->biometric_id)) {
                    $query->orWhere('biometric_id', $student->biometric_id);
                }
            })
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->get()
                ->groupBy(fn($att) => Carbon::parse($att->date)->format('Y-m-d'));

            // Calculate working days in this month (excluding Sundays)
            $startOfMonth = Carbon::createFromDate($year, $month, 1);
            if ($month == date('n') && $year == date('Y')) {
                $endOfMonth = Carbon::today();
            } else {
                $endOfMonth = $startOfMonth->copy()->endOfMonth();
            }

            $totalWorkingDays = 0;
            $presentCount = 0;
            $dailyRecords = [];
            $currentDate = $startOfMonth->copy();
            while ($currentDate <= $endOfMonth) {
                if ($currentDate->dayOfWeek !== Carbon::SUNDAY) {
                    $totalWorkingDays++;
                    $dateStr = $currentDate->format('Y-m-d');
                    $dayPunches = $attendanceRecords->get($dateStr, collect());
                    $dayAttendance = Attedance::resolveDayAttendance($dayPunches);
                    if ($dayAttendance['is_present']) {
                        $presentCount++;
                    }
                    $dailyRecords[] = [
                        'date' => $currentDate->format('d-m-Y'),
                        'day' => $currentDate->format('l'),
                        'in_time' => Attedance::formatTimeForApi($dayAttendance['in_time']) ?? '-',
                        'out_time' => Attedance::formatTimeForApi($dayAttendance['out_time']) ?? '-',
                        'status' => $dayAttendance['is_present'] ? 'Present' : 'Absent'
                    ];
                }
                $currentDate->addDay();
            }

            if ($totalWorkingDays > 0) {
                $absentCount = $totalWorkingDays - $presentCount;
                $attendanceList[] = [
                    'month' => $dateCursor->format('F'),
                    'total_days' => $totalWorkingDays,
                    'present_days' => $presentCount,
                    'absent_days' => $absentCount,
                    'daily_records' => $dailyRecords
                ];
            }
        }
        $attendanceList = array_reverse($attendanceList);

        // 5. Test Details (Current Semester)
        $testList = [];
        if ($courseReg) {
            $tests = Test::where('course_id', $courseReg->course_id)
                ->where('batch_id', $courseReg->batch_id)
                ->where('semester', $currentSemester)
                ->where('date', '>=', $startOfAttendance->format('Y-m-d'))
                ->whereNull('deleted_at')
                ->orderBy('date', 'desc')
                ->get();

            foreach ($tests as $test) {
                $obtainedMarkRecord = DB::table('student_test_marks')
                    ->where('test_id', $test->id)
                    ->where('register_id', $student->id)
                    ->first();

                $testList[] = [
                    'subject' => $test->subject_name,
                    'date' => $test->date ? Carbon::parse($test->date)->format('d-m-Y') : '-',
                    'total_marks' => $test->mark,
                    'obtained_marks' => $obtainedMarkRecord ? $obtainedMarkRecord->marks : '-'
                ];
            }
        }

        // 6. Complaints
        $complaintList = [];
        $complaints = FacultyComplaintReport::where('admission_id', $student->id)
            ->with('faculty')
            ->orderBy('date', 'desc')
            ->get();

        foreach ($complaints as $c) {
            $complaintList[] = [
                'complaint' => $c->complaint,
                'date' => $c->date ? Carbon::parse($c->date)->format('d-m-Y') : '-',
                'faculty' => $c->faculty ? $c->faculty->name : 'N/A'
            ];
        }

        // 7. Assignments
        $assignmentList = [];
        if ($courseReg) {
            $assignments = Assignment::where('course_id', $courseReg->course_id)
                ->where('batch_id', $courseReg->batch_id)
                ->where('semester', $currentSemester)
                ->whereNull('deleted_at')
                ->orderBy('date', 'desc')
                ->get();

            foreach ($assignments as $a) {
                $grNo = $student->gr_no ?? 0;
                $submission = StudentAssignmentReport::where('assignment_id', $a->id)
                    ->where('gr_no', $grNo)
                    ->first();

                $assignmentList[] = [
                    'subject' => $a->subject,
                    'date' => $a->date ? Carbon::parse($a->date)->format('d-m-Y') : '-',
                    'status' => $submission ? $submission->status : 'Pending',
                    'remarks' => $submission ? $submission->remarks : '-'
                ];
            }
        }

        // 8. Assessments
        $assessmentList = [];
        $assessments = Assessment::where('admission_id', $student->id)
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($assessments as $as) {
            $assessmentList[] = [
                'subject' => $as->subject,
                'performance' => $as->performance,
                'remarks' => $as->remarks
            ];
        }

        return [
            'personal' => [
                'id' => $student->id,
                'gr_no' => $student->id,
                'name' => $student->first_name . ' ' . $student->last_name . ' ' . $student->father_name,
                'photo' => $student->profile_pic ? asset($student->profile_pic) : asset('admin/assets/images/user.png')
            ],
            'academic' => [
                'batch' => $courseReg && $courseReg->batch ? $courseReg->batch->batch_name : '-',
                'class' => $courseReg && $courseReg->class ? $courseReg->class->class : '-',
                'semester' => 'Semester ' . $currentSemester
            ],
            'fees' => $feesList,
            'attendance' => $attendanceList,
            'tests' => $testList,
            'complaints' => $complaintList,
            'assignments' => $assignmentList,
            'assessments' => $assessmentList
        ];
    }
}
