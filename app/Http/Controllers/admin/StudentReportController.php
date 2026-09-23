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

use Mpdf\Mpdf;

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

    public function downloadPdf(Request $request)
    {
        $studentId = $request->student_id;
        if (!$studentId) {
            abort(400, 'Student ID is required.');
        }

        $data = $this->gatherReportData($studentId);
        if (!$data) {
            abort(404, 'Student not found.');
        }

        // Prepare local / live image paths for mPDF
        $data['logo_src'] = $this->getPdfImageSrc('uploads/logo/logo.png');

        $student = Admission::find($studentId);
        $studentPhoto = $student ? $student->profile_pic : null;
        $resolvedPhoto = $this->getPdfImageSrc($studentPhoto);

        if (empty($resolvedPhoto)) {
            $resolvedPhoto = $this->getPdfImageSrc('admin/assets/images/users/user-dummy-img.jpg');
        }
        $data['student_photo_src'] = $resolvedPhoto;

        $html = view('software.module.student_report.pdf', $data)->render();

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 10,
            'autoPageBreak' => true,
        ]);

        $mpdf->SetTitle('Student Report - ' . ($data['personal']['name'] ?? 'Report'));
        $mpdf->WriteHTML($html);

        $student = Admission::find($studentId);
        $parts = [];
        if ($student) {
            if (!empty($student->last_name)) $parts[] = $student->last_name;
            if (!empty($student->first_name)) $parts[] = $student->first_name;
            if (!empty($student->father_name)) $parts[] = $student->father_name;
        }
        $nameString = !empty($parts) ? implode('_', $parts) : ($data['personal']['name'] ?? 'student');
        $safeName = strtoupper(preg_replace('/[^A-Za-z0-9]+/', '_', $nameString));
        $safeName = trim($safeName, '_');
        $fileName = ($safeName ?: 'STUDENT_' . $studentId) . '_Report.pdf';

        $pdfContent = $mpdf->Output($fileName, 'S');

        $disposition = ($request->get('action') === 'stream' || $request->get('action') === 'view') ? 'inline' : 'attachment';

        return response($pdfContent, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', $disposition . '; filename="' . $fileName . '"');
    }

    public function getStudentsList(Request $request)
    {
        $query = CourceRegistration::with(['admission', 'course', 'batch', 'class'])
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
            if (!$student) return null;
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
                $q = CourceRegistration::query();
                if ($request->filled('course_id')) $q->where('course_id', $request->course_id);
                if ($request->filled('batch_id')) $q->where('batch_id', $request->batch_id);
                if ($request->filled('class_id')) $q->where('class_id', $request->class_id);
                $studentIds = $q->pluck('register_id')->filter()->unique()->toArray();
            }
        }

        if (empty($studentIds)) {
            abort(400, 'No students selected for bulk download.');
        }

        $format = $request->get('format', 'zip'); // 'zip' (default) or 'combined'

        if ($format === 'zip') {
            $zip = new \ZipArchive();
            $zipFileName = 'Student_Reports_' . date('Ymd_His') . '.zip';
            $zipFilePath = storage_path('app/' . $zipFileName);

            if ($zip->open($zipFilePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                abort(500, 'Could not create ZIP archive.');
            }

            foreach ($studentIds as $studentId) {
                $data = $this->gatherReportData($studentId);
                if (!$data) continue;

                $data['logo_src'] = $this->getPdfImageSrc('uploads/logo/logo.png');
                $student = Admission::find($studentId);
                $studentPhoto = $student ? $student->profile_pic : null;
                $resolvedPhoto = $this->getPdfImageSrc($studentPhoto);
                if (empty($resolvedPhoto)) {
                    $resolvedPhoto = $this->getPdfImageSrc('admin/assets/images/users/user-dummy-img.jpg');
                }
                $data['student_photo_src'] = $resolvedPhoto;

                $html = view('software.module.student_report.pdf', $data)->render();

                $mpdf = new Mpdf([
                    'mode' => 'utf-8',
                    'format' => 'A4',
                    'margin_left' => 10,
                    'margin_right' => 10,
                    'margin_top' => 10,
                    'margin_bottom' => 10,
                    'autoPageBreak' => true,
                ]);

                $mpdf->SetTitle('Student Report - ' . ($data['personal']['name'] ?? 'Report'));
                $mpdf->WriteHTML($html);

                $parts = [];
                if ($student) {
                    if (!empty($student->last_name)) $parts[] = $student->last_name;
                    if (!empty($student->first_name)) $parts[] = $student->first_name;
                    if (!empty($student->father_name)) $parts[] = $student->father_name;
                }
                $nameString = !empty($parts) ? implode('_', $parts) : ($data['personal']['name'] ?? 'student');
                $safeName = strtoupper(preg_replace('/[^A-Za-z0-9]+/', '_', $nameString));
                $safeName = trim($safeName, '_');
                $fileName = ($safeName ?: 'STUDENT_' . $studentId) . '_Report.pdf';

                $pdfString = $mpdf->Output('', 'S');
                $zip->addFromString($fileName, $pdfString);
            }

            $zip->close();

            return response()->download($zipFilePath, $zipFileName)->deleteFileAfterSend(true);
        } else {
            // Combined single PDF
            $mpdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4',
                'margin_left' => 10,
                'margin_right' => 10,
                'margin_top' => 10,
                'margin_bottom' => 10,
                'autoPageBreak' => true,
            ]);
            $mpdf->SetTitle('Bulk Student Reports');

            $count = 0;
            foreach ($studentIds as $studentId) {
                $data = $this->gatherReportData($studentId);
                if (!$data) continue;

                if ($count > 0) {
                    $mpdf->AddPage();
                }

                $data['logo_src'] = $this->getPdfImageSrc('uploads/logo/logo.png');
                $student = Admission::find($studentId);
                $studentPhoto = $student ? $student->profile_pic : null;
                $resolvedPhoto = $this->getPdfImageSrc($studentPhoto);
                if (empty($resolvedPhoto)) {
                    $resolvedPhoto = $this->getPdfImageSrc('admin/assets/images/users/user-dummy-img.jpg');
                }
                $data['student_photo_src'] = $resolvedPhoto;

                $html = view('software.module.student_report.pdf', $data)->render();
                $mpdf->WriteHTML($html);
                $count++;
            }

            if ($count === 0) {
                abort(404, 'No valid student reports generated.');
            }

            $fileName = 'Bulk_Student_Reports_' . date('Ymd_His') . '.pdf';
            $pdfContent = $mpdf->Output($fileName, 'S');

            return response($pdfContent, 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
        }
    }

    private function getPdfImageSrc($imagePath)
    {
        if (empty($imagePath)) {
            return '';
        }

        // 1. If it's a full remote URL (e.g. live server: https://admin.bcakeshod.com/...)
        if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
            try {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $imagePath);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($ch, CURLOPT_TIMEOUT, 6);
                $imageData = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE) ?: 'image/jpeg';
                curl_close($ch);

                if ($httpCode === 200 && !empty($imageData)) {
                    return 'data:' . $contentType . ';base64,' . base64_encode($imageData);
                }
            } catch (\Throwable $e) {
                // fallback to original URL
            }
            return $imagePath;
        }

        // 2. Clean relative path (handles 'public/uploads/...', '/uploads/...', etc.)
        $cleanPath = ltrim($imagePath, '/\\');
        if (str_starts_with($cleanPath, 'public/')) {
            $cleanPath = substr($cleanPath, 7);
        }

        // 3. Check if file exists locally in public_path
        $fullLocalPath = public_path($cleanPath);
        if (file_exists($fullLocalPath) && is_file($fullLocalPath)) {
            return $fullLocalPath;
        }

        return '';
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

            // Calculate working days in this month (excluding Sundays and Holidays)
            $startOfMonth = Carbon::createFromDate($year, $month, 1);
            if ($month == date('n') && $year == date('Y')) {
                $endOfMonth = Carbon::today();
            } else {
                $endOfMonth = $startOfMonth->copy()->endOfMonth();
            }

            $holidays = MasterHoliday::where('status', 'active')
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

        // 9. Achievements
        $achievementList = [];
        $achievements = \App\Models\StudentAchievement::where('admission_id', $student->id)
            ->with('event')
            ->orderBy('date', 'desc')
            ->get();

        foreach ($achievements as $ach) {
            $achievementList[] = [
                'event' => $ach->event ? $ach->event->name : 'N/A',
                'rank' => $ach->rank,
                'date' => $ach->date ? Carbon::parse($ach->date)->format('d-m-Y') : '-',
                'remark' => $ach->remark ?? '-'
            ];
        }

        // 10. Student Results (All Semesters)
        $resultList = [];
        $studentResults = \App\Models\StudentResult::where('admission_id', $student->id)
            ->where('semester', '<=', $currentSemester)
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

        return [
            'personal' => [
                'id' => $student->id,
                'gr_no' => $student->gr_no ?? '-',
                'name' => $student->first_name . ' ' . $student->last_name . ' ' . $student->father_name,
                'photo' => $student->profile_pic ? (filter_var($student->profile_pic, FILTER_VALIDATE_URL) ? $student->profile_pic : asset($student->profile_pic)) : asset('admin/assets/images/users/user-dummy-img.jpg')
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
            'assessments' => $assessmentList,
            'achievements' => $achievementList,
            'results' => $resultList
        ];
    }
}
