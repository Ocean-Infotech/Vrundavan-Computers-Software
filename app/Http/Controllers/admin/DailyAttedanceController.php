<?php

namespace App\Http\Controllers\admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Attedance;
use App\Models\Admission;
use App\Models\CourceRegistration;
use App\Models\Master\MasterCourse;
use App\Models\Master\MasterBatch;
use App\Models\Master\MasterClass;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\Facades\DataTables;

class DailyAttedanceController extends Controller
{
    public $modules = [];

    public function __construct()
    {
        $this->modules = [
            'title' => 'Daily Attendance',
            'folder_path' => 'software.module.daily_attendance',
            'route' => 'daily-attendance',
            'permission_prefix' => 'daily-attendance',
        ];
    }

    public function index(Request $request)
    {
        $modules = $this->modules;
        $modules['login_user_role'] = Helper::getLoginUserRole();
        $modules['permission_list'] = Helper::directCan('daily-attendance-list');

        if (!$modules['permission_list']) {
            if ($request->ajax()) {
                return response()->json(['error' => 'User does not have the right permissions.'], 403);
            }
            abort(403, 'User does not have the right permissions.');
        }

        View::share('modules', $modules);

        if ($request->ajax()) {
            $today = Carbon::today();
            $dateFormatted = $today->format('Y-m-d');
            $isSunday = ($today->dayOfWeek === Carbon::SUNDAY);

            if ($isSunday) {
                return DataTables::of(collect([]))
                    ->with([
                        'total_count'   => 0,
                        'present_count' => 0,
                        'absent_count'  => 0,
                        'is_sunday'     => true,
                        'date_label'    => $today->format('d-m-Y (l)'),
                    ])
                    ->make(true);
            }

            $selectedDate = $dateFormatted;

            $latestRegIds = DB::table('cource_registration')
                ->select('register_id', DB::raw('MAX(id) as max_id'))
                ->whereNull('deleted_at')
                ->groupBy('register_id');

            // Query active student course registrations (only students)
            $studentsQuery = CourceRegistration::joinSub($latestRegIds, 'latest_reg', function ($join) {
                    $join->on('cource_registration.id', '=', 'latest_reg.max_id');
                })
                ->join('admission', 'admission.id', '=', 'cource_registration.register_id')
                ->whereNull('admission.deleted_at')
                ->whereNull('cource_registration.deleted_at')
                ->whereIn('cource_registration.status', ['active', 'Running'])
                ->whereNotNull('admission.biometric_id')
                ->where('admission.biometric_id', '!=', '');

            if ($request->filled('course_id')) {
                $studentsQuery->where('cource_registration.course_id', $request->course_id);
            }

            if ($request->filled('batch_id')) {
                $studentsQuery->where('cource_registration.batch_id', $request->batch_id);
            }

            if ($request->filled('class_id')) {
                $classIds = (array) $request->class_id;
                $studentsQuery->whereIn('cource_registration.class_id', $classIds);
            }

            $students = $studentsQuery->with(['admission', 'course', 'batch', 'class'])->get();

            // Fetch attendance logs on this date for all filtered students
            $biometricIds = $students->pluck('admission.biometric_id')->filter()->toArray();

            $attendanceLogs = Attedance::select([
                    'biometric_id',
                    'date',
                    DB::raw('MIN(in_time) as first_punch'),
                    DB::raw('MAX(in_time) as last_punch'),
                    DB::raw('MIN(DeviceName) as DeviceName'),
                ])
                ->whereDate('date', $dateFormatted)
                ->whereIn('biometric_id', $biometricIds)
                ->groupBy('biometric_id', 'date')
                ->get()
                ->keyBy('biometric_id');

            $data = [];
            $presentCount = 0;
            $absentCount  = 0;

            foreach ($students as $registration) {
                $student = $registration->admission;
                if (!$student) continue;

                $bioId = $student->biometric_id;
                $log   = $attendanceLogs->get($bioId);

                $isPresent = !empty($log);
                $inTimeStr  = '-';
                $outTimeStr = '-';
                $deviceName = $log->DeviceName ?? '-';

                if ($isPresent) {
                    $presentCount++;
                    if (!empty($log->first_punch)) {
                        $inTimeStr = Carbon::parse($log->first_punch)->format('h:i A');
                    }
                    if (!empty($log->last_punch) && $log->last_punch !== $log->first_punch) {
                        $outTimeStr = Carbon::parse($log->last_punch)->format('h:i A');
                    }
                } else {
                    $absentCount++;
                }

                $c = $registration->course->course_name ?? '';
                $b = $registration->batch->batch_name ?? '';
                $cl = $registration->class->class ?? '';
                $cbc = trim("{$c} - {$b} - {$cl}", ' -');

                $data[] = [
                    'biometric_id'       => $bioId,
                    'student_name'       => trim("{$student->first_name} {$student->last_name} {$student->father_name}"),
                    'course_batch_class' => $cbc ?: '-',
                    'date'               => Carbon::parse($selectedDate)->format('d-m-Y'),
                    'in_time'            => $inTimeStr,
                    'out_time'           => $outTimeStr,
                    'device_name'        => $deviceName,
                    'status'             => $isPresent ? 'Present' : 'Absent',
                    'is_present'         => $isPresent,
                ];
            }

            $collection = collect($data);

            if ($request->filled('search.value')) {
                $search = strtolower($request->input('search.value'));
                $collection = $collection->filter(function ($item) use ($search) {
                    return str_contains(strtolower($item['student_name']), $search) ||
                           str_contains(strtolower($item['biometric_id']), $search) ||
                           str_contains(strtolower($item['course_batch_class']), $search);
                });
            }

            return DataTables::of($collection)
                ->addIndexColumn()
                ->addColumn('student_name_link', function ($row) {
                    $name = e($row['student_name']);
                    $bio  = e($row['biometric_id']);
                    return '<a href="javascript:void(0)" class="student-name-link fw-semibold text-primary text-decoration-none" '
                         . 'data-biometric-id="' . $bio . '" '
                         . 'data-student-name="' . $name . '">' . $name . '</a>';
                })
                ->addColumn('status_badge', function ($row) {
                    if ($row['is_present']) {
                        return '<span class="badge bg-success"><i class="fa-solid fa-check me-1"></i>Present</span>';
                    }
                    return '<span class="badge bg-danger"><i class="fa-solid fa-xmark me-1"></i>Absent</span>';
                })
                ->rawColumns(['student_name_link', 'status_badge'])
                ->with([
                    'total_count'   => $collection->count(),
                    'present_count' => $presentCount,
                    'absent_count'  => $absentCount,
                ])
                ->make(true);
        }

        $columns = [
            (object)['data' => 'DT_RowIndex', 'name' => 'id', 'td_label' => 'No', 'orderable' => false, 'searchable' => false],
            (object)['data' => 'biometric_id', 'name' => 'biometric_id', 'td_label' => 'Biometric ID'],
            (object)['data' => 'student_name_link', 'name' => 'student_name', 'td_label' => 'Student Name', 'orderable' => false, 'searchable' => false],
            (object)['data' => 'course_batch_class', 'name' => 'course_batch_class', 'td_label' => 'Course - Batch - Class', 'orderable' => false, 'searchable' => false],
            (object)['data' => 'date', 'name' => 'date', 'td_label' => 'Date'],
            (object)['data' => 'in_time', 'name' => 'in_time', 'td_label' => 'Punch In Time', 'className' => 'text-center'],
            (object)['data' => 'out_time', 'name' => 'out_time', 'className' => 'text-center', 'td_label' => 'Punch Out Time'],
            (object)['data' => 'device_name', 'name' => 'device_name', 'td_label' => 'Device Name'],
            (object)['data' => 'status_badge', 'name' => 'status', 'td_label' => 'Status', 'className' => 'text-center'],
        ];

        $courses = MasterCourse::where('status', 'active')->orderBy('course_name', 'asc')->get();
        $batches = MasterBatch::where('status', 'active')->orderBy('batch_name', 'asc')->get();
        $classes = MasterClass::where('status', 'active')->orderBy('class', 'asc')->get();

        return view($modules['folder_path'] . '.index', compact('columns', 'courses', 'batches', 'classes'));
    }

    public function studentDetail(Request $request)
    {
        $biometricId = $request->input('biometric_id');
        $monthInput  = $request->input('month') ?: Carbon::now()->format('Y-m');

        if (empty($biometricId)) {
            return response()->json(['status' => false, 'message' => 'Biometric ID is required.'], 400);
        }

        $student = Admission::where('biometric_id', $biometricId)->first();
        $studentName = $student ? trim("{$student->first_name} {$student->last_name} {$student->father_name}") : 'Student (' . $biometricId . ')';

        try {
            $startOfMonth = Carbon::parse($monthInput . '-01')->startOfMonth();
            $endOfMonth   = Carbon::parse($monthInput . '-01')->endOfMonth();
        } catch (\Exception $e) {
            $startOfMonth = Carbon::now()->startOfMonth();
            $endOfMonth   = Carbon::now()->endOfMonth();
            $monthInput   = Carbon::now()->format('Y-m');
        }

        $today = Carbon::today();

        $logs = Attedance::select([
                'date',
                DB::raw('MIN(in_time) as first_punch'),
                DB::raw('MAX(in_time) as last_punch'),
                DB::raw('MIN(DeviceName) as DeviceName'),
            ])
            ->where('biometric_id', $biometricId)
            ->whereBetween('date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
            ->groupBy('date')
            ->get()
            ->keyBy(function ($item) {
                return Carbon::parse($item->date)->format('Y-m-d');
            });

        $rows = [];
        $presentDays = 0;
        $absentDays  = 0;
        $sundayCount = 0;

        for ($d = $startOfMonth->copy(); $d->lte($endOfMonth); $d->addDay()) {
            $dateStr  = $d->format('Y-m-d');
            $isSunday = ($d->dayOfWeek === Carbon::SUNDAY);
            $isFuture = $d->gt($today);

            $dayLog    = $logs->get($dateStr);
            $isPresent = !empty($dayLog);

            $inTimeStr  = '-';
            $outTimeStr = '-';
            $deviceName = $dayLog->DeviceName ?? '-';

            if ($isPresent) {
                $presentDays++;
                if (!empty($dayLog->first_punch)) {
                    $inTimeStr = Carbon::parse($dayLog->first_punch)->format('h:i A');
                }
                if (!empty($dayLog->last_punch) && $dayLog->last_punch !== $dayLog->first_punch) {
                    $outTimeStr = Carbon::parse($dayLog->last_punch)->format('h:i A');
                }
            } else {
                if ($isSunday) {
                    $sundayCount++;
                } else if (!$isFuture) {
                    $absentDays++;
                }
            }

            if ($isPresent) {
                $statusHtml = '<span class="badge bg-success">Present</span>';
            } else if ($isSunday) {
                $statusHtml = '<span class="badge bg-secondary">Sunday</span>';
            } else if ($isFuture) {
                $statusHtml = '<span class="badge bg-light text-muted border">Upcoming</span>';
            } else {
                $statusHtml = '<span class="badge bg-danger">Absent</span>';
            }

            $rows[] = [
                'date_formatted' => $d->format('d-m-Y'),
                'day_name'       => $d->format('l'),
                'in_time'        => $inTimeStr,
                'out_time'       => $outTimeStr,
                'device_name'    => $deviceName,
                'status_html'    => $statusHtml,
            ];
        }

        return response()->json([
            'status'       => true,
            'student_name' => $studentName,
            'biometric_id' => $biometricId,
            'stats'        => [
                'total_days'   => $startOfMonth->daysInMonth,
                'present_days' => $presentDays,
                'absent_days'  => $absentDays,
                'sundays'      => $sundayCount,
            ],
            'rows'         => $rows,
        ]);
    }
}
