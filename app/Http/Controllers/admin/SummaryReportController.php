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
use App\Models\Master\MasterHoliday;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Carbon\Carbon;

class SummaryReportController extends Controller
{
    public $modules = [];

    public function __construct()
    {
        $this->modules = [
            'title' => 'Summary Report',
            'folder_path' => 'software.module.summary_report',
            'route' => 'summary_report',
            'permission_prefix' => 'summary_report',
        ];
    }

    public function index(Request $request)
    {
        $modules = $this->modules;
        $modules['login_user_role'] = Helper::getLoginUserRole();
        $modules['permission_list'] = Helper::directCan($modules['permission_prefix'] . '-list') || Helper::directCan('student_report-list');

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

        return view('software.module.summary_report.print', $data);
    }

    public function downloadPdf(Request $request)
    {
        // Use HTML print view (browser Print → Save as PDF). No mPDF dependency.
        return $this->printReport($request);
    }

    public function getStudentsList(Request $request)
    {
        $query = CourceRegistration::with([
            'admission' => function ($q) {
                $q->whereNull('deleted_at');
            },
            'course' => function ($q) {
                $q->whereNull('deleted_at');
            },
            'batch' => function ($q) {
                $q->whereNull('deleted_at');
            },
            'class' => function ($q) {
                $q->whereNull('deleted_at');
            }
        ])
            ->whereNull('deleted_at')
            ->whereHas('admission', function ($q) {
                $q->whereNull('deleted_at');
            });

        if ($request->filled('student_id')) {
            $query->where('register_id', $request->student_id);
        }
        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }
        if ($request->filled('batch_id')) {
            $query->where('batch_id', $request->batch_id);
        }
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        $registrations = $query->get();

        $students = $registrations->map(function ($reg) {
            $student = $reg->admission;
            if (!$student || $student->deleted_at !== null) return null;
            return [
                'id' => $student->id,
                'gr_no' => $student->gr_no ?? '-',
                'name' => $student->first_name . ' ' . $student->last_name . ' ' . $student->father_name,
                'course' => $reg->course ? $reg->course->course_name : '-',
                'batch' => $reg->batch ? $reg->batch->batch_name : '-',
                'class' => $reg->class ? $reg->class->class : '-',
                'photo' => $student->profile_pic ? (filter_var($student->profile_pic, FILTER_VALIDATE_URL) ? $student->profile_pic : asset($student->profile_pic)) : asset('admin/assets/images/users/user-dummy-img.jpg')
            ];
        })->filter()->unique('id')->values();

        return response()->json(['success' => true, 'data' => $students]);
    }

    public function bulkDownloadPdf(Request $request)
    {
        @ini_set('memory_limit', '512M');
        @ini_set('max_execution_time', '300');

        $studentIds = $request->student_ids;
        if (is_string($studentIds)) {
            $studentIds = explode(',', $studentIds);
        }
        $studentIds = array_filter(array_map('intval', (array)$studentIds));

        if (empty($studentIds)) {
            if ($request->filled('course_id') || $request->filled('batch_id') || $request->filled('class_id')) {
                $q = CourceRegistration::whereNull('deleted_at')
                    ->whereHas('admission', function ($query) {
                        $query->whereNull('deleted_at');
                    });
                if ($request->filled('course_id')) $q->where('course_id', $request->course_id);
                if ($request->filled('batch_id')) $q->where('batch_id', $request->batch_id);
                if ($request->filled('class_id')) $q->where('class_id', $request->class_id);
                $studentIds = $q->pluck('register_id')->filter()->unique()->toArray();
            }
        }

        if (empty($studentIds)) {
            abort(400, 'No students selected for bulk download.');
        }

        // HTML print pages (browser Print → Save as PDF). No mPDF dependency.
        $format = $request->get('format', 'zip');

        if ($format === 'zip') {
            $zip = new \ZipArchive();
            $zipFileName = 'Summary_Reports_' . date('Ymd_His') . '.zip';
            $zipFilePath = storage_path('app/' . $zipFileName);

            if ($zip->open($zipFilePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                abort(500, 'Could not create ZIP archive.');
            }

            $added = 0;
            foreach ($studentIds as $studentId) {
                $data = $this->gatherReportData($studentId);
                if (!$data) {
                    continue;
                }
                $safeName = strtoupper(preg_replace('/[^A-Za-z0-9]+/', '_', $data['personal']['name'] ?? ('student_' . $studentId)));
                $safeName = trim($safeName, '_') ?: ('STUDENT_' . $studentId);
                $html = view('software.module.summary_report.print', $data)->render();
                $zip->addFromString($safeName . '_Summary_Report.html', $html);
                $added++;
            }

            $zip->close();

            if ($added === 0) {
                @unlink($zipFilePath);
                abort(404, 'No valid summary reports generated.');
            }

            return response()->download($zipFilePath, $zipFileName)->deleteFileAfterSend(true);
        }

        $bodyChunks = [];
        foreach ($studentIds as $studentId) {
            $data = $this->gatherReportData($studentId);
            if (!$data) {
                continue;
            }

            $fullHtml = view('software.module.summary_report.print', $data)->render();
            if (preg_match('/<body[^>]*>(.*)<\/body>/is', $fullHtml, $matches)) {
                $bodyChunks[] = $matches[1];
            } else {
                $bodyChunks[] = $fullHtml;
            }
        }

        if (empty($bodyChunks)) {
            abort(404, 'No valid summary reports generated.');
        }

        $combinedHtml = '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8">'
            . '<title>Bulk Summary Reports</title>'
            . '<style>@media print{.no-print{display:none!important}}.page-break{page-break-after:always}</style>'
            . '</head><body>'
            . '<div class="no-print" style="margin:10px 0;"><button onclick="window.print()">Print / Save as PDF</button></div>'
            . implode('<div class="page-break"></div>', $bodyChunks)
            . '</body></html>';

        return response($combinedHtml, 200)->header('Content-Type', 'text/html; charset=UTF-8');
    }

    private function gatherReportData($studentId)
    {
        // 1. Personal Details
        $student = Admission::whereNull('deleted_at')->find($studentId);
        if (!$student) {
            $student = Admission::whereNull('deleted_at')->where('gr_no', $studentId)->first();
        }
        if (!$student) {
            $reg = CourceRegistration::whereNull('deleted_at')->find($studentId);
            if ($reg) {
                $student = Admission::whereNull('deleted_at')->find($reg->register_id);
            }
        }
        if (!$student || $student->deleted_at !== null) {
            return null;
        }

        // 2. Academic Details
        $courseReg = CourceRegistration::whereNull('deleted_at')
            ->where('register_id', $student->id)
            ->with([
                'course' => function ($q) {
                    $q->whereNull('deleted_at');
                },
                'batch' => function ($q) {
                    $q->whereNull('deleted_at');
                },
                'class' => function ($q) {
                    $q->whereNull('deleted_at');
                }
            ])
            ->latest('id')
            ->first();

        $currentSemester = 1;
        if ($courseReg) {
            if ($courseReg->is_lateral_entry == 1 && $courseReg->joining_semester) {
                $currentSemester = (int)$courseReg->joining_semester;
            }
            $maxPaidSem = FeesCollection::whereNull('deleted_at')
                ->where('student_id', $student->id)
                ->where('course_id', $courseReg->course_id)
                ->max('year_semester');
            if ($maxPaidSem && $maxPaidSem > $currentSemester) {
                $currentSemester = (int)$maxPaidSem;
            }
            $maxBatchAssignSem = Assignment::whereNull('deleted_at')
                ->where('course_id', $courseReg->course_id)
                ->where('batch_id', $courseReg->batch_id)
                ->max('semester');
            if ($maxBatchAssignSem && $maxBatchAssignSem > $currentSemester) {
                $currentSemester = (int)$maxBatchAssignSem;
            }
            $maxBatchTestSem = Test::whereNull('deleted_at')
                ->where('course_id', $courseReg->course_id)
                ->where('batch_id', $courseReg->batch_id)
                ->max('semester');
            if ($maxBatchTestSem && $maxBatchTestSem > $currentSemester) {
                $currentSemester = (int)$maxBatchTestSem;
            }
        }

        $now = Carbon::now();
        $startOfAttendance = Carbon::create($now->year, 6, 1)->startOfDay();
        if ($now->month < 6) {
            $startOfAttendance->subYear();
        }

        // Attendance Details (Month-wise)
        $attendanceList = [];
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

            // Calculate working days in this month (excluding Sundays and Holidays)
            $startOfMonth = Carbon::createFromDate($year, $month, 1);
            if ($month == date('n') && $year == date('Y')) {
                $endOfMonth = Carbon::today();
            } else {
                $endOfMonth = $startOfMonth->copy()->endOfMonth();
            }

            $holidays = MasterHoliday::where('status', 'active')
                ->whereNull('deleted_at')
                ->where(function ($q) use ($startOfMonth, $endOfMonth) {
                    $q->whereBetween('from_date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
                        ->orWhereBetween('to_date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
                        ->orWhere(function ($sub) use ($startOfMonth, $endOfMonth) {
                            $sub->where('from_date', '<=', $startOfMonth->format('Y-m-d'))
                                ->where('to_date', '>=', $endOfMonth->format('Y-m-d'));
                        });
                })
                ->get();

            $holidayMap = [];
            foreach ($holidays as $h) {
                $curr = Carbon::parse($h->from_date);
                $to = Carbon::parse($h->to_date);
                while ($curr <= $to) {
                    $holidayMap[$curr->format('Y-m-d')] = $h->name;
                    $curr->addDay();
                }
            }

            $totalWorkingDays = 0;
            $holidayCount = 0;
            $presentCount = 0;
            $dailyRecords = [];
            $currentDate = $startOfMonth->copy();
            while ($currentDate <= $endOfMonth) {
                if ($currentDate->dayOfWeek !== Carbon::SUNDAY) {
                    $dateStr = $currentDate->format('Y-m-d');
                    $dayPunches = $attendanceRecords->get($dateStr, collect());
                    $dayAttendance = Attedance::resolveDayAttendance($dayPunches);
                    $isHoliday = isset($holidayMap[$dateStr]);

                    if ($dayAttendance['is_present']) {
                        $presentCount++;
                        $totalWorkingDays++;
                        $status = 'Present';
                    } elseif ($isHoliday) {
                        $holidayCount++;
                        $status = 'Holiday (' . $holidayMap[$dateStr] . ')';
                    } else {
                        $totalWorkingDays++;
                        $status = 'Absent';
                    }

                    $dailyRecords[] = [
                        'date' => $currentDate->format('d-m-Y'),
                        'day' => $currentDate->format('l'),
                        'in_time' => Attedance::formatTimeForApi($dayAttendance['in_time']) ?? '-',
                        'out_time' => Attedance::formatTimeForApi($dayAttendance['out_time']) ?? '-',
                        'status' => $status
                    ];
                }
                $currentDate->addDay();
            }

            if (($totalWorkingDays + $holidayCount) > 0) {
                $absentCount = max(0, $totalWorkingDays - $presentCount);
                $attendanceList[] = [
                    'month' => $dateCursor->format('F'),
                    'total_days' => $totalWorkingDays,
                    'present_days' => $presentCount,
                    'absent_days' => $absentCount,
                    'holiday_days' => $holidayCount,
                    'daily_records' => $dailyRecords
                ];
            }
        }
        $attendanceList = array_reverse($attendanceList);

        // 3. Assignments
        $assignmentList = [];
        if ($courseReg) {
            $assignments = Assignment::whereNull('deleted_at')
                ->where('course_id', $courseReg->course_id)
                ->where('batch_id', $courseReg->batch_id)
                ->where('semester', $currentSemester)
                ->orderBy('date', 'desc')
                ->get();

            foreach ($assignments as $a) {
                $grNo = $student->gr_no ?? 0;
                $submission = StudentAssignmentReport::where('assignment_id', $a->id)
                    ->where('gr_no', $grNo)
                    ->first();

                $statusVal = 'Pending';
                if ($submission && !empty($submission->status)) {
                    $statusVal = ucfirst(strtolower(trim($submission->status)));
                }

                $assignmentList[] = [
                    'subject' => $a->subject,
                    'date' => $a->date ? Carbon::parse($a->date)->format('d-m-Y') : '-',
                    'status' => $statusVal,
                    'remarks' => $submission ? ($submission->remarks ?: '-') : '-'
                ];
            }
        }

        // 4. Assessments
        $assessmentList = [];
        $assessments = Assessment::whereNull('deleted_at')
            ->where('admission_id', $student->id)
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($assessments as $as) {
            $assessmentList[] = [
                'subject' => $as->subject,
                'performance' => $as->performance ?? '-',
                'remarks' => $as->remarks ?? '-'
            ];
        }

        // 5. Test Details (Current Semester)
        $testList = [];
        if ($courseReg) {
            $tests = Test::whereNull('deleted_at')
                ->where('course_id', $courseReg->course_id)
                ->where('batch_id', $courseReg->batch_id)
                ->where('semester', $currentSemester)
                ->where('date', '>=', $startOfAttendance->format('Y-m-d'))
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
        $complaints = FacultyComplaintReport::whereNull('deleted_at')
            ->where('admission_id', $student->id)
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

        // 7. Student Results (All Semesters)
        $resultList = [];
        $studentResults = \App\Models\StudentResult::whereNull('deleted_at')
            ->where('admission_id', $student->id)
            ->where('semester', '<=', $currentSemester)
            ->where(function ($q) {
                $q->whereNull('result_id')
                    ->orWhere('result_id', 0)
                    ->orWhereHas('resultMaster', function ($rm) {
                        $rm->whereNull('deleted_at');
                    });
            })
            ->with('resultMaster')
            ->orderBy('semester', 'asc')
            ->get();

        foreach ($studentResults as $r) {
            $totalMarks = '-';
            if ((int)$r->semester === 0) {
                $totalMarks = '700';
            } elseif ($r->resultMaster) {
                $totalMarks = $r->resultMaster->total_marks;
            }

            $resultList[] = [
                'semester' => (int)$r->semester === 0 ? 'HSC / Entry Qualification' : 'Semester ' . $r->semester,
                'total_marks' => $totalMarks,
                'obtained_marks' => $r->obtained_marks,
                'percentage' => $r->percentage ? $r->percentage . '%' : '-'
            ];
        }

        // 8. Achievements
        $achievementList = [];
        $achievements = \App\Models\StudentAchievement::whereNull('deleted_at')
            ->where('admission_id', $student->id)
            ->where(function ($q) {
                $q->whereNull('event_id')
                    ->orWhere('event_id', 0)
                    ->orWhereHas('event', function ($ev) {
                        $ev->whereNull('deleted_at');
                    });
            })
            ->with('event')
            ->orderBy('date', 'desc')
            ->get();

        foreach ($achievements as $ach) {
            $achievementList[] = [
                'event' => $ach->event ? $ach->event->name : 'N/A',
                'rank' => $ach->rank ?? '-',
                'date' => $ach->date ? Carbon::parse($ach->date)->format('d-m-Y') : '-',
                'remark' => $ach->remark ?? '-'
            ];
        }

        return [
            'personal' => [
                'id' => $student->id,
                'gr_no' => $student->gr_no ?? '-',
                'name' => $student->first_name . ' ' . $student->last_name . ' ' . $student->father_name,
                'photo' => $student->profile_pic ? (filter_var($student->profile_pic, FILTER_VALIDATE_URL) ? $student->profile_pic : asset($student->profile_pic)) : asset('admin/assets/images/users/user-dummy-img.jpg')
            ],
            'academic' => [
                'course' => $courseReg && $courseReg->course ? $courseReg->course->course_name : '-',
                'batch' => $courseReg && $courseReg->batch ? $courseReg->batch->batch_name : '-',
                'class' => $courseReg && $courseReg->class ? $courseReg->class->class : '-',
                'semester' => 'Semester ' . $currentSemester
            ],
            'attendance' => $attendanceList,
            'assignments' => $assignmentList,
            'assessments' => $assessmentList,
            'tests' => $testList,
            'complaints' => $complaintList,
            'results' => $resultList,
            'achievements' => $achievementList
        ];
    }
}
