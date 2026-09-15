<?php

namespace App\Http\Controllers\admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Attedance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FacultyAttedanceController extends Controller
{
    public $modules = [];

    public function __construct()
    {
        $this->modules = [
            'title' => 'Faculty Attendance',
            'folder_path' => 'software.module.faculty_attendance',
            'route' => 'faculty-attendance',
            'table_name' => (new Attedance())->getTable(),
            'permission_prefix' => 'faculty-attendance',
        ];
    }

    public function index(Request $request)
    {
        $modules = $this->modules;
        $modules['login_user_role'] = Helper::getLoginUserRole();
        $modules['permission_list'] = Helper::directCan($modules['permission_prefix'] . '-list');
        
        if (!$modules['permission_list']) {
            if (isset($request) && $request->ajax()) {
                return response()->json(['error' => 'User does not have the right permissions.'], 403);
            }
            abort(403, 'User does not have the right permissions.');
        }

        View::share('modules', $modules);
        
        try {
            $columns = [
                (object)['data' => 'biometric_id', 'name' => 'biometric_id', 'td_label' => 'Biometric ID'],
                (object)['data' => 'faculty_name', 'name' => 'faculty_name', 'td_label' => 'Faculty Name', 'orderable' => false, 'searchable' => false],
                (object)['data' => 'date', 'name' => 'date', 'td_label' => 'Date'],
                (object)['data' => 'in_time', 'name' => 'in_time', 'td_label' => 'Punch In Time', 'orderable' => false, 'searchable' => false],
                (object)['data' => 'out_time', 'name' => 'out_time', 'td_label' => 'Punch Out Time', 'orderable' => false, 'searchable' => false],
                (object)['data' => 'DeviceKey', 'name' => 'DeviceKey', 'td_label' => 'Device Key', 'orderable' => false, 'searchable' => false],
                (object)['data' => 'DeviceName', 'name' => 'DeviceName', 'td_label' => 'Device Name', 'orderable' => false, 'searchable' => false],
            ];
            View::share("columns", $columns);

            if ($request->ajax()) {
                $user = Auth::user();
                $isFaculty = Helper::getLoginUserRole() === 'Facility';

                $data = Attedance::select([
                        'attedance.biometric_id',
                        'attedance.date',
                        DB::raw('MIN(attedance.in_time) as first_punch'),
                        DB::raw('MAX(attedance.in_time) as last_punch'),
                        DB::raw('MIN(attedance.DeviceKey) as DeviceKey'),
                        DB::raw('MIN(attedance.DeviceName) as DeviceName'),
                        'users.name as faculty_name',
                    ])
                    ->join('users', 'users.biometric_id', '=', 'attedance.biometric_id');

                if ($isFaculty) {
                    $biometricId = $user->biometric_id ?? '---';
                    $data = $data->where('attedance.biometric_id', $biometricId);
                } else {
                    $facultyBiometrics = User::whereHas('roles', function ($query) {
                        $query->where('name', 'Facility');
                    })->pluck('biometric_id')->filter()->toArray();

                    $data = $data->whereIn('attedance.biometric_id', $facultyBiometrics);
                }

                $data = $data->groupBy('attedance.biometric_id', 'attedance.date', 'users.name')
                    ->orderByDesc('attedance.date');

                $returnData = DataTables::of($data)
                    ->addIndexColumn()
                    ->filter(function ($query) use ($request) {
                        $search = trim((string) ($request->input('search.value') ?? $request->search ?? ''));
                        if ($search === '') {
                            return;
                        }
                        $query->where(function ($q) use ($search) {
                            $q->where('attedance.date', 'like', "%{$search}%")
                              ->orWhere('users.name', 'like', "%{$search}%")
                              ->orWhere('attedance.DeviceKey', 'like', "%{$search}%")
                              ->orWhere('attedance.DeviceName', 'like', "%{$search}%");
                        });
                    })
                    ->editColumn('biometric_id', function ($row) {
                        return $row->biometric_id ?: '-';
                    })
                    ->addColumn('faculty_name', function ($row) {
                        if (empty($row->faculty_name)) {
                            return '-';
                        }
                        $name = e($row->faculty_name);
                        $bio = e($row->biometric_id);
                        return '<a href="javascript:void(0)" class="faculty-name-link fw-bold text-primary text-decoration-none" data-biometric-id="' . $bio . '" data-faculty-name="' . $name . '">' . $name . '</a>';
                    })
                    ->editColumn('date', function ($row) {
                        return Carbon::parse($row->date)->format('d-m-Y');
                    })
                    ->editColumn('in_time', function ($row) {
                        if (empty($row->first_punch)) {
                            return '<p class="text-center">-</p>';
                        }
                        return Carbon::parse($row->first_punch)->format('h:i A');
                    })
                    ->addColumn('out_time', function ($row) {
                        if (empty($row->last_punch) || $row->last_punch === $row->first_punch) {
                            return '<p class="text-center">-</p>';
                        }
                        return Carbon::parse($row->last_punch)->format('h:i A');
                    })
                    ->editColumn('DeviceKey', function ($row) {
                        return $row->DeviceKey ?: '-';
                    })
                    ->editColumn('DeviceName', function ($row) {
                        return $row->DeviceName ?: '-';
                    })
                    ->rawColumns(['faculty_name', 'date', 'in_time', 'out_time'])
                    ->make(true);
                    
                return $returnData;
            }
            
            return view($modules['folder_path'] . '.index');
        } catch (\Exception $e) {
            return Redirect::route('dashboard')->withErrors($e->getMessage());
        }
    }

    public function monthlyDetails(Request $request)
    {
        $modules = $this->modules;
        $permissionList = Helper::directCan($modules['permission_prefix'] . '-list');
        
        if (!$permissionList) {
            return response()->json(['error' => 'User does not have the right permissions.'], 403);
        }

        $biometricId = $request->input('biometric_id');
        $monthInput  = $request->input('month') ?: Carbon::now()->format('Y-m');

        if (empty($biometricId)) {
            return response()->json(['status' => false, 'message' => 'Biometric ID is required.'], 400);
        }

        $user = Auth::user();
        $isFaculty = Helper::getLoginUserRole() === 'Facility';
        if ($isFaculty && ($user->biometric_id != $biometricId)) {
            return response()->json(['error' => 'Unauthorized access.'], 403);
        }

        $targetUser = User::where('biometric_id', $biometricId)->first();
        $facultyName = $targetUser ? $targetUser->name : 'Faculty (' . $biometricId . ')';

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
                DB::raw('MIN(DeviceKey) as DeviceKey'),
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
        $absentDays = 0;
        $sundayCount = 0;
        $totalDaysInMonth = $startOfMonth->daysInMonth;

        for ($d = $startOfMonth->copy(); $d->lte($endOfMonth); $d->addDay()) {
            $dateStr = $d->format('Y-m-d');
            $isSunday = ($d->dayOfWeek === Carbon::SUNDAY);
            $isFuture = $d->gt($today);
            $isToday  = $d->isToday();

            $dayLog = $logs->get($dateStr);
            $isPresent = !empty($dayLog);

            $inTimeStr   = '-';
            $outTimeStr  = '-';
            $durationStr = '-';
            $deviceName  = $dayLog->DeviceName ?? '-';

            if ($isPresent) {
                $presentDays++;
                $firstPunch = $dayLog->first_punch;
                $lastPunch  = $dayLog->last_punch;

                if (!empty($firstPunch)) {
                    $inTimeStr = Carbon::parse($firstPunch)->format('h:i A');
                }

                if (!empty($lastPunch) && $lastPunch !== $firstPunch) {
                    $outTimeStr = Carbon::parse($lastPunch)->format('h:i A');

                    $start = Carbon::parse($firstPunch);
                    $end   = Carbon::parse($lastPunch);
                    $diff  = $start->diff($end);
                    $durationStr = $diff->format('%h hrs %i mins');
                }
            } else {
                if ($isSunday) {
                    $sundayCount++;
                } else if (!$isFuture) {
                    $absentDays++;
                }
            }

            if ($isPresent) {
                $statusHtml = '<span class="badge bg-success"><i class="fa-solid fa-check me-1"></i>Present</span>';
            } else if ($isSunday) {
                $statusHtml = '<span class="badge bg-secondary"><i class="fa-solid fa-mug-hot me-1"></i>Sunday</span>';
            } else if ($isFuture) {
                $statusHtml = '<span class="badge bg-light text-muted border">Upcoming</span>';
            } else {
                $statusHtml = '<span class="badge bg-danger"><i class="fa-solid fa-xmark me-1"></i>Absent</span>';
            }

            $rows[] = [
                'date_formatted' => $d->format('d-m-Y'),
                'day_name'       => $d->format('l'),
                'in_time'        => $inTimeStr,
                'out_time'       => $outTimeStr,
                'duration'       => $durationStr,
                'device_name'    => $deviceName,
                'status_html'    => $statusHtml,
                'is_sunday'      => $isSunday,
                'is_today'       => $isToday,
            ];
        }

        return response()->json([
            'status'        => true,
            'faculty_name'  => $facultyName,
            'biometric_id'  => $biometricId,
            'month_label'   => $startOfMonth->format('F Y'),
            'month_value'   => $monthInput,
            'stats'         => [
                'total_days'   => $totalDaysInMonth,
                'present_days' => $presentDays,
                'absent_days'  => $absentDays,
                'sundays'      => $sundayCount,
            ],
            'rows'          => $rows,
        ]);
    }
}

