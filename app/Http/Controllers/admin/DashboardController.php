<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Admission;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class DashboardController extends Controller
{
    public $modules = [];
    public function __construct()
    {
        $this->modules = [
            'title' => 'Dashboard',
            'folder_path' => 'software.module',
            'route' => '',
            'table_name' => '',
            'permisstion_prefix' => '',
        ];

        // $this->middleware('permission:permissions-list|permissions-create|permissions-edit|permissions-delete', ['only' => ['index','show']]);
        // $this->middleware('permission:'.$this->modules['permisstion_prefix'].'-list', ['only' => ['index','show']]);
        // $this->middleware('permission:'.$this->modules['permisstion_prefix'].'-create', ['only' => ['create','store']]);
        // $this->middleware('permission:'.$this->modules['permisstion_prefix'].'-edit', ['only' => ['edit','update']]);
        // $this->middleware('permission:'.$this->modules['permisstion_prefix'].'-delete', ['only' => ['destroy']]);
    }

    public function dashboard(Request $request)
    {
        $modules = $this->modules;
        View::share('modules', $modules);

        $isFaculty = \App\Helpers\Helper::getLoginUserRole() === 'Facility';
        $tasks = [];
        if ($isFaculty) {
            $tasks = \App\Models\Task::where('assigned_to', Auth::id())
                ->orderBy('id', 'desc')
                ->get();
        }

        $totalStudents = Admission::count();
        $totalCourses = \App\Models\Master\MasterCourse::count();
        $studentRequestsCount = \App\Models\StudentRequest::count();
        $todayTests = \App\Models\Test::whereDate('date', \Carbon\Carbon::today())
            ->with(['course'])
            ->get();
        $todayTestsCount = $todayTests->count();
        $timetablesCount = \App\Models\Timetable::whereNotNull('attachment')
            ->where('attachment', '!=', '')
            ->count();
        $timetables = \App\Models\Timetable::whereNotNull('attachment')
            ->where('attachment', '!=', '')
            ->with(['course', 'batch', 'class'])
            ->get();

        $today = Carbon::today();
        $selectedBirthdayMonth = (int) $request->get('birthday_month', $today->month);
        if ($selectedBirthdayMonth < 1 || $selectedBirthdayMonth > 12) {
            $selectedBirthdayMonth = $today->month;
        }
        $isCurrentBirthdayMonth = $selectedBirthdayMonth === (int) $today->month;
        $selectedBirthdayMonthName = Carbon::create($today->year, $selectedBirthdayMonth, 1)->format('F');

        $studentBirthdays = Admission::whereMonth('date_of_birth', $today->month)
            ->whereDay('date_of_birth', $today->day)
            ->whereHas('courses', function ($q) {
                $q->whereNotIn('status', ['Cancel', 'cancel']);
            })
            ->select('id', 'first_name', 'last_name', 'father_name', 'gr_no', 'profile_pic')
            ->get();
        $studentBirthdaysCount = $studentBirthdays->count();

        $facilityBirthdays = \App\Models\User::whereHas('roles', function ($q) {
                $q->where('name', 'Facility');
            })
            ->whereMonth('date_of_birth', $today->month)
            ->whereDay('date_of_birth', $today->day)
            ->select('id', 'name')
            ->get();
        $facilityBirthdaysCount = $facilityBirthdays->count();

        // Upcoming / month birthdays (filter by birthday_month)
        // Current month: today + remaining days. Other months: full month list.
        $mapBirthdayStatus = function ($dateOfBirth) use ($today, $selectedBirthdayMonth) {
            $dob = Carbon::parse($dateOfBirth);
            $day = $dob->day;
            if (!checkdate($selectedBirthdayMonth, $day, $today->year)) {
                $day = 28;
            }
            $birthdayThisYear = Carbon::create($today->year, $selectedBirthdayMonth, $day)->startOfDay();
            $daysRemaining = (int) $today->diffInDays($birthdayThisYear, false);

            if ($daysRemaining === 0) {
                $statusLabel = 'Today';
            } elseif ($daysRemaining === 1) {
                $statusLabel = 'Tomorrow';
            } elseif ($daysRemaining > 1) {
                $statusLabel = "In {$daysRemaining} days";
            } else {
                $statusLabel = abs($daysRemaining) . ' days ago';
            }

            return [
                'birthday_day' => $day,
                'birthday_date' => Carbon::create($today->year, $selectedBirthdayMonth, $day)->format('d F'),
                'days_remaining' => $daysRemaining,
                'status_label' => $statusLabel,
            ];
        };

        $studentBirthdayQuery = Admission::whereNotNull('date_of_birth')
            ->whereMonth('date_of_birth', $selectedBirthdayMonth)
            ->whereHas('courses', function ($q) {
                $q->whereNotIn('status', ['Cancel', 'cancel']);
            })
            ->select('id', 'first_name', 'last_name', 'father_name', 'gr_no', 'profile_pic', 'date_of_birth', 'mobile_no');

        if ($isCurrentBirthdayMonth) {
            $studentBirthdayQuery->whereDay('date_of_birth', '>=', $today->day);
        }

        $upcomingStudentBirthdays = $studentBirthdayQuery->get()
            ->map(function ($student) use ($mapBirthdayStatus) {
                $meta = $mapBirthdayStatus($student->date_of_birth);
                $student->birthday_day = $meta['birthday_day'];
                $student->birthday_date = $meta['birthday_date'];
                $student->days_remaining = $meta['days_remaining'];
                $student->status_label = $meta['status_label'];
                $student->display_name = trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? ''));

                return $student;
            })
            ->sortBy(['birthday_day', 'id'])
            ->values();
        $upcomingStudentBirthdaysCount = $upcomingStudentBirthdays->count();

        $facilityBirthdayQuery = \App\Models\User::whereHas('roles', function ($q) {
                $q->where('name', 'Facility');
            })
            ->whereNotNull('date_of_birth')
            ->whereMonth('date_of_birth', $selectedBirthdayMonth)
            ->select('id', 'name', 'date_of_birth', 'phone');

        if ($isCurrentBirthdayMonth) {
            $facilityBirthdayQuery->whereDay('date_of_birth', '>=', $today->day);
        }

        $upcomingFacilityBirthdays = $facilityBirthdayQuery->get()
            ->map(function ($user) use ($mapBirthdayStatus) {
                $meta = $mapBirthdayStatus($user->date_of_birth);
                $user->birthday_day = $meta['birthday_day'];
                $user->birthday_date = $meta['birthday_date'];
                $user->days_remaining = $meta['days_remaining'];
                $user->status_label = $meta['status_label'];

                return $user;
            })
            ->sortBy(['birthday_day', 'id'])
            ->values();
        $upcomingFacilityBirthdaysCount = $upcomingFacilityBirthdays->count();
        $currentMonthName = $selectedBirthdayMonthName;
        $birthdayMonths = collect(range(1, 12))->mapWithKeys(function ($month) use ($today) {
            return [$month => Carbon::create($today->year, $month, 1)->format('F')];
        });

        return view($modules['folder_path'] . '.dashboard', compact(
            'totalStudents',
            'totalCourses',
            'studentRequestsCount',
            'todayTestsCount',
            'timetablesCount',
            'timetables',
            'isFaculty',
            'tasks',
            'todayTests',
            'studentBirthdays',
            'studentBirthdaysCount',
            'facilityBirthdays',
            'facilityBirthdaysCount',
            'upcomingStudentBirthdays',
            'upcomingStudentBirthdaysCount',
            'upcomingFacilityBirthdays',
            'upcomingFacilityBirthdaysCount',
            'currentMonthName',
            'selectedBirthdayMonth',
            'birthdayMonths',
            'isCurrentBirthdayMonth'
        ));
    }
}
