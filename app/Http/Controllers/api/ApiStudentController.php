<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\admin\StudentsFeesReportController;
use App\Http\Controllers\Controller;
use App\Models\Admission;
use App\Models\Attedance;
use App\Models\CourceRegistration;
use App\Models\FeesCollection;
use App\Models\Master\MasterCourse;
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

                $data = [
                    'university'     => $courseRegistered->university ?? '',
                    'department'     => $courseRegistered->department ?? '',
                    'course'         => $course->course_name ?? '',
                    'semester'       => $course->semester ?? '',
                    'gr_no'          => $student->id,
                    'enrollment_no'  => $student->enrolment_no ?? '',
                    'spid'           => $student->spid ?? '',
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
            $monthYear = null;

            // Filter by exact date if provided (format: dd-mm-yyyy)
            if ($request->filled('date')) {
                try {
                    $date = \Carbon\Carbon::createFromFormat('d-m-Y', $request->date);
                } catch (\Exception $e) {
                    return $this->sendError('Invalid date provided.', [], [], 422);
                }

                $query->whereDate('date', $date->format('Y-m-d'));
                $monthYear = $date->format('d-F-Y');
            }
            // Filter by month (and optionally year)
            elseif ($request->filled('month')) {
                $month = $request->month;
                $year  = $request->year ?? date('Y'); // default to current year
                $query->whereMonth('date', $month)
                    ->whereYear('date', $year);

                $monthYear = \Carbon\Carbon::createFromDate($year, $month, 1)->format('F-Y');
            }

            $attendanceRecords = $query->orderBy('date', 'desc')->get();

            if ($attendanceRecords->isEmpty()) {
                $msg = 'No attendance records found.';

                if ($request->filled('date')) {
                    try {
                        $formattedDate = \Carbon\Carbon::createFromFormat('d-m-Y', $request->date)
                            ->format('d F Y');
                        $msg = "No attendance records found for {$formattedDate}.";
                    } catch (\Exception $e) {
                        $msg = "Invalid date format. Please use dd-mm-yyyy.";
                    }
                } elseif ($request->filled('month')) {
                    $month = $request->month;
                    $year  = $request->year ?? date('Y');
                    $formattedMonth = \Carbon\Carbon::createFromDate($year, $month, 1)
                        ->format('F Y');
                    $msg = "No attendance records found for {$formattedMonth}.";
                }

                return $this->sendError($msg, [], [], 404);
            }


            $totalLectures  = $attendanceRecords->count();
            $presentCount   = $attendanceRecords->whereNotNull('in_time')->count();
            $absentCount    = $totalLectures - $presentCount;
            $averagePercent = $totalLectures > 0 ? round(($presentCount / $totalLectures) * 100, 2) : 0;

            $dataList = $attendanceRecords->map(function ($att) {
                $isPresent = !empty($att->in_time);
                return [
                    'date'        => $att->date ? \Carbon\Carbon::parse($att->date)->format('Y-m-d') : null,
                    'in_time'     => $att->in_time ? date('h:i A', strtotime($att->in_time)) : null,
                    'out_time'    => $att->out_time ? date('h:i A', strtotime($att->out_time)) : null,
                    'status'      => $isPresent ? 'Present' : 'Absent',
                    'status_code' => $isPresent ? 'P' : 'A',
                ];
            });

            $summaryData = [
                'total_lectures'   => $totalLectures,
                'present_lectures' => $presentCount,
                'absent_lectures'  => $absentCount,
                'average_percent'  => $averagePercent
            ];

            if ($monthYear) {
                $summaryData['month_year'] = $monthYear;
            }

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
}
