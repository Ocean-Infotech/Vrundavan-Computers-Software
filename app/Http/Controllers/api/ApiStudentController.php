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

/**
 * @group Student
 *
 * APIs for student academic data, fees, attendance, tests and notifications.
 */
class ApiStudentController extends Controller
{

    /**
     * Get academic details of logged-in student
     *
     * Returns the academic information (university, course, batch, class, shift timings and timetable attachment)
     * for the currently authenticated student.
     *
     * @authenticated
     *
     * @response 200 {
     *   "status": "true",
     *   "message": "Academic details fetched successfully.",
     *   "data": {
     *     "university": "Saurashtra University",
     *     "department": "Computer Science",
     *     "course": "BCA",
     *     "batch": "2024-2027",
     *     "class": "TYBCA",
     *     "shift": "09:00 AM To 12:00 PM",
     *     "gr_no": 123,
     *     "enrollment_no": "ENR2024001",
     *     "spid": "SPID12345",
     *     "udise": "UDISE1234",
     *     "apaar_id_abc_id": "APAR123456",
     *     "attachment": "https://example.com/storage/timetable.pdf"
     *   }
     * }
     *
     * @response 401 scenario="Not authenticated" {
     *   "status": "false",
     *   "message": "Unauthorization.",
     *   "data": []
     * }
     */
    public function academic_details(Request $request)
    {
        try {
            $student = Auth::guard('student-api')->user();

            if (!$student) {
                return $this->sendError("Unauthorization.", [], [], 401);
            }

            $courseRegistered = CourceRegistration::where('register_id', $student->id)
                ->with(['batch', 'course', 'class', 'shift', 'admission'])
                ->first();
// dd($courseRegistered);
            if (!$courseRegistered) {
                return $this->sendError('Your Academic Detail not found.', [], [], 422);
            } elseif ($courseRegistered?->status != "active") {
                return $this->sendError('Academic detail not active.', [], [], 422);
            } else {

                // Fetch timetable attachment
                $timetable = Timetable::where('course_id', $courseRegistered->course_id)
                    ->where('batch_id', $courseRegistered->batch_id)
                    ->where('class_id', $courseRegistered->class_id)
                    ->first();

                // Format shift timing
                $shiftTiming = '';
                if ($courseRegistered->shift?->from_time && $courseRegistered->shift?->to_time) {
                    $from = \Carbon\Carbon::parse($courseRegistered->shift->from_time)->format('h:i A');
                    $to   = \Carbon\Carbon::parse($courseRegistered->shift->to_time)->format('h:i A');
                    $shiftTiming = $from . ' To ' . $to;
                }

                $data = [
                    'university'        => $courseRegistered->university ?? '',
                    'department'        => $courseRegistered->department ?? '',
                    'course'            => $courseRegistered->course?->course_name ?? '',
                    'batch'             => $courseRegistered->batch?->batch_name ?? '',
                    'class'             => $courseRegistered->class?->class ?? '',
                    'shift'             => $shiftTiming,
                    'gr_no'             => $student->id,
                    'enrollment_no'     => $student->enrolment_no ?? '',
                    'spid'              => $student->spid ?? '',
                    'udise'             => $student->udise,
                    'apaar_id_abc_id'   => $student->apaar_id_abc_id,
                    'attachment'        => $timetable?->attachment ? asset($timetable->attachment) : '',
                ];

                return $this->sendResponse($data, 'Academic details fetched successfully.');
            }
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', $e->getMessage(), [], 500);
        }
    }
    /**
     * Get fee details of logged-in student
     *
     * Returns the total course fees, total paid and pending amount, along with a list of paid fee records
     * (semester-wise) for the currently authenticated student.
     *
     * @authenticated
     *
     * @response 200 {
     *   "status": "true",
     *   "message": "Fee details fetched successfully.",
     *   "data": {
     *     "total": 45000,
     *     "paid": 30000,
     *     "pending": 15000,
     *     "details": [
     *       {
     *         "course": "BCA",
     *         "semester": "Semester 1",
     *         "receipt_no": 10,
     *         "amount": 15000,
     *         "date": "2025-06-01",
     *         "status": "Paid"
     *       }
     *     ]
     *   }
     * }
     *
     * @response 404 scenario="No fee records" {
     *   "status": "false",
     *   "message": "No fee records found.",
     *   "data": []
     * }
     */
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
    /**
     * Get institute contact details
     *
     * Returns static contact information (branches and phone numbers) for the institute.
     *
     * @authenticated
     *
     * @response 200 {
     *   "status": "true",
     *   "message": "Contact Us Information fetched successfully.",
     *   "data": {
     *     "branches": [
     *       {
     *         "name": "Keshod Branch",
     *         "address": "Amrut nagar road, Keshod",
     *         "email": "vrundavancomputerkeshod@gmail.com"
     *       }
     *     ],
     *     "contacts": {
     *       "Vrundavan Computer": {
     *         "College mobile": "96874 51774",
     *         "Phone": "02871-231059",
     *         "Distance": "93752 21111"
     *       }
     *     }
     *   }
     * }
     */
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
                        'email'   => 'vrundavancomputerskeshod@gmail.com',
                    ],
                    [
                        'name'    => 'Vapi Branch',
                        'address' => '134, Tirupati Plaza, Daman Rd, Chala, Vapi, Gujarat - 396191',
                        'email'   => 'vrundavancomputersvapi@gmail.com',
                    ],
                    [
                        'name'    => 'BCA College',
                        'address' => 'PVM Campus, Veraval Road, Keshod',
                        'email'   => 'pvmsciencecollagekeshod@gmail.com',
                    ]
                ],
                'contacts' => [

                    'Vrundavan Computer' => [
                        'College mobile'        => '96874 51774',
                        'Phone'         => '02871-231059',
                        'Distance' => '93752 21111',
                    ]

                ]
            ];

            return $this->sendResponse($data, 'Contact Us Information fetched successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', $e->getMessage(), [], 500);
        }
    }
    /**
     * Get attendance details
     *
     * Returns attendance for a single date or for an entire month for the logged‑in student.
     * If `date` is provided, returns a single record; otherwise, returns a monthly summary and daily records.
     *
     * @authenticated
     *
     * @bodyParam date string The specific date to check (format: d-m-Y). Example: 01-07-2025
     * @bodyParam month integer The month number (1–12) for which to fetch attendance. Example: 7
     * @bodyParam year integer The year for which to fetch attendance. Example: 2025
     *
     * @response 200 scenario="Single date" {
     *   "status": "true",
     *   "message": "Attendance records fetched successfully.",
     *   "data": {
     *     "summary": {
     *       "total_lectures": 1,
     *       "present_lectures": 1,
     *       "absent_lectures": 0,
     *       "average_percent": 100,
     *       "date": "10-July-2026"
     *     },
     *     "records": [
     *       {
     *         "date": "2026-07-10",
     *         "day": "Friday",
     *         "in_time": "09:35 AM",
     *         "out_time": "12:30 PM",
     *         "status": "Present",
     *         "status_code": "P",
     *         "punch_count": 2,
     *         "punches": [
     *           { "id": 9, "biometric_id": "232", "time": "09:35 AM", "type": "In", "device_name": "Face" },
     *           { "id": 10, "biometric_id": "232", "time": "12:30 PM", "type": "Out", "device_name": "Face" }
     *         ]
     *       }
     *     ]
     *   }
     * }
     */
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

            if ($request->filled('date')) {
                try {
                    $date = \Carbon\Carbon::createFromFormat('d-m-Y', $request->date);
                } catch (\Exception $e) {
                    return $this->sendError('Invalid date provided.', [], [], 422);
                }

                $dayAttendance = Attedance::resolveDayAttendance(
                    Attedance::sortedPunchesForDate($student->id, $date->format('Y-m-d'))
                );

                $dataList = [
                    [
                        'admission_id' => $student->id,
                        'biometric_id' => $student->biometric_id,
                        'date'        => $date->format('Y-m-d'),
                        'day'         => $date->format('l'),
                        'in_time'     => Attedance::formatTimeForApi($dayAttendance['in_time']),
                        'out_time'    => Attedance::formatTimeForApi($dayAttendance['out_time']),
                        'status'      => $dayAttendance['is_present'] ? 'Present' : 'Absent',
                        'status_code' => $dayAttendance['is_present'] ? 'P' : 'A',
                        'punch_count' => $dayAttendance['punch_count'],
                        'punches'     => collect($dayAttendance['punches'])->map(function ($punch) {
                            return [
                                'id' => $punch['id'],
                                'biometric_id' => $punch['biometric_id'] ?? null,
                                'time' => Attedance::formatTimeForApi($punch['time']),
                                'type' => $punch['type'],
                                'device_key' => $punch['device_key'],
                                'device_name' => $punch['device_name'],
                            ];
                        })->values()->all(),
                    ]
                ];

                $summaryData = [
                    'total_lectures'   => 1,
                    'present_lectures' => $dayAttendance['is_present'] ? 1 : 0,
                    'absent_lectures'  => $dayAttendance['is_present'] ? 0 : 1,
                    'average_percent'  => $dayAttendance['is_present'] ? 100 : 0,
                    'date'             => $date->format('d-F-Y')
                ];

                return $this->sendResponse([
                    'summary' => $summaryData,
                    'records' => $dataList
                ], 'Attendance records fetched successfully.');
            }

            $month = $request->month ?? date('n');
            $year  = $request->year ?? date('Y');

            $attendanceRecords = Attedance::where('admission_id', $student->id)
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->orderBy('date', 'desc')
                ->orderBy('in_time')
                ->orderBy('id')
                ->get()
                ->groupBy(fn ($att) => \Carbon\Carbon::parse($att->date)->format('Y-m-d'));

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

            $presentCount = 0;
            $dataList = [];

            foreach ($allDates as $date) {
                $dayPunches = $attendanceRecords->get($date, collect());
                $dayAttendance = Attedance::resolveDayAttendance($dayPunches);

                if ($dayAttendance['is_present']) {
                    $presentCount++;
                }

                $dataList[] = [
                    'admission_id' => $student->id,
                    'biometric_id' => $student->biometric_id,
                    'date'        => $date,
                    'day'         => \Carbon\Carbon::parse($date)->format('l'),
                    'in_time'     => Attedance::formatTimeForApi($dayAttendance['in_time']),
                    'out_time'    => Attedance::formatTimeForApi($dayAttendance['out_time']),
                    'status'      => $dayAttendance['is_present'] ? 'Present' : 'Absent',
                    'status_code' => $dayAttendance['is_present'] ? 'P' : 'A',
                    'punch_count' => $dayAttendance['punch_count'],
                    'punches'     => collect($dayAttendance['punches'])->map(function ($punch) {
                        return [
                            'id' => $punch['id'],
                            'biometric_id' => $punch['biometric_id'] ?? null,
                            'time' => Attedance::formatTimeForApi($punch['time']),
                            'type' => $punch['type'],
                            'device_key' => $punch['device_key'],
                            'device_name' => $punch['device_name'],
                        ];
                    })->values()->all(),
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


    /**
     * Get Google Drive document folder URL
     *
     * Returns the Google Drive folder URL associated with the logged‑in student (if configured).
     *
     * @authenticated
     *
     * @response 200 {
     *   "status": "true",
     *   "message": "Google Drive folder link fetched successfully.",
     *   "data": {
     *     "gdrive_url": "https://drive.google.com/drive/folders/abc123"
     *   }
     * }
     *
     * @response 422 scenario="No folder configured" {
     *   "status": "false",
     *   "message": "No Google Drive folder found.",
     *   "data": []
     * }
     */
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
    /**
     * Get test subjects
     *
     * Returns the list of subjects for which the logged‑in student has test records.
     *
     * @authenticated
     *
     * @response 200 {
     *   "status": "true",
     *   "message": "Subjects List.",
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "Mathematics"
     *     }
     *   ]
     * }
     *
     * @response 404 scenario="No subjects" {
     *   "status": "false",
     *   "message": "No subjects found.",
     *   "data": []
     * }
     */
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
                ->with('test:id,subject_name')
                ->get()
                ->pluck('test')
                ->filter() // Remove nulls if any (incomplete relations)
                ->unique('subject_name') // Unique by subject_name
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
    
    /**
     * Get test details
     *
     * Returns detailed test results (subject, unit, course, batch, date, marks and percentage) for the logged‑in student.
     * You can optionally filter by subject or search by subject name.
     *
     * @authenticated
     *
     * @bodyParam subject_id integer The subject ID to filter tests by. Example: 1
     * @bodyParam subject_name string The exact subject name to filter tests by. Example: Mathematics
     * @bodyParam search string A text to search in subject names (partial match). Example: Maths
     *
     * @response 200 {
     *   "status": "true",
     *   "message": "Test details fetched successfully.",
     *   "data": [
     *     {
     *       "subject": "Mathematics",
     *       "unit": "Unit 1",
     *       "course": "BCA",
     *       "batch": "2024-2027",
     *       "date": "01-07-2025",
     *       "marks": "18/20",
     *       "percentage": "90%"
     *     }
     *   ]
     * }
     */
    public function test_details(Request $request)
    {
        try {
            $student = Auth::guard('student-api')->user();

            if (!$student) {
                return $this->sendError("Unauthorized.", [], [], 401);
            }

            $validator = Validator::make($request->all(), [
                'subject_id'    => 'nullable|integer|exists:test,id',
                'subject_name'  => 'nullable|string|max:255',
                'search'        => 'nullable|string|max:255',
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
                    if ($request->filled('subject_name')) {
                        $q->where('subject_name', $request->subject_name);
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

    /**
     * Get notifications list
     *
     * Returns all notifications visible to the logged‑in student (personal, course‑level and general).
     *
     * @group Notifications
     * @authenticated
     *
     * @response 200 {
     *   "status": "true",
     *   "message": "Notifications List.",
     *   "data": [
     *     {
     *       "id": 1,
     *       "title": "Exam schedule",
     *       "body": "Your mid‑semester exams start next week.",
     *       "is_read": false,
     *       "date_time": "2025-07-01 10:00:00"
     *     }
     *   ]
     * }
     */
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
    /**
     * Mark notifications as read
     *
     * Marks specific notifications as read for the logged‑in student. If no IDs are provided,
     * all unread notifications for the student are marked as read.
     *
     * @group Notifications
     * @authenticated
     *
     * @bodyParam notification_ids array The IDs of notifications to mark as read. Example: [1, 2, 3]
     * @bodyParam notification_ids.* integer A single notification ID.
     *
     * @response 200 {
     *   "status": "true",
     *   "message": "Notifications marked as read successfully.",
     *   "data": [
     *     {
     *       "id": 1,
     *       "title": "Exam schedule",
     *       "body": "Your mid‑semester exams start next week.",
     *       "is_read": true
     *     }
     *   ]
     * }
     */
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
                "Notifications marked as read successfully."
            );
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', $e->getMessage(), [], 500);
        }
    }
}
