<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\admin\StudentsFeesReportController;
use App\Http\Controllers\Controller;
use App\Models\Admission;
use App\Models\Attedance;
use App\Models\CourceRegistration;
use App\Models\FeesCollection;
use App\Models\Master\MasterCourse;
use App\Models\Master\MasterHoliday;
use App\Models\NotificationSetting;
use App\Models\StudentTestMark;
use App\Models\Timetable;
use App\Models\Test;
use Mpdf\Mpdf;
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
            } elseif (!in_array(strtolower($courseRegistered?->status), ['running', 'active'])) {
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
                    'gr_no'             => $student->gr_no ?? '',
                    'biometric_id'      => $student->biometric_id ?? '',
                    'enrollment_no'     => $student->enrolment_no ?? '',
                    'spid'              => $student->spid ?? '',
                    'udise'             => $student->udise,
                    'apaar_id_abc_id'   => $student->apaar_id_abc_id,
                    'profile_pic'       => $student->profile_pic_url ?? '',
                    'attachment'        => $timetable?->attachment ? asset($timetable->attachment) : '',
                ];

                return $this->sendResponse($data, 'Academic details fetched successfully.');
            }
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', $e->getMessage(), [], 500);
        }
    }

    /**
     * Get timetable details of logged-in student
     *
     * Returns timetable attachment for authenticated student.
     *
     * @authenticated
     *
     * @response 200 {
     *   "status": "true",
     *   "message": "Timetable details fetched successfully.",
     *   "data": {
     *     "attachment": "https://example.com/storage/timetable.pdf"
     *   }
     * }
     */
    public function timetable_details(Request $request)
    {
        try {
            $student = Auth::guard('student-api')->user();

            if (!$student) {
                return $this->sendError("Unauthorization.", [], [], 401);
            }

            $courseRegistered = CourceRegistration::where('register_id', $student->id)
                ->with(['batch', 'course', 'class'])
                ->first();

            if (!$courseRegistered) {
                return $this->sendError('Your Academic Detail not found.', [], [], 422);
            }

            $timetable = Timetable::where('course_id', $courseRegistered->course_id)
                ->where('batch_id', $courseRegistered->batch_id)
                ->where('class_id', $courseRegistered->class_id)
                ->first();

            if (!$timetable || !$timetable->attachment) {
                return $this->sendError('No record found.', [], [], 404);
            }

            return response()->json([
                'status' => 'true',
                'message' => 'Timetable details fetched successfully.',
                'attachment' => asset($timetable->attachment),
            ], 200);
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
     *         "status": "Paid",
     *         "receipt_url": "http://127.0.0.1:8000/api/student/fee-receipt/10/download"
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

            $feeRecordsQuery = FeesCollection::with('course')
                ->where('student_id', $student->id);

            if ($request->filled('semester')) {
                $feeRecordsQuery->where('year_semester', $request->semester);
            }

            $feeRecords = $feeRecordsQuery->get();

            if ($feeRecords->isEmpty()) {
                return $this->sendError('No fee records found.', [], [], 404);
            }

            $courseFees = $feeRecords->first()->course->course_fees ?? 0;


            $paidFees = $feeRecords->where('status', true)->sum('fees');

            $pendingFees = $courseFees - $paidFees;

            $feesDetails = $feeRecords->map(function ($fee) {
                return [
                    'course'      => $fee->course->course_name ?? '',
                    'semester'    => 'Semester ' . $fee->year_semester,
                    'receipt_no'  => $fee->id,
                    'amount'      => (int)$fee->fees,
                    'date'        => $fee->date ? \Carbon\Carbon::parse($fee->date)->format('Y-m-d') : null,
                    'status'      => 'Paid',
                    'receipt_url' => route('student.fee-receipt.download', ['id' => $fee->id]),
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
                        'School Number'         => '96246 51774',
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

                $dateStr = $date->format('Y-m-d');
                $dayAttendance = Attedance::resolveDayAttendance(
                    Attedance::sortedPunchesForDate($student->id, $dateStr, $student->biometric_id)
                );

                $holiday = MasterHoliday::where('status', 'active')
                    ->where('from_date', '<=', $dateStr)
                    ->where('to_date', '>=', $dateStr)
                    ->first();

                $isSunday = ($date->dayOfWeek === \Carbon\Carbon::SUNDAY);

                if ($dayAttendance['is_present']) {
                    $status = 'Present';
                    $statusCode = 'P';
                } elseif ($holiday) {
                    $status = 'Holiday (' . $holiday->name . ')';
                    $statusCode = 'H';
                } elseif ($isSunday) {
                    $status = 'Sunday';
                    $statusCode = 'S';
                } else {
                    $status = 'Absent';
                    $statusCode = 'A';
                }

                $dataList = [
                    [
                        'admission_id' => $student->id,
                        'biometric_id' => $student->biometric_id,
                        'date'        => $dateStr,
                        'day'         => $date->format('l'),
                        'in_time'     => Attedance::formatTimeForApi($dayAttendance['in_time']),
                        'out_time'    => Attedance::formatTimeForApi($dayAttendance['out_time']),
                        'status'      => $status,
                        'status_code' => $statusCode,
                        'is_holiday'  => ($holiday || $isSunday) ? 'Yes' : 'No',
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
                    'absent_lectures'  => ($dayAttendance['is_present'] || $holiday || $isSunday) ? 0 : 1,
                    'average_percent'  => $dayAttendance['is_present'] ? 100 : 0,
                    'date'             => $date->format('d-F-Y'),
                    'day'              => $date->format('l'),
                ];

                return $this->sendResponse([
                    'summary' => $summaryData,
                    'records' => $dataList
                ], 'Attendance records fetched successfully.');
            }

            $month = $request->month ?? date('n');
            $year  = $request->year ?? date('Y');

            $attendanceRecords = Attedance::where(function ($query) use ($student) {
                $query->where('admission_id', $student->id);
                if (!empty($student->biometric_id)) {
                    $query->orWhere('biometric_id', $student->biometric_id);
                }
            })
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->orderBy('date', 'desc')
                ->orderBy('in_time')
                ->orderBy('id')
                ->get()
                ->groupBy(fn($att) => \Carbon\Carbon::parse($att->date)->format('Y-m-d'));

            $startOfMonth = \Carbon\Carbon::createFromDate($year, $month, 1);

            if ($month == date('n') && $year == date('Y')) {
                $endOfMonth = \Carbon\Carbon::today()->endOfDay(); // include today
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
                $curr = \Carbon\Carbon::parse($h->from_date);
                $to = \Carbon\Carbon::parse($h->to_date);
                while ($curr <= $to) {
                    $holidayMap[$curr->format('Y-m-d')] = $h->name;
                    $curr->addDay();
                }
            }

            // Generate all working dates (excluding Sundays)
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

            $presentCount = 0;
            $holidayCount = 0;
            $dataList = [];

            foreach ($allDates as $date) {
                $dayPunches = $attendanceRecords->get($date, collect());
                $dayAttendance = Attedance::resolveDayAttendance($dayPunches);
                $isHoliday = isset($holidayMap[$date]);

                if ($dayAttendance['is_present']) {
                    $presentCount++;
                    $status = 'Present';
                    $statusCode = 'P';
                } elseif ($isHoliday) {
                    $holidayCount++;
                    $status = 'Holiday (' . $holidayMap[$date] . ')';
                    $statusCode = 'H';
                } else {
                    $status = 'Absent';
                    $statusCode = 'A';
                }

                $dataList[] = [
                    'admission_id' => $student->id,
                    'biometric_id' => $student->biometric_id,
                    'date'        => $date,
                    'day'         => \Carbon\Carbon::parse($date)->format('l'),
                    'in_time'     => Attedance::formatTimeForApi($dayAttendance['in_time']),
                    'out_time'    => Attedance::formatTimeForApi($dayAttendance['out_time']),
                    'status'      => $status,
                    'status_code' => $statusCode,
                    'is_holiday'  => $isHoliday ? 'Yes' : 'No',
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

            $totalLectures   = count($allDates);
            $workingLectures = max(0, $totalLectures - $holidayCount);
            $absentCount     = max(0, $workingLectures - $presentCount);
            $averagePercent  = $workingLectures > 0 ? round(($presentCount / $workingLectures) * 100, 2) : 0;

            $summaryData = [
                'total_lectures'   => $totalLectures,
                'working_lectures' => $workingLectures,
                'holiday_lectures' => $holidayCount,
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
     * Get Test Schedule
     *
     * Returns scheduled tests for the logged-in student based on their registered course and batch.
     *
     * @authenticated
     */
    public function test_schedule(Request $request)
    {
        try {
            $student = Auth::guard('student-api')->user();

            if (!$student) {
                return $this->sendError("Unauthorized.", [], [], 401);
            }

            $courseRegistered = CourceRegistration::where('register_id', $student->id)
                ->with(['batch', 'course'])
                ->first();

            if (!$courseRegistered) {
                return $this->sendError('Your Academic Detail not found.', [], [], 422);
            }

            $tests = Test::where('course_id', $courseRegistered->course_id)
                ->where('batch_id', $courseRegistered->batch_id)
                ->where('status', 'active')
                ->where('date', '>=', \Carbon\Carbon::today()->format('Y-m-d'))
                ->with(['course', 'batch'])
                ->orderBy('date', 'asc')
                ->get();

            if ($tests->isEmpty()) {
                return $this->sendError('No record found.', [], [], 404);
            }

            $data = $tests->map(function ($test) {
                return [
                    'subject_name' => $test->subject_name ?? '',
                    'test_type'    => $test->test_type ?? '',
                    'unit_name'    => $test->unit_name ?? '',
                    'mark'         => $test->mark ?? '',
                    'date'         => $test->date ? \Carbon\Carbon::parse($test->date)->format('d-m-Y') : '',
                ];
            });

            return $this->sendResponse($data, 'Test schedule fetched successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', $e->getMessage(), [], 500);
        }
    }

    /**
     * Get assignment details for student
     *
     * Returns assignments and status for logged-in student.
     *
     * @group Assignments
     * @authenticated
     */
    public function assignment_details(Request $request)
    {
        try {
            $student = Auth::guard('student-api')->user();

            if (!$student) {
                return $this->sendError("Unauthorized.", [], [], 401);
            }

            $validator = Validator::make($request->all(), [
                'subject' => 'nullable|string|max:255',
                'search'  => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors(), [], 422);
            }

            $registration = \App\Models\CourceRegistration::where('register_id', $student->id)
                ->where('status', 'active')
                ->first();

            if (!$registration) {
                return $this->sendError("Student registration not found.", [], [], 404);
            }

            $query = \App\Models\Assignment::with(['course', 'batch'])
                ->where('course_id', $registration->course_id)
                ->where('batch_id', $registration->batch_id)
                ->whereNull('deleted_at');

            if ($request->filled('subject')) {
                $query->where('subject', $request->subject);
            }
            if ($request->filled('search')) {
                $query->where('subject', 'like', '%' . $request->search . '%');
            }

            $assignments = $query->orderBy('date', 'desc')->get();

            if ($assignments->isEmpty()) {
                return $this->sendError("No assignments found.", [], [], 404);
            }

            $reports = \App\Models\StudentAssignmentReport::where('gr_no', $student->gr_no)
                ->whereIn('assignment_id', $assignments->pluck('id'))
                ->get()
                ->keyBy('assignment_id');

            $data = $assignments->map(function ($assignment) use ($reports) {
                $report = $reports->get($assignment->id);
                return [
                    'assignment_id' => $assignment->id,
                    'subject'       => $assignment->subject,
                    'unit'          => $assignment->unit ?? '',
                    // 'course'        => $assignment->course->course_name ?? null,
                    // 'batch'         => $assignment->batch->batch_name ?? null,
                    'date'          => $assignment->date ? \Carbon\Carbon::parse($assignment->date)->format('d-m-Y') : null,
                    'status'        => $report ? ucfirst($report->status) : 'Pending',
                    'remarks'       => $report ? $report->remarks : '',
                ];
            });

            return $this->sendResponse($data, "Assignment details fetched successfully.");
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
                    $q->whereRaw("FIND_IN_SET(?, student_id)", [$student->id])
                        ->orWhereNull('student_id')
                        ->orWhere('student_id', '')
                        ->orWhere('course_id', $student->course_id ?? 0);
                })
                ->latest()
                ->get();

            if ($notifications->isEmpty()) {
                return $this->sendError("No notifications found.", [], [], 404);
            }

            $readNotificationIds = \App\Models\NotificationRead::where('student_id', $student->id)
                ->pluck('notification_id')
                ->toArray();

            $data = $notifications->map(function ($row) use ($readNotificationIds) {
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
                    'file_path' => $row->image ? asset($row->image) : ($row->pdf ? asset($row->pdf) : ''),
                    'is_read' => in_array($row->id, $readNotificationIds),
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

            $query = NotificationSetting::query()
                ->where(function ($q) use ($student) {
                    $q->whereRaw("FIND_IN_SET(?, student_id)", [$student->id])
                        ->orWhereNull('student_id')
                        ->orWhere('student_id', '')
                        ->orWhere('course_id', (string) ($student->course_id ?? '0'));
                });

            if (!empty($notificationIds)) {
                $query->whereIn('id', $notificationIds);
            }

            $notificationsToMark = $query->pluck('id')->toArray();

             foreach ($notificationsToMark as $notifId) {
                \App\Models\NotificationRead::updateOrCreate(
                    [
                        'notification_id' => $notifId,
                        'student_id' => $student->id,
                    ],
                    []
                );
            }

            $readNotificationIds = \App\Models\NotificationRead::where('student_id', $student->id)
                ->pluck('notification_id')
                ->toArray();

            $readNotifications = NotificationSetting::query()
                ->whereIn('id', $readNotificationIds)
                ->where(function ($q) use ($student) {
                    $q->whereRaw("FIND_IN_SET(?, student_id)", [$student->id])
                        ->orWhereNull('student_id')
                        ->orWhere('student_id', '')
                        ->orWhere('course_id', (string) ($student->course_id ?? '0'));
                })
                ->orderBy('created_at', 'desc')
                ->get()
                ->makeHidden(['created_by', 'updated_by', 'deleted_by', 'created_at', 'updated_at']);

            $data = $readNotifications->map(function ($row) {
                $cleanBody = $row->body;
                $cleanBody = str_ireplace(['<br>', '<br/>', '<br />', '</p>'], "\n", $cleanBody);
                $cleanBody = str_ireplace('<p>', '', $cleanBody);
                $cleanBody = strip_tags($cleanBody);
                $cleanBody = html_entity_decode($cleanBody, ENT_QUOTES, 'UTF-8');
                $cleanBody = preg_replace("/[ \t]+/", " ", $cleanBody);
                $cleanBody = preg_replace("/\r\n|\r/", "\n", $cleanBody);
                $cleanBody = trim($cleanBody);

                return [
                    'id'      => $row->id,
                    'title'   => $row->title,
                    'body'    => $cleanBody,
                    'is_read' => true,
                ];
            });

            return $this->sendResponse(
                $data,
                "Notifications marked as read successfully."
            );
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', $e->getMessage(), [], 500);
        }
    }

    /**
     * Get faculty complaint reports of student
     *
     * Returns list of faculty complaints for student.
     *
     * @response 200 {
     *   "status": "true",
     *   "message": "Faculty complaint reports fetched successfully.",
     *   "data": [
     *     {
     *       "id": 1,
     *       "admission_id": 1,
     *       "gr_no": "229",
     *       "date": "21-07-2026",
     *       "complaint": "Complaint text",
     *       "faculty_id": 4,
     *       "faculty_name": "Jignesh Parmar",
     *       "faculty_email": "jignesh@gmail.com",
     *       "created_at": "2026-07-21 15:30:00"
     *     }
     *   ]
     * }
     */
    public function faculty_complaint_reports(Request $request)
    {
        try {
            $student = Auth::guard('student-api')->user();
            $studentId = $request->input('student_id') ?? $request->input('admission_id') ?? ($student ? $student->id : null);

            if (!$studentId && !$student) {
                return $this->sendError("Unauthorization.", [], [], 401);
            }

            // Automatically mark all complaints for this student as read
            if ($studentId) {
                \App\Models\FacultyComplaintReport::where('admission_id', $studentId)
                    ->where('is_read', false)
                    ->update(['is_read' => true]);
            }

            $query = \App\Models\FacultyComplaintReport::with(['faculty:id,name,email']);

            if ($studentId) {
                $query->where('admission_id', $studentId);
            }

            if ($request->has('gr_no') && !empty($request->gr_no)) {
                $query->where('gr_no', $request->gr_no);
            }

            $reports = $query->orderBy('date', 'desc')
                ->orderBy('id', 'desc')
                ->get()
                ->map(function ($report) {
                    return [
                        'id'           => $report->id,
                        'admission_id' => $report->admission_id,
                        'gr_no'        => $report->gr_no,
                        'date'         => $report->date ? \App\Helpers\Helper::convert_date($report->date, 'Y-m-d', 'd-m-Y') : '',
                        'complaint'    => $report->complaint,
                        'faculty_id'   => $report->faculty_id,
                        'faculty_name' => $report->faculty?->name ?? '',
                        'is_read'      => (bool) ($report->is_read ?? false),
                    ];
                });

            return $this->sendResponse($reports, 'Faculty complaint reports fetched successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', $e->getMessage(), [], 500);
        }
    }

    /**
     * Get marksheet details of logged-in student
     *
     * @authenticated
     */
    public function marksheet_details(Request $request)
    {
        try {
            $student = Auth::guard('student-api')->user();

            if (!$student) {
                return $this->sendError("Unauthorization.", [], [], 401);
            }

            $marksheetDetails = [];
            $issues = $student->marksheetIssues()->orderBy('semester', 'asc')->get();
            foreach ($issues as $issue) {
                $marksheetDetails[] = [
                    'id'        => $issue->id,
                    'semester'  => (int) $issue->semester,
                    'gr_no'     => $issue->gr_no,
                    'series'    => $issue->series,
                    'note'      => $issue->note,
                    'date'      => $issue->date ? \Carbon\Carbon::parse($issue->date)->format('d-m-Y') : null,
                ];
            }

            return $this->sendResponse($marksheetDetails, 'Marksheet details fetched successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', $e->getMessage(), [], 500);
        }
    }


    /**
     * Get unread counts for faculty complaint reports and marksheet details
     *
     * @authenticated
     */
    public function unread_counts(Request $request)
    {
        try {
            $student = Auth::guard('student-api')->user();

            if (!$student) {
                return $this->sendError("Unauthorization.", [], [], 401);
            }

            $unreadComplaints = \App\Models\FacultyComplaintReport::where('admission_id', $student->id)
                ->where('is_read', false)
                ->count();

            $marksheetCount = \App\Models\StudentMarksheetIssue::where('admission_id', $student->id)
                ->count();

            $assessmentCount = \App\Models\Assessment::where('admission_id', $student->id)
                ->where('is_read', false)
                ->count();

            return $this->sendResponse([[
                'unread_complaints' => $unreadComplaints,
                'marksheet_count' => $marksheetCount,
                'unread_assessment' => $assessmentCount,
            ]], 'Unread counts fetched successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', $e->getMessage(), [], 500);
        }
    }

    /**
     * Get assessment details for the student
     *
     * @authenticated
     */
    public function assessment_details(Request $request)
    {
        try {
            $student = Auth::guard('student-api')->user();

            if (!$student) {
                return $this->sendError("Unauthorization.", [], [], 401);
            }

            // Automatically mark all assessments for this student as read
            \App\Models\Assessment::where('admission_id', $student->id)
                ->where('is_read', false)
                ->update(['is_read' => true]);

            $assessments = \App\Models\Assessment::where('admission_id', $student->id)
                ->orderBy('id', 'desc')
                ->get();

            $assessmentDetails = [];
            foreach ($assessments as $assessment) {
                $assessmentDetails[] = [
                    'id'          => $assessment->id,
                    'subject'     => $assessment->subject,
                    'performance' => ucfirst($assessment->performance),
                    'remarks'     => $assessment->remarks ?? '',
                    'date'        => $assessment->created_at ? $assessment->created_at->format('d-m-Y') : null,
                ];
            }

            return $this->sendResponse($assessmentDetails, 'Assessment details fetched successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', $e->getMessage(), [], 500);
        }
    }

    /**
     * Get achievement details for the student
     *
     * @authenticated
     */
    public function achievement_details(Request $request)
    {
        try {
            $student = Auth::guard('student-api')->user();

            if (!$student) {
                return $this->sendError("Unauthorization.", [], [], 401);
            }

            $achievements = \App\Models\StudentAchievement::with('event')
                ->where('admission_id', $student->id)
                ->orderBy('date', 'desc')
                ->orderBy('id', 'desc')
                ->get();

            $achievementDetails = [];
            foreach ($achievements as $ach) {
                $achievementDetails[] = [
                    'id'         => $ach->id,
                    'event_name' => $ach->event->name ?? '',
                    'rank'       => $ach->rank ?? '',
                    'remark'     => $ach->remark ?? '',
                    'date'       => $ach->date ? \Carbon\Carbon::parse($ach->date)->format('d-m-Y') : null,
                ];
            }

            return $this->sendResponse($achievementDetails, 'Achievement details fetched successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', $e->getMessage(), [], 500);
        }
    }

    /**
     * Get holiday list
     *
     * @authenticated
     */
    public function holiday_list(Request $request)
    {
        try {
            $year = $request->year ?? date('Y');
            $month = $request->month ?? null;

            $query = MasterHoliday::where('status', 'active')
                ->where(function ($q) use ($year) {
                    $q->whereYear('from_date', $year)
                      ->orWhereYear('to_date', $year);
                });

            if ($month) {
                $query->where(function ($q) use ($month) {
                    $q->whereMonth('from_date', $month)
                      ->orWhereMonth('to_date', $month);
                });
            }

            $holidays = $query->orderBy('from_date', 'asc')->get();

            $data = $holidays->map(function ($h) {
                $from = \Carbon\Carbon::parse($h->from_date);
                $to = \Carbon\Carbon::parse($h->to_date);
                $days = $from->diffInDays($to) + 1;

                return [
                    'id' => $h->id,
                    'name' => $h->name,
                    'from_date' => $from->format('d-m-Y'),
                    'to_date' => $to->format('d-m-Y'),
                    'day' => $from->format('l'),
                    'total_days' => $days,
                    'type' => $h->type ?? 'Public Holiday',
                    'description' => $h->description ?? '',
                ];
            });

            return $this->sendResponse($data, 'Holiday list fetched successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', $e->getMessage(), [], 500);
        }
    }

    /**
     * Download or view fee receipt PDF
     *
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function download_fee_receipt($id, Request $request)
    {
        try {
            $feesData = FeesCollection::with(['course', 'batch', 'admission', 'registration.batch', 'createdByUser'])->find($id);

            if (!$feesData) {
                return response()->json([
                    'status' => false,
                    'message' => 'Receipt not found.'
                ], 404);
            }

            $feesDetails = json_decode($feesData->fee_details, true);
            $totalFee = 0;
            if ($feesDetails && is_array($feesDetails)) {
                foreach ($feesDetails as $fee) {
                    $totalFee += (float)($fee['amount'] ?? 0);
                }
            } else {
                $totalFee = (float)$feesData->fees;
            }

            $amountInWords = $this->numberToWords($totalFee);
            $logo_src = $this->getPdfImageSrc('uploads/logo/logo.png');

            $html = view('software.module.fees-collection.receipt_pdf', compact(
                'feesData',
                'feesDetails',
                'totalFee',
                'amountInWords',
                'logo_src'
            ))->render();

            $mpdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4',
                'default_font' => 'dejavusans',
                'margin_left' => 12,
                'margin_right' => 12,
                'margin_top' => 12,
                'margin_bottom' => 12,
                'autoPageBreak' => true,
            ]);

            $logoLocal = public_path('uploads/logo/logo.png');
            if (file_exists($logoLocal)) {
                $mpdf->SetWatermarkImage($logoLocal, 0.08, [80, 80], [65, 45]);
                $mpdf->showWatermarkImage = true;
                $mpdf->watermarkImgBehind = true;
            }

            $mpdf->SetTitle('Fee Receipt - #' . $feesData->id);
            $mpdf->WriteHTML($html);

            $rawStudentName = $feesData->student_name ?? ($feesData->admission ? $feesData->admission->first_name . ' ' . $feesData->admission->last_name : 'Student');
            $safeStudentName = strtoupper(preg_replace('/[^A-Za-z0-9]+/', '_', trim($rawStudentName)));
            $safeStudentName = trim($safeStudentName, '_');
            $semesterStr = !empty($feesData->year_semester) ? 'Sem_' . $feesData->year_semester : 'Receipt';

            $fileName = 'Fee_Receipt_' . $feesData->id . '_' . $safeStudentName . '_' . $semesterStr . '.pdf';
            $pdfContent = $mpdf->Output($fileName, 'S');

            $disposition = ($request->get('action') === 'view' || $request->get('action') === 'stream') ? 'inline' : 'attachment';

            return response($pdfContent, 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', $disposition . '; filename="' . $fileName . '"');
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Could not generate receipt PDF: ' . $e->getMessage()
            ], 500);
        }
    }

    private function numberToWords($num)
    {
        $num = (int)$num;
        if ($num === 0) {
            return 'Zero Rupees Only';
        }

        $words = [
            '', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten',
            'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen',
            'Eighteen', 'Nineteen'
        ];
        $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

        $numToWordsHelper = function ($n, $label) use (&$numToWordsHelper, $words, $tens) {
            $str = '';
            if ($n > 19) {
                $str .= $tens[(int)($n / 10)] . ' ' . $words[$n % 10];
            } else {
                $str .= $words[$n];
            }
            return trim($str) ? trim($str) . ' ' . $label . ' ' : '';
        };

        $result = '';

        $crore = (int)($num / 10000000);
        $num %= 10000000;
        $lakh = (int)($num / 100000);
        $num %= 100000;
        $thousand = (int)($num / 1000);
        $num %= 1000;
        $hundred = (int)($num / 100);
        $remainder = $num % 100;

        if ($crore) $result .= $numToWordsHelper($crore, 'Crore');
        if ($lakh) $result .= $numToWordsHelper($lakh, 'Lakh');
        if ($thousand) $result .= $numToWordsHelper($thousand, 'Thousand');
        if ($hundred) $result .= $numToWordsHelper($hundred, 'Hundred');
        if ($remainder) {
            $result .= ($result !== '' ? 'and ' : '') . $numToWordsHelper($remainder, '');
        }

        return trim($result) . ' Rupees Only';
    }

    private function getPdfImageSrc($imagePath)
    {
        if (empty($imagePath)) {
            return '';
        }

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
                $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE) ?: 'image/png';
                curl_close($ch);

                if ($httpCode === 200 && !empty($imageData)) {
                    return 'data:' . $contentType . ';base64,' . base64_encode($imageData);
                }
            } catch (\Throwable $e) {
            }
            return $imagePath;
        }

        $cleanPath = ltrim($imagePath, '/\\');
        if (str_starts_with($cleanPath, 'public/')) {
            $cleanPath = substr($cleanPath, 7);
        }

        $fullLocalPath = public_path($cleanPath);
        if (file_exists($fullLocalPath) && is_file($fullLocalPath)) {
            return $fullLocalPath;
        }

        return '';
    }
}

