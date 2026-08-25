<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Admission;
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

        $studentBirthdays = Admission::whereMonth('date_of_birth', \Carbon\Carbon::today()->month)
            ->whereDay('date_of_birth', \Carbon\Carbon::today()->day)
            ->select('id', 'first_name', 'last_name', 'father_name', 'gr_no', 'profile_pic')
            ->get();
        $studentBirthdaysCount = $studentBirthdays->count();

        $facilityBirthdays = \App\Models\User::whereHas('roles', function($q) {
                $q->where('name', 'Facility');
            })
            ->whereMonth('date_of_birth', \Carbon\Carbon::today()->month)
            ->whereDay('date_of_birth', \Carbon\Carbon::today()->day)
            ->select('id', 'name')
            ->get();
        $facilityBirthdaysCount = $facilityBirthdays->count();

        return view($modules['folder_path'] . '.dashboard', compact(
            'totalStudents', 'totalCourses', 'studentRequestsCount', 'todayTestsCount', 'timetablesCount', 'timetables', 'isFaculty', 'tasks', 'todayTests', 'studentBirthdays', 'studentBirthdaysCount', 'facilityBirthdays', 'facilityBirthdaysCount'
        ));
    }
}
