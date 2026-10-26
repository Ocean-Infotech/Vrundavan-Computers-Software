<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Admission;
use App\Models\Attedance;
use App\Models\CourceRegistration;
use App\Models\Master\MasterBatch;
use App\Models\Master\MasterClass;
use App\Models\Master\MasterCourse;
use App\Models\Slider;
use App\Models\StudentRequest;
use App\Models\Supplier;
use App\Models\Test;
use App\Models\Timetable;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * @group Admin
 *
 * APIs for Admin mobile application (Authentication, Dashboard, Staff, Suppliers and Slider Management).
 */
class ApiAdminController extends Controller
{
    /**
     * Admin login
     *
     * Authenticates an admin user using email and password, returning a Bearer access token.
     *
     * @bodyParam email string required The admin email address. Example: admin@vrundavan.com
     * @bodyParam password string required The admin password (min 6 chars). Example: password123
     *
     * @response 200 {
     *   "status": "true",
     *   "message": "Login successful.",
     *   "data": {
     *     "token": "eyJ0eXAiOiJKV1QiLCJhbGciOi...",
     *     "expires_at": "2026-11-19 12:00:00",
     *     "admin": {
     *       "id": 1,
     *       "name": "Super Admin",
     *       "email": "admin@vrundavan.com",
     *       "phone": "9876543210",
     *       "status": "active",
     *       "role": "Super Admin"
     *     }
     *   }
     * }
     *
     * @response 401 scenario="Invalid credentials" {
     *   "status": "false",
     *   "message": "Invalid email or password."
     * }
     *
     * @response 422 scenario="Validation failed" {
     *   "status": "false",
     *   "message": "The email field is required.",
     *   "messages": {
     *     "email": ["The email field is required."]
     *   }
     * }
     */
    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|min:6',
            ]);

            if ($validator->fails()) {
                return $this->sendError($validator->errors()->first(), $validator->errors(), [], 422);
            }

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return $this->sendError('Invalid email or password.', [], [], 401);
            }

            if (strtolower((string) $user->status) === 'inactive') {
                return $this->sendError('Your account is inactive. Please contact admin.', [], [], 422);
            }

            $blockedRoles = ['Mechanics', 'Other'];
            if ($user->roles()->whereIn('name', $blockedRoles)->exists()) {
                return $this->sendError('You are not authorized to login as admin.', [], [], 403);
            }

            $tokenResult = $user->createToken('admin-api');

            return $this->sendResponse([
                'token' => $tokenResult->accessToken,
                'expires_at' => $tokenResult->token->expires_at,
                'admin' => $this->adminPayload($user),
            ], 'Login successful.');
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', $e->getMessage(), [], 500);
        }
    }

    /**
     * Admin logout
     *
     * Revokes the current access token for authenticated admin.
     *
     * @authenticated
     *
     * @response 200 {
     *   "status": "true",
     *   "message": "Logged out successfully.",
     *   "data": []
     * }
     *
     * @response 401 scenario="Unauthenticated" {
     *   "status": "false",
     *   "message": "Unauthorization."
     * }
     */
    public function logout(Request $request)
    {
        try {
            $admin = Auth::guard('api')->user();

            if (!$admin) {
                return $this->sendError("Unauthorization.", [], [], 401);
            }

            $admin->token()->revoke();

            return $this->sendResponse([], 'Logged out successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', $e->getMessage(), [], 500);
        }
    }

    /**
     * Get admin profile
     *
     * Returns details of currently authenticated admin user.
     *
     * @authenticated
     *
     * @response 200 {
     *   "status": "true",
     *   "message": "Admin profile.",
     *   "data": {
     *     "id": 1,
     *     "name": "Super Admin",
     *     "email": "admin@vrundavan.com",
     *     "phone": "9876543210",
     *     "status": "active",
     *     "role": "Super Admin"
     *   }
     * }
     *
     * @response 401 scenario="Unauthenticated" {
     *   "status": "false",
     *   "message": "Unauthorization."
     * }
     */
    public function profile(Request $request)
    {
        try {
            $admin = Auth::guard('api')->user();

            if (!$admin) {
                return $this->sendError("Unauthorization.", [], [], 401);
            }

            return $this->sendResponse($this->adminPayload($admin), 'Admin profile.');
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', $e->getMessage(), [], 500);
        }
    }

    /**
     * Get admin dashboard details
     *
     * Returns summary metrics including student count, requests count, tests today, active timetables,
     * list of tests scheduled today, and students with birthdays today.
     *
     * @authenticated
     *
     * @response 200 {
     *   "status": "true",
     *   "message": "Admin dashboard.",
     *   "data": {
     *     "total_students": 150,
     *     "student_requests": 12,
     *     "today_tests": 2,
     *     "timetables": 8,
     *     "today_test_list": [
     *       {
     *         "id": 1,
     *         "course_name": "BCA",
     *         "date": "2026-10-19"
     *       }
     *     ],
     *     "student_birthdays": [
     *       {
     *         "id": 5,
     *         "name": "Rahul Patel",
     *         "gr_no": "GR0012"
     *       }
     *     ]
     *   }
     * }
     *
     * @response 401 scenario="Unauthenticated" {
     *   "status": "false",
     *   "message": "Unauthorization."
     * }
     */
    public function dashboard(Request $request)
    {
        try {
            $admin = Auth::guard('api')->user();

            if (!$admin) {
                return $this->sendError("Unauthorization.", [], [], 401);
            }

            $today = Carbon::today();
            $targetDate = $request->filled('date') ? Carbon::parse($request->date)->format('Y-m-d') : $today->format('Y-m-d');

            // Base Students Query with optional Course / Batch / Class filtering
            $baseStudentsQuery = Admission::query();

            if ($request->filled('course_id') || $request->filled('batch_id') || $request->filled('class_id')) {
                $baseStudentsQuery->whereHas('courses', function ($q) use ($request) {
                    if ($request->filled('course_id')) {
                        $q->where('course_id', $request->course_id);
                    }
                    if ($request->filled('batch_id')) {
                        $q->where('batch_id', $request->batch_id);
                    }
                    if ($request->filled('class_id')) {
                        $q->where('class_id', $request->class_id);
                    }
                });
            }

            // 4 Required Counts (Total Students, Male, Female, Today Present)
            $totalStudents = (clone $baseStudentsQuery)->count();
            $totalMale = (clone $baseStudentsQuery)->where('gender', 'Male')->count();
            $totalFemale = (clone $baseStudentsQuery)->where('gender', 'Female')->count();

            $studentBiometricIds = (clone $baseStudentsQuery)->whereNotNull('biometric_id')->where('biometric_id', '!=', '')->pluck('biometric_id')->toArray();
            $studentIds = (clone $baseStudentsQuery)->pluck('id')->toArray();

            $todayPresent = Attedance::whereDate('date', $targetDate)
                ->where(function ($q) use ($studentBiometricIds, $studentIds) {
                    $q->whereIn('biometric_id', $studentBiometricIds)
                      ->orWhereIn('admission_id', $studentIds);
                })
                ->distinct('biometric_id')
                ->count('biometric_id');

            $todayTestsQuery = Test::whereDate('date', $today)->with('course:id,course_name');
            if ($request->filled('course_id')) {
                $todayTestsQuery->where('course_id', $request->course_id);
            }
            $todayTests = $todayTestsQuery->get()
                ->map(function ($test) {
                    return [
                        'id' => $test->id,
                        'course_name' => $test->course->course_name ?? '',
                        'date' => $test->date,
                    ];
                })
                ->values();

            return $this->sendResponse([
                // 4 Requested Stat Cards (Filtered by Course/Batch/Class if provided, else All)
                'total_students' => $totalStudents,
                'male' => $totalMale,
                'female' => $totalFemale,
                'today_present' => $todayPresent,

                // Active Filters Applied
                'filters' => [
                    'course_id' => $request->course_id ? (int)$request->course_id : null,
                    'batch_id' => $request->batch_id ? (int)$request->batch_id : null,
                    'class_id' => $request->class_id ? (int)$request->class_id : null,
                    'date' => $targetDate,
                ],

                // Additional dashboard metrics
                'student_requests' => StudentRequest::count(),
                'today_tests' => $todayTests->count(),
                'timetables' => Timetable::whereNotNull('attachment')->where('attachment', '!=', '')->count(),
                'today_test_list' => $todayTests,
            ], 'Admin dashboard.');
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', $e->getMessage(), [], 500);
        }
    }

    /**
     * Get student birthdays (Today and Upcoming)
     *
     * Returns students celebrating birthday today and upcoming birthdays within the next N days.
     *
     * @authenticated
     *
     * @bodyParam type string optional Filter by type: 'all', 'today', or 'upcoming' (default: all). Example: all
     * @bodyParam days integer optional Number of upcoming days to check (default: 30). Example: 30
     *
     * @response 200 {
     *   "status": "true",
     *   "message": "Student birthdays retrieved successfully.",
     *   "data": {
     *     "today_count": 2,
     *     "upcoming_count": 8,
     *     "today": [
     *       {
     *         "id": 50,
     *         "name": "RAM DHRUV RAJESHBHAI",
     *         "profile_pic": "http://127.0.0.1:8000/uploads/...",
     *         "date_of_birth": "19-10-2007",
     *         "birthday_date": "19 October",
     *         "days_remaining": 0,
     *         "status_label": "Today",
     *         "mobile_no": "9586225901",
     *         "course_batch": "BCA - 2025"
     *       }
     *     ],
     *     "upcoming": [
     *       {
     *         "id": 9,
     *         "name": "THUMBAR DHRUVIN ASHOKBHAI",
     *         "profile_pic": "http://127.0.0.1:8000/uploads/...",
     *         "date_of_birth": "20-10-2007",
     *         "birthday_date": "20 October",
     *         "days_remaining": 1,
     *         "status_label": "Tomorrow",
     *         "mobile_no": "9876543210",
     *         "course_batch": "BCA - 2025"
     *       }
     *     ]
     *   }
     * }
     */
    public function studentBirthdays(Request $request)
    {
        try {
            $admin = Auth::guard('api')->user();

            if (!$admin) {
                return $this->sendError("Unauthorization.", [], [], 401);
            }

            $today = Carbon::today();
            $maxDays = $request->filled('days') ? (int) $request->days : 30;
            $type = $request->get('type', 'all'); // 'all', 'today', 'upcoming'

            $query = Admission::whereNotNull('date_of_birth');

            $students = $query->with(['courses.course', 'courses.batch', 'courses.class'])->get();

            $todayList = [];
            $upcomingList = [];

            foreach ($students as $student) {
                try {
                    $dob = Carbon::parse($student->date_of_birth);
                    $targetYear = $today->year;
                    $day = $dob->day;

                    if (!checkdate($dob->month, $day, $targetYear)) {
                        $day = 28;
                    }

                    $nextBirthday = Carbon::create($targetYear, $dob->month, $day)->startOfDay();

                    if ($nextBirthday->lt($today)) {
                        $nextYear = $targetYear + 1;
                        $nextDay = $dob->day;
                        if (!checkdate($dob->month, $nextDay, $nextYear)) {
                            $nextDay = 28;
                        }
                        $nextBirthday = Carbon::create($nextYear, $dob->month, $nextDay)->startOfDay();
                    }

                    $daysRemaining = (int) $today->diffInDays($nextBirthday, false);
                    $activeCourseReg = $student->courses->first();

                    $courseName = $activeCourseReg?->course?->course_name ?? null;
                    $batchName = $activeCourseReg?->batch?->batch_name ?? null;
                    $courseBatch = trim(($courseName ? $courseName : '') . ($courseName && $batchName ? ' - ' : '') . ($batchName ? $batchName : ''));

                    $studentData = [
                        'id' => $student->id,
                        'name' => trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? '') . ' ' . ($student->father_name ?? '')),
                        'profile_pic' => $student->profile_pic ? asset($student->profile_pic) : null,
                        'date_of_birth' => $dob->format('d-m-Y'),
                        'birthday_date' => $dob->format('d F'),
                        'days_remaining' => $daysRemaining,
                        'status_label' => $daysRemaining === 0 ? 'Today' : ($daysRemaining === 1 ? 'Tomorrow' : "In {$daysRemaining} days"),
                        'mobile_no' => $student->mobile_no,
                        'course_batch' => $courseBatch ?: null,
                    ];

                    if ($daysRemaining === 0) {
                        $todayList[] = $studentData;
                    } elseif ($daysRemaining > 0 && $daysRemaining <= $maxDays) {
                        $upcomingList[] = $studentData;
                    }
                } catch (\Exception $ex) {
                    continue;
                }
            }

            // Sort upcoming birthdays chronologically by nearest days remaining
            usort($upcomingList, fn($a, $b) => $a['days_remaining'] <=> $b['days_remaining']);

            if ($type === 'today') {
                return $this->sendResponse([
                    'count' => count($todayList),
                    'birthdays' => $todayList,
                ], 'Today student birthdays retrieved successfully.');
            }

            if ($type === 'upcoming') {
                return $this->sendResponse([
                    'count' => count($upcomingList),
                    'birthdays' => $upcomingList,
                ], 'Upcoming student birthdays retrieved successfully.');
            }

            return $this->sendResponse([
                'today_count' => count($todayList),
                'upcoming_count' => count($upcomingList),
                'today' => $todayList,
                'upcoming' => $upcomingList,
            ], 'Student birthdays (today and upcoming) retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', $e->getMessage(), [], 500);
        }
    }

    /**
     * Get student list with search, academic filters (Course, Batch, Class) and summary counts
     *
     * Returns students matching search and filters along with header counts.
     *
     * @authenticated
     *
     * @bodyParam search string optional Search keyword for Name, Roll No or Mobile. Example: Rahul
     * @bodyParam course_id integer optional Filter by Course ID. Example: 1
     * @bodyParam batch_id integer optional Filter by Batch ID. Example: 1
     * @bodyParam class_id integer optional Filter by Class ID. Example: 1
     * @bodyParam gender string optional Filter by gender (Male or Female). Example: Male
     * @bodyParam per_page integer optional Number of records per page (default: 50). Example: 50
     */
    public function studentList(Request $request)
    {
        try {
            $admin = Auth::guard('api')->user();

            if (!$admin) {
                return $this->sendError("Unauthorization.", [], [], 401);
            }

            $query = Admission::query();

            // Course, Batch, Class Filters (Optional)
            if ($request->filled('course_id') || $request->filled('batch_id') || $request->filled('class_id')) {
                $query->whereHas('courses', function ($q) use ($request) {
                    if ($request->filled('course_id')) {
                        $q->where('course_id', $request->course_id);
                    }
                    if ($request->filled('batch_id')) {
                        $q->where('batch_id', $request->batch_id);
                    }
                    if ($request->filled('class_id')) {
                        $q->where('class_id', $request->class_id);
                    }
                });
            }

            // Calculate the 4 summary counts matching the Course/Batch/Class filter
            $totalStudents = (clone $query)->count();
            $totalMale = (clone $query)->where('gender', 'Male')->count();
            $totalFemale = (clone $query)->where('gender', 'Female')->count();

            $today = Carbon::today()->format('Y-m-d');
            $studentBiometricIds = (clone $query)->whereNotNull('biometric_id')->where('biometric_id', '!=', '')->pluck('biometric_id')->toArray();
            $studentIds = (clone $query)->pluck('id')->toArray();

            $todayPresent = Attedance::whereDate('date', $today)
                ->where(function ($q) use ($studentBiometricIds, $studentIds) {
                    if (!empty($studentBiometricIds)) {
                        $q->whereIn('biometric_id', $studentBiometricIds);
                    }
                    if (!empty($studentIds)) {
                        $q->orWhereIn('admission_id', $studentIds);
                    }
                })
                ->distinct('biometric_id')
                ->count('biometric_id');

            // Apply Gender filter to list (e.g. user clicks Male or Female stat card)
            if ($request->filled('gender')) {
                $query->where('gender', $request->gender);
            }

            // Search by Name / Roll No / GR No / Mobile No
            $search = trim((string) ($request->search ?? $request->name ?? $request->roll_no ?? ''));
            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'LIKE', "%{$search}%")
                      ->orWhere('last_name', 'LIKE', "%{$search}%")
                      ->orWhere('father_name', 'LIKE', "%{$search}%")
                      ->orWhere('gr_no', 'LIKE', "%{$search}%")
                      ->orWhere('mobile_no', 'LIKE', "%{$search}%")
                      ->orWhere('biometric_id', 'LIKE', "%{$search}%")
                      ->orWhereRaw("CONCAT(COALESCE(first_name,''), ' ', COALESCE(last_name,'')) LIKE ?", ["%{$search}%"])
                      ->orWhereRaw("CONCAT(COALESCE(last_name,''), ' ', COALESCE(first_name,'')) LIKE ?", ["%{$search}%"])
                      ->orWhereRaw("CONCAT(COALESCE(first_name,''), ' ', COALESCE(father_name,''), ' ', COALESCE(last_name,'')) LIKE ?", ["%{$search}%"]);
                });
            }

            $perPage = (int) $request->get('per_page', 50);
            $students = $query->with(['courses.course', 'courses.batch', 'courses.class'])
                ->orderBy('id', 'asc')
                ->paginate($perPage);

            $formattedStudents = $students->getCollection()->map(function ($student, $index) use ($students) {
                $activeCourseReg = $student->courses->first();

                $courseName = $activeCourseReg?->course?->course_name ?? '';
                $batchName = $activeCourseReg?->batch?->batch_name ?? '';

                // Course-Batch (e.g. BCA-2025)
                $courseBatch = '';
                if (!empty($courseName) && !empty($batchName)) {
                    $courseBatch = "{$courseName}-{$batchName}";
                } elseif (!empty($courseName)) {
                    $courseBatch = $courseName;
                } elseif (!empty($batchName)) {
                    $courseBatch = $batchName;
                }

                $firstName = trim((string) ($student->first_name ?? ''));
                $lastName = trim((string) ($student->last_name ?? ''));
                $fatherName = trim((string) ($student->father_name ?? ''));

                $displayName = trim("{$firstName} {$lastName}");
                if (empty($displayName)) {
                    $displayName = trim("{$firstName} {$fatherName}");
                }
                if (empty($displayName)) {
                    $displayName = (string) ($student->gr_no ?? 'Student #' . $student->id);
                }

                $grNo = (string) ($student->gr_no ?? $student->id);

                return [
                    'sr_no' => ($students->currentPage() - 1) * $students->perPage() + ($index + 1),
                    'id' => $student->id,
                    'name' => $displayName,
                    'gr_no' => $grNo,
                    'course_batch' => $courseBatch,
                ];
            });

            return response()->json([
                'status' => 'true',
                'message' => 'Student list retrieved successfully.',
                'counts' => [
                    'total_students' => (int) $totalStudents,
                    'male' => (int) $totalMale,
                    'female' => (int) $totalFemale,
                    'today_present' => (int) $todayPresent,
                ],
                // Direct root fields for mobile ease
                'total_students' => (int) $totalStudents,
                'male' => (int) $totalMale,
                'female' => (int) $totalFemale,
                'today_present' => (int) $todayPresent,
                'filters' => [
                    'course_id' => $request->course_id ? (int)$request->course_id : null,
                    'batch_id' => $request->batch_id ? (int)$request->batch_id : null,
                    'class_id' => $request->class_id ? (int)$request->class_id : null,
                    'gender' => $request->gender ?? null,
                    'search' => $search ?: null,
                ],
                'data' => $formattedStudents,
                'pagination' => [
                    'current_page' => $students->currentPage(),
                    'total_pages' => $students->lastPage(),
                    'total_records' => $students->total(),
                    'per_page' => $students->perPage(),
                ]
            ], 200);
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', $e->getMessage(), [], 500);
        }
    }

    /**
     * Get single student details
     *
     * Returns full profile, academic registration, fee summary, and attendance for a student.
     *
     * @authenticated
     */
    public function studentDetail(Request $request, $id = null)
    {
        try {
            $admin = Auth::guard('api')->user();

            if (!$admin) {
                return $this->sendError("Unauthorization.", [], [], 401);
            }

            $id = $id ?? $request->id ?? $request->student_id;

            if (!$id) {
                return $this->sendError('Student ID is required.', [], [], 422);
            }

            $student = Admission::with([
                'courses.course',
                'courses.batch',
                'courses.class',
                'courses.shift',
                'feesCollections'
            ])->find($id);

            if (!$student) {
                return $this->sendError('Student not found.', [], [], 404);
            }

            $activeCourseReg = $student->courses->first();
            $courseName = $activeCourseReg?->course?->course_name ?? '';
            $className = $activeCourseReg?->class?->class ?? '';

            $classDisplay = $className;
            if (!empty($courseName) && !empty($className)) {
                $classDisplay = (stripos($className, $courseName) !== false)
                    ? $className
                    : trim("{$courseName} {$className}");
            } elseif (!empty($courseName)) {
                $classDisplay = $courseName;
            }

            $firstName = trim((string) ($student->first_name ?? ''));
            $lastName = trim((string) ($student->last_name ?? ''));
            $fatherName = trim((string) ($student->father_name ?? ''));

            $shortName = trim("{$firstName} {$lastName}");
            if (empty($shortName)) {
                $shortName = trim("{$firstName} {$fatherName}");
            }
            if (empty($shortName)) {
                $shortName = $student->gr_no ?? 'Student #' . $student->id;
            }

            $totalFee = (float) ($activeCourseReg?->fee ?? 0);
            $paidFee = (float) $student->feesCollections->sum('paid_amount');
            $remainingFee = max(0, $totalFee - $paidFee);

            $presentCount = 0;
            if ($student->biometric_id) {
                $presentCount = Attedance::where('biometric_id', $student->biometric_id)->distinct('date')->count('date');
            } elseif ($student->id) {
                $presentCount = Attedance::where('admission_id', $student->id)->distinct('date')->count('date');
            }

            $data = [
                'id' => $student->id,
                'name' => $shortName,
                'full_name' => trim("{$firstName} {$fatherName} {$lastName}"),
                'first_name' => $firstName,
                'last_name' => $lastName,
                'father_name' => $fatherName,
                'mother_name' => $student->mother_name ?? '',
                'roll_no' => (string) ($student->gr_no ?? $student->id),
                'gr_no' => (string) ($student->gr_no ?? ''),
                'biometric_id' => (string) ($student->biometric_id ?? ''),
                'gender' => $student->gender ?? '',
                'date_of_birth' => $student->date_of_birth ?? '',
                'mobile_no' => $student->mobile_no ?? '',
                'parent_mobile_no' => $student->parent_mobile_no ?? '',
                'whatsapp_no' => $student->whatsapp_no ?? '',
                'email_address' => $student->email_address ?? '',
                'temporary_address' => $student->temporary_address ?? '',
                'permanent_address' => $student->permanent_address ?? '',
                'cast' => $student->cast ?? '',
                'category' => $student->category ?? '',
                'enrolment_no' => $student->enrolment_no ?? '',
                'spid' => $student->spid ?? '',
                'status' => $student->status ?? 'active',
                'profile_pic' => $student->profile_pic ? asset($student->profile_pic) : null,
                'academic' => [
                    'course' => $courseName,
                    'batch' => $activeCourseReg?->batch?->batch_name ?? '',
                    'class' => $classDisplay,
                    'class_name' => $className,
                    'shift' => $activeCourseReg?->shift?->name ?? '',
                    'university' => $activeCourseReg?->university ?? '',
                    'department' => $activeCourseReg?->department ?? '',
                    'admission_date' => $activeCourseReg?->date ?? '',
                ],
                'fees_summary' => [
                    'total_fee' => $totalFee,
                    'paid_fee' => $paidFee,
                    'remaining_fee' => $remainingFee,
                ],
                'attendance_summary' => [
                    'total_present_days' => $presentCount,
                ],
            ];

            return $this->sendResponse($data, 'Student details retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', $e->getMessage(), [], 500);
        }
    }

    /**
     * Today Attendance / Total Attendance screen API
     *
     * Returns overall presence/absence donut stats and class-wise attendance cards.
     *
     * @authenticated
     */
    public function todayAttendance(Request $request)
    {
        try {
            $admin = Auth::guard('api')->user();

            if (!$admin) {
                return $this->sendError("Unauthorization.", [], [], 401);
            }

            $dateInput = $request->input('date');
            if ($dateInput) {
                try {
                    $carbonDate = Carbon::parse($dateInput);
                } catch (\Exception $e) {
                    $carbonDate = Carbon::today();
                }
            } else {
                $carbonDate = Carbon::today();
            }

            $selectedDate = $carbonDate->format('Y-m-d');
            $dateFormatted = $carbonDate->format('d M Y');
            $isSunday = ($carbonDate->dayOfWeek === Carbon::SUNDAY);

            $latestRegIds = DB::table('cource_registration')
                ->select('register_id', DB::raw('MAX(id) as max_id'))
                ->whereNull('deleted_at')
                ->groupBy('register_id');

            $studentsQuery = CourceRegistration::joinSub($latestRegIds, 'latest_reg', function ($join) {
                    $join->on('cource_registration.id', '=', 'latest_reg.max_id');
                })
                ->join('admission', 'admission.id', '=', 'cource_registration.register_id')
                ->whereNull('admission.deleted_at')
                ->whereNull('cource_registration.deleted_at')
                ->whereIn('cource_registration.status', ['active', 'Running']);

            // Filters: Course, Batch, Class
            if ($request->filled('course_id')) {
                $studentsQuery->where('cource_registration.course_id', $request->course_id);
            }
            if ($request->filled('batch_id')) {
                $studentsQuery->where('cource_registration.batch_id', $request->batch_id);
            }
            if ($request->filled('class_id')) {
                $studentsQuery->where('cource_registration.class_id', $request->class_id);
            }

            $totalStudents = (clone $studentsQuery)->count();

            // Biometric IDs and Admission IDs present on this date
            $presentBioIds = Attedance::whereDate('date', $selectedDate)
                ->whereNotNull('biometric_id')
                ->where('biometric_id', '!=', '')
                ->distinct('biometric_id')
                ->pluck('biometric_id')
                ->toArray();

            $presentAdmissionIds = Attedance::whereDate('date', $selectedDate)
                ->whereNotNull('admission_id')
                ->distinct('admission_id')
                ->pluck('admission_id')
                ->toArray();

            $students = $studentsQuery->with(['admission', 'course', 'class', 'batch'])->get();

            $totalPresent = 0;
            $totalAbsent = 0;
            $classStats = [];
            $courseBatchStats = [];

            foreach ($students as $reg) {
                $student = $reg->admission;
                if (!$student) continue;

                $bioId = $student->biometric_id;
                $admId = $student->id;

                $isPresent = (in_array($bioId, $presentBioIds) || in_array($admId, $presentAdmissionIds));
                if ($isPresent) {
                    $totalPresent++;
                } else {
                    $totalAbsent++;
                }

                $classId = $reg->class_id ?? 0;
                $className = $reg->class?->class ?? 'Class';
                $courseId = $reg->course_id ?? 0;
                $courseName = $reg->course?->course_name ?? 'Course';
                $batchId = $reg->batch_id ?? 0;
                $batchName = $reg->batch?->batch_name ?? '';

                // 1. Course-Batch grouping (e.g. BCA-2025)
                $courseBatchName = !empty($batchName) ? "{$courseName}-{$batchName}" : $courseName;
                $cbKey = "{$courseId}_{$batchId}";

                if (!isset($courseBatchStats[$cbKey])) {
                    $courseBatchStats[$cbKey] = [
                        'course_id' => $courseId,
                        'batch_id' => $batchId,
                        'course_name' => $courseName,
                        'batch_name' => $batchName,
                        'course_batch' => $courseBatchName,
                        'title' => $courseBatchName,
                        'present' => 0,
                        'total' => 0,
                        'absent' => 0,
                    ];
                }

                $courseBatchStats[$cbKey]['total']++;
                if ($isPresent) {
                    $courseBatchStats[$cbKey]['present']++;
                } else {
                    $courseBatchStats[$cbKey]['absent']++;
                }

                // 2. Class grouping
                $title = $className;
                if (!empty($courseName) && stripos($className, $courseName) === false) {
                    $title = "{$courseName} {$className}";
                }

                if (!isset($classStats[$classId])) {
                    $classStats[$classId] = [
                        'class_id' => $classId,
                        'title' => $title,
                        'class_name' => $className,
                        'course_name' => $courseName,
                        'present' => 0,
                        'total' => 0,
                        'absent' => 0,
                    ];
                }

                $classStats[$classId]['total']++;
                if ($isPresent) {
                    $classStats[$classId]['present']++;
                } else {
                    $classStats[$classId]['absent']++;
                }
            }

            // Calculate percentage and ratio for Course-Batch
            $courseBatchList = [];
            foreach ($courseBatchStats as $cb) {
                $pct = $cb['total'] > 0 ? (int) round(($cb['present'] / $cb['total']) * 100) : 0;
                $cb['ratio'] = "{$cb['present']}/{$cb['total']}";
                $cb['percentage'] = $pct;
                $cb['percentage_text'] = "{$pct}%";
                $courseBatchList[] = $cb;
            }
            usort($courseBatchList, fn($a, $b) => $b['total'] <=> $a['total']);

            // Calculate percentage and ratio for Classes
            $classWiseList = [];
            foreach ($classStats as $cs) {
                $pct = $cs['total'] > 0 ? (int) round(($cs['present'] / $cs['total']) * 100) : 0;
                $cs['ratio'] = "{$cs['present']}/{$cs['total']}";
                $cs['percentage'] = $pct;
                $cs['percentage_text'] = "{$pct}%";
                $classWiseList[] = $cs;
            }
            usort($classWiseList, fn($a, $b) => $b['total'] <=> $a['total']);

            $overallPct = $totalStudents > 0 ? (int) round(($totalPresent / $totalStudents) * 100) : 0;

            $responseData = [
                'date' => $selectedDate,
                'date_formatted' => $dateFormatted,
                'day' => $carbonDate->format('l'),
                'is_sunday' => $isSunday,
                'summary' => [
                    'percentage' => $overallPct,
                    'percentage_text' => "{$overallPct}%",
                    'present' => $totalPresent,
                    'absent' => $totalAbsent,
                    'total' => $totalStudents,
                ],
                'present' => $totalPresent,
                'absent' => $totalAbsent,
                'total' => $totalStudents,
                'percentage' => $overallPct,
                'percentage_text' => "{$overallPct}%",
                'filters' => [
                    'course_id' => $request->course_id ? (int) $request->course_id : null,
                    'batch_id' => $request->batch_id ? (int) $request->batch_id : null,
                    'class_id' => $request->class_id ? (int) $request->class_id : null,
                ],
                'filter_options' => [
                    'courses' => MasterCourse::where('status', 'active')->orderBy('course_name', 'asc')->get(['id', 'course_name as name']),
                    'batches' => MasterBatch::where('status', 'active')->orderBy('batch_name', 'asc')->get(['id', 'batch_name as name']),
                    'classes' => MasterClass::where('status', 'active')->orderBy('class', 'asc')->get(['id', 'class as name']),
                ],
                'course_batch_wise' => $courseBatchList,
                'data' => $courseBatchList,
                'class_wise' => $classWiseList,
            ];

            return $this->sendResponse($responseData, 'Today attendance retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', $e->getMessage(), [], 500);
        }
    }

    /**
     * Get students attendance list for a specific class on a date
     *
     * @authenticated
     */
    public function classAttendanceDetail(Request $request, $class_id = null)
    {
        try {
            $admin = Auth::guard('api')->user();

            if (!$admin) {
                return $this->sendError("Unauthorization.", [], [], 401);
            }

            $classId = $class_id ?? $request->class_id;
            $courseId = $request->course_id;
            $batchId = $request->batch_id;

            if (!$classId && !$courseId && !$batchId) {
                return $this->sendError('Class ID or Course ID/Batch ID is required.', [], [], 422);
            }

            $dateInput = $request->input('date');
            $carbonDate = $dateInput ? Carbon::parse($dateInput) : Carbon::today();
            $selectedDate = $carbonDate->format('Y-m-d');
            $dateFormatted = $carbonDate->format('d M Y');

            $class = $classId ? MasterClass::find($classId) : null;
            $course = $courseId ? MasterCourse::find($courseId) : null;
            $batch = $batchId ? MasterBatch::find($batchId) : null;

            $title = '';
            if ($class) {
                $title = $class->class;
            } elseif ($course && $batch) {
                $title = "{$course->course_name}-{$batch->batch_name}";
            } elseif ($course) {
                $title = $course->course_name;
            } else {
                $title = 'Attendance Detail';
            }

            $latestRegQuery = DB::table('cource_registration')
                ->select('register_id', DB::raw('MAX(id) as max_id'))
                ->whereNull('deleted_at');

            if ($classId) {
                $latestRegQuery->where('class_id', $classId);
            }
            if ($courseId) {
                $latestRegQuery->where('course_id', $courseId);
            }
            if ($batchId) {
                $latestRegQuery->where('batch_id', $batchId);
            }

            $latestRegIds = $latestRegQuery->groupBy('register_id');

            $registrations = CourceRegistration::joinSub($latestRegIds, 'latest_reg', function ($join) {
                    $join->on('cource_registration.id', '=', 'latest_reg.max_id');
                })
                ->join('admission', 'admission.id', '=', 'cource_registration.register_id')
                ->whereNull('admission.deleted_at')
                ->whereNull('cource_registration.deleted_at')
                ->whereIn('cource_registration.status', ['active', 'Running'])
                ->with(['admission', 'course', 'batch'])
                ->get();

            $bioIds = $registrations->pluck('admission.biometric_id')->filter()->toArray();
            $admIds = $registrations->pluck('admission.id')->filter()->toArray();

            $attendanceLogs = Attedance::select([
                    'biometric_id',
                    'admission_id',
                    DB::raw('MIN(in_time) as first_punch'),
                    DB::raw('MAX(in_time) as last_punch'),
                ])
                ->whereDate('date', $selectedDate)
                ->where(function ($q) use ($bioIds, $admIds) {
                    if (!empty($bioIds)) {
                        $q->whereIn('biometric_id', $bioIds);
                    }
                    if (!empty($admIds)) {
                        $q->orWhereIn('admission_id', $admIds);
                    }
                })
                ->groupBy('biometric_id', 'admission_id')
                ->get();

            $logsByBio = $attendanceLogs->whereNotNull('biometric_id')->keyBy('biometric_id');
            $logsByAdm = $attendanceLogs->whereNotNull('admission_id')->keyBy('admission_id');

            $studentList = [];
            $presentCount = 0;
            $absentCount = 0;

            foreach ($registrations as $index => $reg) {
                $student = $reg->admission;
                if (!$student) continue;

                $bioId = (string) $student->biometric_id;
                $admId = (int) $student->id;

                $log = ($bioId && isset($logsByBio[$bioId]))
                    ? $logsByBio[$bioId]
                    : ($admId && isset($logsByAdm[$admId]) ? $logsByAdm[$admId] : null);

                $isPresent = !empty($log);
                if ($isPresent) {
                    $presentCount++;
                } else {
                    $absentCount++;
                }

                $inTime = ($log && !empty($log->first_punch)) ? Carbon::parse($log->first_punch)->format('h:i A') : null;
                $outTime = ($log && !empty($log->last_punch) && $log->last_punch !== $log->first_punch) ? Carbon::parse($log->last_punch)->format('h:i A') : null;

                $firstName = trim((string) ($student->first_name ?? ''));
                $lastName = trim((string) ($student->last_name ?? ''));
                $fatherName = trim((string) ($student->father_name ?? ''));

                $displayName = trim("{$firstName} {$lastName}");
                if (empty($displayName)) {
                    $displayName = trim("{$firstName} {$fatherName}");
                }
                if (empty($displayName)) {
                    $displayName = $student->gr_no ?? 'Student #' . $student->id;
                }

                $studentList[] = [
                    'sr_no' => $index + 1,
                    'id' => $student->id,
                    'name' => $displayName,
                    'gr_no' => (string) ($student->gr_no ?? $student->id),
                    'status' => $isPresent ? 'Present' : 'Absent',
                    'is_present' => $isPresent,
                    'in_time' => $inTime,
                    'out_time' => $outTime,
                ];
            }

            $total = count($studentList);
            $pct = $total > 0 ? (int) round(($presentCount / $total) * 100) : 0;

            return $this->sendResponse([
                'title' => $title,
                'class_id' => $classId ? (int) $classId : null,
                'class_name' => $class?->class ?? null,
                'course_id' => $courseId ? (int) $courseId : null,
                'course_name' => $course?->course_name ?? null,
                'batch_id' => $batchId ? (int) $batchId : null,
                'batch_name' => $batch?->batch_name ?? null,
                'date' => $selectedDate,
                'date_formatted' => $dateFormatted,
                'total' => $total,
                'present' => $presentCount,
                'absent' => $absentCount,
                'percentage' => $pct,
                'percentage_text' => "{$pct}%",
                'ratio' => "{$presentCount}/{$total}",
                'students' => $studentList,
            ], 'Attendance details retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', $e->getMessage(), [], 500);
        }
    }

    /**
     * Mark Attendance manually for a student
     *
     * @authenticated
     */
    public function markAttendance(Request $request)
    {
        try {
            $admin = Auth::guard('api')->user();

            if (!$admin) {
                return $this->sendError("Unauthorization.", [], [], 401);
            }

            $validator = Validator::make($request->all(), [
                'student_id' => 'required',
                'status' => 'required|in:present,absent,Present,Absent',
            ]);

            if ($validator->fails()) {
                return $this->sendError($validator->errors()->first(), $validator->errors(), [], 422);
            }

            $student = Admission::find($request->student_id);
            if (!$student) {
                return $this->sendError('Student not found.', [], [], 404);
            }

            $date = $request->input('date', Carbon::today()->format('Y-m-d'));
            $status = strtolower($request->status);

            if ($status === 'present') {
                $nowTime = Carbon::now()->format('H:i:s');
                $inTime = $request->input('in_time', $nowTime);
                $outTime = $request->input('out_time', null);

                $attendance = Attedance::firstOrNew([
                    'admission_id' => $student->id,
                    'date' => $date,
                ]);

                $attendance->biometric_id = $student->biometric_id ?? $attendance->biometric_id;
                $attendance->in_time = $attendance->in_time ?: $inTime;
                if ($outTime) {
                    $attendance->out_time = $outTime;
                }
                $attendance->DeviceName = 'Manual App Punch';
                $attendance->save();

                return $this->sendResponse([
                    'student_id' => $student->id,
                    'student_name' => trim("{$student->first_name} {$student->last_name}"),
                    'date' => $date,
                    'status' => 'Present',
                    'in_time' => $attendance->in_time,
                ], 'Attendance marked as Present successfully.');
            } else {
                Attedance::where('date', $date)
                    ->where(function ($q) use ($student) {
                        $q->where('admission_id', $student->id);
                        if ($student->biometric_id) {
                            $q->orWhere('biometric_id', $student->biometric_id);
                        }
                    })
                    ->delete();

                return $this->sendResponse([
                    'student_id' => $student->id,
                    'student_name' => trim("{$student->first_name} {$student->last_name}"),
                    'date' => $date,
                    'status' => 'Absent',
                ], 'Attendance marked as Absent successfully.');
            }
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', $e->getMessage(), [], 500);
        }
    }

    /**
     * Get academic filter dropdown options (Courses, Batches, Classes)
     *
     * Returns list of courses, batches, and classes for mobile app filter dropdowns.
     *
     * @authenticated
     *
     * @response 200 {
     *   "status": "true",
     *   "message": "Filter options retrieved successfully.",
     *   "data": {
     *     "courses": [{"id": 1, "name": "BCA"}],
     *     "batches": [{"id": 1, "name": "2025"}],
     *     "classes": [{"id": 1, "name": "CLASS A(Girls)"}]
     *   }
     * }
     */
    public function filterOptions(Request $request)
    {
        try {
            $admin = Auth::guard('api')->user();

            if (!$admin) {
                return $this->sendError("Unauthorization.", [], [], 401);
            }

            $courses = MasterCourse::whereNull('deleted_at')
                ->orderBy('course_name')
                ->get(['id', 'course_name'])
                ->map(fn($c) => ['id' => $c->id, 'name' => $c->course_name])
                ->values();

            $batches = MasterBatch::whereNull('deleted_at')
                ->orderBy('id', 'desc')
                ->get(['id', 'batch_name'])
                ->map(fn($b) => ['id' => $b->id, 'name' => $b->batch_name])
                ->values();

            $classes = MasterClass::whereNull('deleted_at')
                ->orderBy('id')
                ->get(['id', 'class'])
                ->map(fn($cl) => ['id' => $cl->id, 'name' => $cl->class])
                ->values();

            return $this->sendResponse([
                'courses' => $courses,
                'batches' => $batches,
                'classes' => $classes,
            ], 'Filter options retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', $e->getMessage(), [], 500);
        }
    }

    /**
     * Get staff list
     *
     * Returns list of staff members with role 'Facility' along with contact details.
     *
     * @authenticated
     *
     * @bodyParam search string optional Search by staff name, email, or phone number. Example: Jignesh
     * @bodyParam status string optional Filter by status (`active` or `inactive`). Example: active
     *
     * @response 200 {
     *   "status": "true",
     *   "message": "Staff list.",
     *   "data": [
     *     {
     *       "id": 3,
     *       "name": "Prof. Sharma",
     *       "email": "sharma@vrundavan.com",
     *       "phone": "9876500000",
     *       "biometric_id": "BIO102",
     *       "date_of_birth": "15-08-1988",
     *       "status": "active",
     *       "role": "Facility"
     *     }
     *   ]
     * }
     *
     * @response 401 scenario="Unauthenticated" {
     *   "status": "false",
     *   "message": "Unauthorization."
     * }
     */
    public function staffList(Request $request)
    {
        try {
            $admin = Auth::guard('api')->user();

            if (!$admin) {
                return $this->sendError("Unauthorization.", [], [], 401);
            }

            $query = User::whereHas('roles', function ($query) {
                $query->where('name', 'Facility');
            });

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $staff = $query->orderBy('name')
                ->get(['id', 'name', 'email', 'phone', 'biometric_id', 'date_of_birth', 'status']);

            $list = $staff->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'mobile' => $user->phone,
                    'biometric_id' => $user->biometric_id,
                    'date_of_birth' => $user->date_of_birth
                        ? Carbon::parse($user->date_of_birth)->format('d-m-Y')
                        : null,
                    'status' => $user->status,
                    'role' => 'Facility',
                ];
            })->values();

            return $this->sendResponse($list, 'Staff list.');
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', $e->getMessage(), [], 500);
        }
    }

    /**
     * Get supplier contact list
     *
     * Returns all supplier contact entries ordered by name.
     *
     * @authenticated
     *
     * @bodyParam search string optional Search by supplier name or mobile number. Example: Ocean
     *
     * @response 200 {
     *   "status": "true",
     *   "message": "Supplier contact list.",
     *   "data": [
     *     {
     *       "id": 2,
     *       "name": "Ocean Infotech - Technical Support",
     *       "mobile": "8528549049"
     *     }
     *   ]
     * }
     *
     * @response 401 scenario="Unauthenticated" {
     *   "status": "false",
     *   "message": "Unauthorization."
     * }
     */
    public function supplierList(Request $request)
    {
        try {
            $admin = Auth::guard('api')->user();

            if (!$admin) {
                return $this->sendError("Unauthorization.", [], [], 401);
            }

            $query = Supplier::query();

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('mobile', 'like', "%{$search}%");
                });
            }

            $suppliers = $query->orderBy('name')->get(['id', 'name', 'mobile']);

            $list = $suppliers->map(function ($supplier) {
                return [
                    'id' => $supplier->id,
                    'name' => $supplier->name,
                    'mobile' => $supplier->mobile,
                ];
            })->values();

            return $this->sendResponse($list, 'Supplier contact list.');
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', $e->getMessage(), [], 500);
        }
    }

    /**
     * Get slider list
     *
     * Returns all slider items ordered by descending ID. Can optionally filter by active or inactive status.
     *
     * @authenticated
     *
     * @bodyParam status string optional Filter by status (`active` or `inactive`). Example: active
     *
     * @response 200 {
     *   "status": "true",
     *   "message": "Slider list retrieved successfully.",
     *   "data": [
     *     {
     *       "id": 9,
     *       "title": "Vrundostav",
     *       "image": "http://127.0.0.1:8000/uploads/sliders/1784808114_event.jpeg",
     *       "raw_image": "uploads/sliders/1784808114_event.jpeg",
     *       "link": "https://example.com",
     *       "status": "active",
     *       "created_at": "23-07-2026 17:31"
     *     }
     *   ]
     * }
     *
     * @response 401 scenario="Unauthenticated" {
     *   "status": "false",
     *   "message": "Unauthorization."
     * }
     */
    public function sliderList(Request $request)
    {
        try {
            $admin = Auth::guard('api')->user();

            if (!$admin) {
                return $this->sendError("Unauthorization.", [], [], 401);
            }

            $query = Slider::orderBy('id', 'desc');

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $sliders = $query->get()->map(function ($slider) {
                return [
                    'id' => $slider->id,
                    'title' => $slider->title,
                    'image' => $slider->image ? asset($slider->image) : null,
                    'raw_image' => $slider->image,
                    'link' => $slider->link,
                    'status' => $slider->status ?? 'active',
                    'created_at' => $slider->created_at ? $slider->created_at->format('d-m-Y H:i') : null,
                ];
            })->values();

            return $this->sendResponse($sliders, 'Slider list retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', $e->getMessage(), [], 500);
        }
    }

    /**
     * Get single slider details
     *
     * Returns details of a specific slider for viewing or editing.
     *
     * @authenticated
     *
     * @urlParam id integer optional The ID of the slider. Example: 9
     * @bodyParam id integer optional The ID of the slider (if not provided in URL). Example: 9
     *
     * @response 200 {
     *   "status": "true",
     *   "message": "Slider details retrieved successfully.",
     *   "data": {
     *     "id": 9,
     *     "title": "Vrundostav",
     *     "image": "http://127.0.0.1:8000/uploads/sliders/1784808114_event.jpeg",
     *     "raw_image": "uploads/sliders/1784808114_event.jpeg",
     *     "link": "https://example.com",
     *     "status": "active",
     *     "created_at": "23-07-2026 17:31"
     *   }
     * }
     *
     * @response 404 scenario="Slider not found" {
     *   "status": "false",
     *   "message": "Slider not found."
     * }
     *
     * @response 422 scenario="Missing slider ID" {
     *   "status": "false",
     *   "message": "Slider ID is required."
     * }
     */
    public function sliderDetail(Request $request, $id = null)
    {
        try {
            $admin = Auth::guard('api')->user();

            if (!$admin) {
                return $this->sendError("Unauthorization.", [], [], 401);
            }

            $id = $id ?? $request->id;

            if (!$id) {
                return $this->sendError('Slider ID is required.', [], [], 422);
            }

            $slider = Slider::find($id);

            if (!$slider) {
                return $this->sendError('Slider not found.', [], [], 404);
            }

            $data = [
                'id' => $slider->id,
                'title' => $slider->title,
                'image' => $slider->image ? asset($slider->image) : null,
                'raw_image' => $slider->image,
                'link' => $slider->link,
                'status' => $slider->status ?? 'active',
                'created_at' => $slider->created_at ? $slider->created_at->format('d-m-Y H:i') : null,
            ];

            return $this->sendResponse($data, 'Slider details retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', $e->getMessage(), [], 500);
        }
    }

    /**
     * Add a new slider
     *
     * Creates a new slider with an uploaded banner image, title and optional URL link/status.
     *
     * @authenticated
     *
     * @bodyParam title string required The slider title to display. Example: Summer Workshop 2026
     * @bodyParam image file required The banner image file (jpeg, png, jpg, gif, webp; max 5MB).
     * @bodyParam link string optional Destination web link when clicked. Example: https://vrundavancomputers.com
     * @bodyParam status string optional Slider status (`active` or `inactive`). Default: active. Example: active
     *
     * @response 200 {
     *   "status": "true",
     *   "message": "Slider added successfully.",
     *   "data": {
     *     "id": 10,
     *     "title": "Summer Workshop 2026",
     *     "image": "http://127.0.0.1:8000/uploads/sliders/1729319800_banner.jpg",
     *     "raw_image": "uploads/sliders/1729319800_banner.jpg",
     *     "link": "https://vrundavancomputers.com",
     *     "status": "active"
     *   }
     * }
     *
     * @response 422 scenario="Validation failed" {
     *   "status": "false",
     *   "message": "The title field is required.",
     *   "messages": {
     *     "title": ["The title field is required."]
     *   }
     * }
     */
    public function sliderAdd(Request $request)
    {
        try {
            $admin = Auth::guard('api')->user();

            if (!$admin) {
                return $this->sendError("Unauthorization.", [], [], 401);
            }

            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
                'link' => 'nullable|string|max:255',
                'status' => 'nullable|in:active,inactive',
            ]);

            if ($validator->fails()) {
                return $this->sendError($validator->errors()->first(), $validator->errors(), [], 422);
            }

            $uploadDir = public_path('uploads/sliders');
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $image = $request->file('image');
            $fileName = time() . '_' . preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $image->getClientOriginalName());
            $image->move($uploadDir, $fileName);
            $imagePath = 'uploads/sliders/' . $fileName;

            $slider = Slider::create([
                'title' => $request->title,
                'image' => $imagePath,
                'link' => $request->link,
                'status' => $request->status ?? 'active',
                'created_by' => $admin->id,
            ]);

            $responseData = [
                'id' => $slider->id,
                'title' => $slider->title,
                'image' => asset($slider->image),
                'raw_image' => $slider->image,
                'link' => $slider->link,
                'status' => $slider->status,
            ];

            return $this->sendResponse($responseData, 'Slider added successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', $e->getMessage(), [], 500);
        }
    }

    /**
     * Update an existing slider
     *
     * Updates slider title, link, status, and optionally uploads a new banner image (replaces old file).
     *
     * @authenticated
     *
     * @urlParam id integer optional The ID of the slider to update. Example: 9
     * @bodyParam id integer optional The ID of the slider (if not provided in URL). Example: 9
     * @bodyParam title string required The updated slider title. Example: Updated Summer Workshop 2026
     * @bodyParam image file optional New replacement image file (jpeg, png, jpg, gif, webp; max 5MB).
     * @bodyParam link string optional Updated destination URL. Example: https://vrundavancomputers.com
     * @bodyParam status string optional Status (`active` or `inactive`). Example: active
     *
     * @response 200 {
     *   "status": "true",
     *   "message": "Slider updated successfully.",
     *   "data": {
     *     "id": 9,
     *     "title": "Updated Summer Workshop 2026",
     *     "image": "http://127.0.0.1:8000/uploads/sliders/1729319900_new_banner.jpg",
     *     "raw_image": "uploads/sliders/1729319900_new_banner.jpg",
     *     "link": "https://vrundavancomputers.com",
     *     "status": "active"
     *   }
     * }
     *
     * @response 404 scenario="Slider not found" {
     *   "status": "false",
     *   "message": "Slider not found."
     * }
     *
     * @response 422 scenario="Validation failed" {
     *   "status": "false",
     *   "message": "The title field is required."
     * }
     */
    public function sliderUpdate(Request $request, $id = null)
    {
        try {
            $admin = Auth::guard('api')->user();

            if (!$admin) {
                return $this->sendError("Unauthorization.", [], [], 401);
            }

            $id = $id ?? $request->id;

            if (!$id) {
                return $this->sendError('Slider ID is required.', [], [], 422);
            }

            $slider = Slider::find($id);

            if (!$slider) {
                return $this->sendError('Slider not found.', [], [], 404);
            }

            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
                'link' => 'nullable|string|max:255',
                'status' => 'nullable|in:active,inactive',
            ]);

            if ($validator->fails()) {
                return $this->sendError($validator->errors()->first(), $validator->errors(), [], 422);
            }

            if ($request->hasFile('image')) {
                if ($slider->image && file_exists(public_path($slider->image))) {
                    @unlink(public_path($slider->image));
                }

                $uploadDir = public_path('uploads/sliders');
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $image = $request->file('image');
                $fileName = time() . '_' . preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $image->getClientOriginalName());
                $image->move($uploadDir, $fileName);
                $slider->image = 'uploads/sliders/' . $fileName;
            }

            $slider->title = $request->title;
            $slider->link = $request->link;
            if ($request->filled('status')) {
                $slider->status = $request->status;
            }
            $slider->updated_by = $admin->id;
            $slider->save();

            $responseData = [
                'id' => $slider->id,
                'title' => $slider->title,
                'image' => asset($slider->image),
                'raw_image' => $slider->image,
                'link' => $slider->link,
                'status' => $slider->status,
            ];

            return $this->sendResponse($responseData, 'Slider updated successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', $e->getMessage(), [], 500);
        }
    }

    /**
     * Delete a slider
     *
     * Soft deletes a slider item by its ID.
     *
     * @authenticated
     *
     * @urlParam id integer optional The ID of the slider to delete. Example: 9
     * @bodyParam id integer optional The ID of the slider to delete (if not provided in URL). Example: 9
     *
     * @response 200 {
     *   "status": "true",
     *   "message": "Slider deleted successfully.",
     *   "data": []
     * }
     *
     * @response 404 scenario="Slider not found" {
     *   "status": "false",
     *   "message": "Slider not found."
     * }
     *
     * @response 422 scenario="Missing ID" {
     *   "status": "false",
     *   "message": "Slider ID is required."
     * }
     */
    public function sliderDelete(Request $request, $id = null)
    {
        try {
            $admin = Auth::guard('api')->user();

            if (!$admin) {
                return $this->sendError("Unauthorization.", [], [], 401);
            }

            $id = $id ?? $request->id;

            if (!$id) {
                return $this->sendError('Slider ID is required.', [], [], 422);
            }

            $slider = Slider::find($id);

            if (!$slider) {
                return $this->sendError('Slider not found.', [], [], 404);
            }

            $slider->deleted_by = $admin->id;
            $slider->save();
            $slider->delete();

            return $this->sendResponse([], 'Slider deleted successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', $e->getMessage(), [], 500);
        }
    }

    /**
     * Format admin payload for response
     */
    private function adminPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'status' => $user->status,
            'role' => $user->getRoleNames()->first(),
        ];
    }
}
