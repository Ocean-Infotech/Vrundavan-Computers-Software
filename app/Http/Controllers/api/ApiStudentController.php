<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\admin\StudentsFeesReportController;
use App\Http\Controllers\Controller;
use App\Models\Admission;
use App\Models\Attedance;
use App\Models\CourceRegistration;
use App\Models\FeesCollection;
use App\Models\Master\MasterCourse;
use App\Models\NotificationSetting;
use App\Models\StudentTestMark;
use App\Models\Timetable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ApiStudentController extends Controller
{

    public function academic_details(Request $request)
    {
        try {
            $student = Auth::guard('student-api')->user();

            if (!$student) {
                return $this->sendError("Unauthorization.", [], [], 401);
            }

            $courseRegistered = CourceRegistration::where('register_id', $student->id)
                ->first();

            if (!$courseRegistered) {
                return $this->sendError('Your Academic Detail not found.', [], [], 422);
            } elseif ($courseRegistered?->status != "active") {
                return $this->sendError('Academic detail not active.', [], [], 422);
            } else {
                $course = MasterCourse::where('id', $courseRegistered->course_id)->first();

                // Fetch timetable attachment (by course_id and batch_id both)
                $timetable = Timetable::where('course_id', $courseRegistered->course_id)
                    ->where('batch_id', $courseRegistered->batch_id)
                    ->where('class_id', $courseRegistered->class_id)
                    ->first();

                $data = [
                    'university'     => $courseRegistered->university ?? '',
                    'department'     => $courseRegistered->department ?? '',
                    'course'         => $course->course_name ?? '',
                    // 'semester'       => $course->semester ?? '',
                    'gr_no'          => $student->id,
                    'enrollment_no'  => $student->enrolment_no ?? '',
                    'spid'           => $student->spid ?? '',
                    'attachment'     => $timetable?->attachment ? asset($timetable->attachment) : '',
                ];

                return $this->sendResponse($data, 'Academic details fetched successfully.');
            }
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', $e->getMessage(), [], 500);
        }
    }


    public function fee_details(Request $request)
    {
        try {
            $student = Auth::guard('student-api')->user();

            if (!$student) {
                return $this->sendError("Unauthorization.", [], [], 401);
            }

            $feeRecords = FeesCollection::with('course')
                ->where('student_id', $student->id)
                ->get();

            if ($feeRecords->isEmpty()) {
                return $this->sendError('No fee records found.', [], [], 404);
            }

            $courseFees = $feeRecords->first()->course->course_fees;

            $paidFees = $feeRecords->where('status', true)->sum('fees');

            $pendingFees = $courseFees - $paidFees;

            $feesDetails = $feeRecords->map(function ($fee) {
                return [
                    'course'     => $fee->course->course_name,
                    'semester'   => 'Semester ' . $fee->year_semester,
                    'receipt_no' => $fee->id,
                    'amount'     => (int)$fee->fees,
                    'date'       => $fee->date ? \Carbon\Carbon::parse($fee->date)->format('Y-m-d') : null,
                    'status'     => 'Paid',
                ];
            });

            $data = [
                'total'   => $courseFees,
                'paid'    => $paidFees,
                'pending' => $pendingFees,
                'details' => $feesDetails,
            ];

            return $this->sendResponse($data, 'Fee details fetched successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', $e->getMessage(), [], 500);
        }
    }
    public function contact_details(Request $request)
    {
        try {
            // dd('hello');
            $student = Auth::guard('student-api')->user();

            if (!$student) {
                return $this->sendError("Unauthorization.", [], [], 401);
            }
            $data = [
                'branches' => [
                    [
                        'name'    => 'Keshod Branch',
                        'address' => 'Amrut nagar road, Keshod',
                        'email'   => 'vrundavancomputerkeshod@gmail.com',
                    ],
                    [
                        'name'    => 'Vapi Branch',
                        'address' => '134, Tirupati Plaza, Daman Rd, Chala, Vapi, Gujarat - 396191',
                        'email'   => 'vrundavancomputervapi@gmail.com',
                    ],
                    [
                        'name'    => 'BCA College',
                        'address' => 'PVM Campus, Veraval Road, Keshod',
                        'email'   => 'pvmbcacollage@gmail.com',
                    ]
                ],
                'contacts' => [

                    'Vrundavan Computer' => [
                        'mobile'        => '96374 51774',
                        'phone'         => '02871-231059',
                        'distance_work' => '93762 21111',
                    ]

                ]
            ];

            return $this->sendResponse($data, 'Contact Us Information fetched successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', $e->getMessage(), [], 500);
        }
    }
    public function attendance_details(Request $request)
    {
        try {
            $student = Auth::guard('student-api')->user();

            if (!$student) {
                return $this->sendError("Unauthorized.", [], [], 401);
            }

            // Validate inputs
            $validator = Validator::make($request->all(), [
                'date'  => ['nullable', 'date_format:d-m-Y'], // dd-mm-yyyy
                'month' => ['nullable', 'integer', 'between:1,12'],
                'year'  => ['nullable', 'integer', 'digits:4'],
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors(), [], 422);
            }

            $query = Attedance::where('admission_id', $student->id);


            if ($request->filled('date')) {
                try {
                    $date = \Carbon\Carbon::createFromFormat('d-m-Y', $request->date);
                } catch (\Exception $e) {
                    return $this->sendError('Invalid date provided.', [], [], 422);
                }

                $query->whereDate('date', $date->format('Y-m-d'));
                $attendanceRecords = $query->orderBy('date', 'desc')->get();

                if ($attendanceRecords->isEmpty()) {
                    return $this->sendError(
                        "No attendance records found for " . $date->format('d-F-Y'),
                        [],
                        [],
                        404
                    );
                }

                $dataList = $attendanceRecords->map(function ($att) {
                    $isPresent = !empty($att->in_time);
                    return [
                        'date'        => $att->date ? \Carbon\Carbon::parse($att->date)->format('Y-m-d') : null,
                        'day'         => \Carbon\Carbon::parse($att->date)->format('l'),
                        'in_time'     => $att->in_time ? date('h:i A', strtotime($att->in_time)) : null,
                        'out_time'    => $att->out_time ? date('h:i A', strtotime($att->out_time)) : null,
                        'status'      => $isPresent ? 'Present' : 'Absent',
                        'status_code' => $isPresent ? 'P' : 'A',
                    ];
                });

                $totalLectures  = $attendanceRecords->count();
                $presentCount   = $attendanceRecords->whereNotNull('in_time')->count();
                $absentCount    = $totalLectures - $presentCount;
                $averagePercent = $totalLectures > 0 ? round(($presentCount / $totalLectures) * 100, 2) : 0;

                $summaryData = [
                    'total_lectures'   => $totalLectures,
                    'present_lectures' => $presentCount,
                    'absent_lectures'  => $absentCount,
                    'average_percent'  => $averagePercent,
                    'date'             => $date->format('d-F-Y')
                ];

                return $this->sendResponse([
                    'summary' => $summaryData,
                    'records' => $dataList
                ], 'Attendance records fetched successfully.');
            }


            $month = $request->month ?? date('n');
            $year  = $request->year ?? date('Y');

            $query->whereMonth('date', $month)->whereYear('date', $year);
            $attendanceRecords = $query->orderBy('date', 'desc')->get();

            $startOfMonth = \Carbon\Carbon::createFromDate($year, $month, 1);

            if ($month == date('n') && $year == date('Y')) {
                $endOfMonth = \Carbon\Carbon::today()->endOfDay(); // include today
            } else {
                $endOfMonth = $startOfMonth->copy()->endOfMonth();
            }


            // Generate all working dates
            $allDates = [];
            $currentDate = $startOfMonth->copy();
            while ($currentDate <= $endOfMonth) {
                if ($currentDate->dayOfWeek !== \Carbon\Carbon::SUNDAY) {
                    $allDates[] = $currentDate->format('Y-m-d');
                }
                $currentDate->addDay();
            }

            // Reverse for DESC order
            $allDates = array_reverse($allDates);

            $totalLectures = count($allDates);

            // Map DB records by date
            $recordsByDate = $attendanceRecords->keyBy(function ($att) {
                return \Carbon\Carbon::parse($att->date)->format('Y-m-d');
            });

            $presentCount = 0;
            $dataList = [];

            foreach ($allDates as $date) {
                $record = $recordsByDate->get($date);
                $isPresent = $record && !empty($record->in_time);

                if ($isPresent) {
                    $presentCount++;
                }

                $dataList[] = [
                    'date'        => $date,
                    'day'         => \Carbon\Carbon::parse($date)->format('l'),
                    'in_time'     => $record && $record->in_time ? date('h:i A', strtotime($record->in_time)) : null,
                    'out_time'    => $record && $record->out_time ? date('h:i A', strtotime($record->out_time)) : null,
                    'status'      => $isPresent ? 'Present' : 'Absent',
                    'status_code' => $isPresent ? 'P' : 'A',
                ];
            }

            $absentCount    = $totalLectures - $presentCount;
            $averagePercent = $totalLectures > 0 ? round(($presentCount / $totalLectures) * 100, 2) : 0;

            $summaryData = [
                'total_lectures'   => $totalLectures,
                'present_lectures' => $presentCount,
                'absent_lectures'  => $absentCount,
                'average_percent'  => $averagePercent,
                'month_year'       => $startOfMonth->format('F-Y')
            ];

            return $this->sendResponse([
                'summary' => $summaryData,
                'records' => $dataList
            ], 'Attendance records fetched successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', $e->getMessage(), [], 500);
        }
    }


    public function document_details(Request $request)
    {
        try {
            $student = Auth::guard('student-api')->user();

            if (!$student) {
                return $this->sendError("Unauthorized.", [], [], 401);
            }

            $gdriveUrl = $student->gdrivefolderurl ?? null;

            if (empty($gdriveUrl)) {
                return $this->sendError("No Google Drive folder found.", [], [], 422);
            }

            if (!preg_match('/^https?:\/\/drive\.google\.com\/drive\/folders\/[a-zA-Z0-9_\-]+/', $gdriveUrl)) {
                return $this->sendError("Invalid Google Drive folder URL.", [], [], 422);
            }

            return $this->sendResponse(['gdrive_url' => $gdriveUrl], 'Google Drive folder link fetched successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', $e->getMessage(), [], 500);
        }
    }
    public function test_subjects(Request $request)
    {
        try {
            $student = Auth::guard('student-api')->user();

            if (!$student) {
                return $this->sendError("Unauthorized.", [], [], 401);
            }

            $subjects = StudentTestMark::where('register_id', $student->id)
                ->whereHas('test', function ($q) {
                    $q->whereNull('deleted_at');
                })
                ->with('test:id,subject_name') // only required fields
                ->get()
                ->pluck('test')
                ->unique('id')
                ->map(function ($test) {
                    return [
                        'id'   => $test->id,
                        'name' => $test->subject_name,
                    ];
                })
                ->values();

            if ($subjects->isEmpty()) {
                return $this->sendError("No subjects found.", [], [], 404);
            }

            return $this->sendResponse($subjects, "Subjects List.");
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', $e->getMessage(), [], 500);
        }
    }
    public function test_details(Request $request)
    {
        try {
            $student = Auth::guard('student-api')->user();

            if (!$student) {
                return $this->sendError("Unauthorized.", [], [], 401);
            }

            $validator = Validator::make($request->all(), [
                'subject_id' => 'nullable|integer|exists:test,id',
                'search'     => 'nullable|string|max:255',

            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors(), [], 422);
            }

            $query = StudentTestMark::with([
                'test.course',
                'test.batch'
            ])
                ->where('register_id', $student->id)
                ->whereHas('test', function ($q) use ($request) {
                    $q->whereNull('deleted_at');

                    if ($request->filled('subject_id')) {
                        $q->where('id', $request->subject_id);
                    }
                    if ($request->filled('search')) {
                        $q->where('subject_name', 'like', '%' . $request->search . '%');
                    }
                });

            $testRecords = $query->latest()->get();

            if ($testRecords->isEmpty()) {
                return $this->sendError("No test records found.", [], [], 404);
            }

            $data = $testRecords->map(function ($record) {
                $test = $record->test;
                $percentage = ($test->mark > 0)
                    ? round(($record->marks / $test->mark) * 100, 2)
                    : 0;

                return [
                    'subject'    => $test->subject_name,
                    'unit'       => $test->unit_name,
                    'course'     => $test->course->course_name ?? null,
                    'batch'      => $test->batch->batch_name ?? null,
                    'date'       => $test->date ? \Carbon\Carbon::parse($test->date)->format('d-m-Y') : null,
                    'marks'      => "{$record->marks}/{$test->mark}",
                    'percentage' => $percentage . '%',
                ];
            });

            return $this->sendResponse($data, "Test details fetched successfully.");
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', $e->getMessage(), [], 500);
        }
    }
    public function notifications_list(Request $request)
    {
        try {
            $student = Auth::guard('student-api')->user();

            if (!$student) {
                return $this->sendError("Unauthorized.", [], [], 401);
            }

            $notifications = NotificationSetting::query()
                ->where(function ($q) use ($student) {
                    $q->where('student_id', $student->id)
                        ->orWhereNull('student_id')
                        ->orWhere('course_id', $student->course_id ?? 0);
                })
                ->where('is_read', 0)
                ->latest()
                ->get();

            if ($notifications->isEmpty()) {
                return $this->sendError("No notifications found.", [], [], 404);
            }

            $data = $notifications->map(function ($row) {
                $cleanBody = $row->body;
                $cleanBody = str_ireplace(['<br>', '<br/>', '<br />', '</p>'], "\n", $cleanBody);
                $cleanBody = str_ireplace('<p>', '', $cleanBody);
                $cleanBody = strip_tags($cleanBody);
                $cleanBody = html_entity_decode($cleanBody, ENT_QUOTES, 'UTF-8');
                $cleanBody = preg_replace("/[ \t]+/", " ", $cleanBody);
                $cleanBody = preg_replace("/\r\n|\r/", "\n", $cleanBody);
                $cleanBody = trim($cleanBody);

                return [
                    'id'    => $row->id,
                    'title' => $row->title,
                    'body'  => $cleanBody,
                    'is_read' => (bool) $row->is_read,
                    'date_time' => $row->created_at->format('Y-m-d H:i:s'),
                ];
            });

            return $this->sendResponse($data, "Notifications List.");
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', $e->getMessage(), [], 500);
        }
    }
    public function notifications_mark_read(Request $request)
    {
        try {
            $student = Auth::guard('student-api')->user();
            if (!$student) {
                return $this->sendError("Unauthorized.", [], [], 401);
            }

            $notificationIds = $request->input('notification_ids', []);

            if (!empty($notificationIds)) {
                $updated = NotificationSetting::whereIn('id', $notificationIds)
                    ->where(function ($q) use ($student) {
                        $q->where('student_id', (string) $student->id)
                            ->orWhereNull('student_id')
                            ->orWhere('course_id', (string) ($student->course_id ?? '0'));
                    })
                    ->update(['is_read' => 1]);

                if ($updated == 0) {
                    return $this->sendError("No notifications found to mark as read.", [], [], 404);
                }
            } else {
                $updated = NotificationSetting::where(function ($q) use ($student) {
                    $q->where('student_id', (string) $student->id)
                        ->orWhereNull('student_id')
                        ->orWhere('course_id', (string) ($student->course_id ?? '0'));
                })
                    ->where('is_read', 0)
                    ->update(['is_read' => 1]);
            }

            $readNotifications = NotificationSetting::query()
                ->where('is_read', 1)
                ->where(function ($q) use ($student) {
                    $q->where('student_id', (string) $student->id)
                        ->orWhereNull('student_id')
                        ->orWhere('course_id', (string) ($student->course_id ?? '0'));
                })
                ->orderBy('created_at', 'desc')
                ->get()
                ->makeHidden(['created_by', 'updated_by', 'deleted_by', 'created_at', 'updated_at']);

            foreach ($readNotifications as $row) {
                $cleanBody = $row->body;
                $cleanBody = str_ireplace(['<br>', '<br/>', '<br />', '</p>'], "\n", $cleanBody);
                $cleanBody = str_ireplace('<p>', '', $cleanBody);
                $cleanBody = strip_tags($cleanBody);
                $cleanBody = html_entity_decode($cleanBody, ENT_QUOTES, 'UTF-8');
                $cleanBody = preg_replace("/[ \t]+/", " ", $cleanBody);
                $cleanBody = preg_replace("/\r\n|\r/", "\n", $cleanBody);
                $cleanBody = trim($cleanBody);

                $row->body = $cleanBody;
            }

            return $this->sendResponse(
                $readNotifications,
                "Notifications marked as read successfully. (" . ($updated ?? 0) . " updated)"
            );
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', $e->getMessage(), [], 500);
        }
    }
}
