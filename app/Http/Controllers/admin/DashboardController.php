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

        // Current month upcoming student birthdays (today + remaining days)
        // Skip students whose course registration status is Cancel
        $upcomingStudentBirthdays = Admission::whereNotNull('date_of_birth')
            ->whereMonth('date_of_birth', $today->month)
            ->whereDay('date_of_birth', '>=', $today->day)
            ->whereHas('courses', function ($q) {
                $q->whereNotIn('status', ['Cancel', 'cancel']);
            })
            ->select('id', 'first_name', 'last_name', 'father_name', 'gr_no', 'profile_pic', 'date_of_birth', 'mobile_no')
            ->get()
            ->map(function ($student) use ($today) {
                $dob = Carbon::parse($student->date_of_birth);
                $day = $dob->day;
                if (!checkdate($today->month, $day, $today->year)) {
                    $day = 28;
                }
                $birthdayThisYear = Carbon::create($today->year, $today->month, $day)->startOfDay();
                $daysRemaining = (int) $today->diffInDays($birthdayThisYear, false);

                if ($daysRemaining === 0) {
                    $statusLabel = 'Today';
                } elseif ($daysRemaining === 1) {
                    $statusLabel = 'Tomorrow';
                } else {
                    $statusLabel = "In {$daysRemaining} days";
                }

                $student->birthday_day = $day;
                $student->birthday_date = $dob->format('d F');
                $student->days_remaining = $daysRemaining;
                $student->status_label = $statusLabel;
                $student->display_name = trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? ''));

                return $student;
            })
            ->sortBy(['birthday_day', 'id'])
            ->values();
        $upcomingStudentBirthdaysCount = $upcomingStudentBirthdays->count();

        // Current month upcoming faculty birthdays (today + remaining days)
        $upcomingFacilityBirthdays = \App\Models\User::whereHas('roles', function ($q) {
                $q->where('name', 'Facility');
            })
            ->whereNotNull('date_of_birth')
            ->whereMonth('date_of_birth', $today->month)
            ->whereDay('date_of_birth', '>=', $today->day)
            ->select('id', 'name', 'date_of_birth', 'phone')
            ->get()
            ->map(function ($user) use ($today) {
                $dob = Carbon::parse($user->date_of_birth);
                $day = $dob->day;
                if (!checkdate($today->month, $day, $today->year)) {
                    $day = 28;
                }
                $birthdayThisYear = Carbon::create($today->year, $today->month, $day)->startOfDay();
                $daysRemaining = (int) $today->diffInDays($birthdayThisYear, false);

                if ($daysRemaining === 0) {
                    $statusLabel = 'Today';
                } elseif ($daysRemaining === 1) {
                    $statusLabel = 'Tomorrow';
                } else {
                    $statusLabel = "In {$daysRemaining} days";
                }

                $user->birthday_day = $day;
                $user->birthday_date = $dob->format('d F');
                $user->days_remaining = $daysRemaining;
                $user->status_label = $statusLabel;

                return $user;
            })
            ->sortBy(['birthday_day', 'id'])
            ->values();
        $upcomingFacilityBirthdaysCount = $upcomingFacilityBirthdays->count();
        $currentMonthName = $today->format('F');

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
            'currentMonthName'
        ));
    }
}
