<?php

namespace App\Http\Controllers;

use App\Http\Requests\NotificationSettingRequest;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use App\Http\Controllers\Controller;
use App\Models\NotificationSetting;
use App\Models\Master\MasterCourse;
use App\Models\Admission;
use App\Models\CourceRegistration;
use App\Models\StudentDeviceToken;
use App\Models\Notification;
use App\Services\FirebaseService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;

class NotificationSettingController extends Controller
{
    public $modules = [];

    public function __construct()
    {
        $this->modules = [
            'title' => 'Notification Setting',
            'folder_path' => 'software.module.notification_setting',
            'route' => 'notification_setting',
            'table_name' => (new NotificationSetting())->getTable(),
            'permission_prefix' => 'notification-setting',
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $modules = $this->modules;
        $modules['login_user_role'] = Helper::getLoginUserRole();
        $modules['permission_list'] = Helper::directCan($modules['permission_prefix'] . '-list');
        $modules['permission_add'] = Helper::directCan($modules['permission_prefix'] . '-create');
        $modules['permission_edit'] = Helper::directCan($modules['permission_prefix'] . '-edit');
        $modules['permission_delete'] = Helper::directCan($modules['permission_prefix'] . '-delete');

        if (!$modules['permission_list']) {
            if ($request->ajax()) {
                return response()->json(['error' => 'User does not have the right permissions.'], 403);
            }
            abort(403, 'User does not have the right permissions.');
        }

        View::share('modules', $modules);

        $columns = [
            (object)['data' => "title", 'name' => 'title', 'td_label' => 'Title'],
            (object)['data' => "body", 'name' => 'body', 'td_label' => 'Message'],
            (object)['data' => "course_id", 'name' => 'course_id', 'td_label' => 'Course'],
            (object)['data' => "student_name", 'td_label' => 'Student'],
            (object)['data' => "action", 'name' => 'action', 'td_label' => 'Action', 'orderable' => false, 'searchable' => false, 'className' => 'w-10 text-center'],
        ];
        View::share("columns", $columns);

        try {
            if ($request->ajax()) {
                $data = NotificationSetting::with('course:id,course_name');

                return Datatables::of($data)
                    ->addIndexColumn()
                    ->filter(function ($query) use ($request) {
                        if ($request->has('search')) {
                            $query->where('title', 'like', "%" . $request->search . "%")
                                  ->orWhere('body', 'like', "%" . $request->search . "%");
                        }
                    })
                    ->addColumn('student_name', fn($row) => $row->student_names)
                    ->editColumn('course_id', fn($row) => $row->course?->course_name ?? '-')
                    ->addColumn('action', function ($row) use ($modules) {
                        $btn = '';
                        if ($modules['permission_edit']) {
                            $btn .= '<a href="' . route($modules["route"] . ".edit", [$row->id]) . '" class="btn btn-light btn-icon mx-1"><i class="fa-solid fa-pen-to-square"></i></a>';
                        }
                        if ($modules['permission_delete']) {
                            $btn .= '<a href="javascript:void(0)" data-id="' . $row->id . '" data-did="' . route($modules["route"] . ".destroy", [$row->id]) . '" class="btn btn-danger btn-icon deletebutton mx-1"><i class="fa-solid fa-trash"></i></a>';
                        }
                        return $btn ?: '-';
                    })
                    ->rawColumns(['action'])
                    ->make(true);
            }

            return view($modules['folder_path'] . '.index');
        } catch (\Exception $e) {
            return Redirect::route('dashboard')->withErrors($e->getMessage());
        }
    }

    public function create()
    {
        $modules = $this->modules;
        View::share('modules', $modules);

        $courses = MasterCourse::pluck('course_name', 'id');
        $students = [];
        $selectedStudents = [];

        View::share('courses', $courses);
        View::share('students', $students);
        View::share('selectedStudents', $selectedStudents);

        return view($modules['folder_path'] . '.form');
    }

    /**
     * Store notification + send firebase push
     */
    public function store(NotificationSettingRequest $request)
    {
        $validated = $request->validated();

        try {
            $studentIds = array_filter($validated['student_id'], fn($id) => $id !== 'all');
            $validated['student_id'] = implode(',', $studentIds);
            $validated['created_by'] = Auth::user()->id;

            // 1. Save in notification_settings
            $notificationSetting = NotificationSetting::create($validated);

            // 2. Get student list (from request or course)
            $studentIds = $studentIds;
            if (empty($studentIds) && $notificationSetting->course_id) {
                $studentIds = CourceRegistration::where('course_id', $notificationSetting->course_id)
                    ->pluck('admission_id')
                    ->toArray();
            }

            // 3. Send push to each student
            $firebase = app(FirebaseService::class);

            foreach ($studentIds as $sid) {
                $device = StudentDeviceToken::where('student_id', $sid)->latest()->first();

                if (!$device || !$device->device_token) {
                    Log::info("No device token found for student_id=" . $sid);
                    continue;
                }

                $results = $firebase->sendNotification(
                    [$device->device_token],
                    $notificationSetting->title,
                    $notificationSetting->body
                );

                // 4. Save in notifications history
                foreach ($results as $result) {
                    Notification::create([
                        'student_id' => $sid,
                        'title'      => $notificationSetting->title,
                        'body'       => $notificationSetting->body,
                        'send_status'=> $result['status'],
                    ]);
                }
            }

            return Redirect::route($this->modules['route'] . '.index')
                ->withSuccess($this->modules['title'] . ' created and sent successfully');
        } catch (\Exception $e) {
            return Redirect::route('dashboard')->withErrors($e->getMessage());
        }
    }

    public function edit(string $id)
    {
        $modules = $this->modules;
        View::share('modules', $modules);

        try {
            $edit = NotificationSetting::findOrFail($id);
            $courses = MasterCourse::pluck('course_name', 'id');
            $selectedStudents = $edit->student_id ? explode(',', $edit->student_id) : [];

            $students = [];
            if ($edit->course_id) {
                $students = CourceRegistration::with('admission:id,first_name,last_name')
                    ->where('course_id', $edit->course_id)
                    ->get()
                    ->map(function ($reg) {
                        return [
                            'id' => $reg->admission->id ?? null,
                            'name' => ($reg->admission->first_name ?? '') . ' ' . ($reg->admission->last_name ?? ''),
                        ];
                    })
                    ->filter(fn($s) => $s['id'] != null)
                    ->values()
                    ->toArray();
            }

            View::share('edit', $edit);
            View::share('courses', $courses);
            View::share('students', $students);
            View::share('selectedStudents', $selectedStudents);

            return view($modules['folder_path'] . '.form');
        } catch (\Exception $e) {
            return Redirect::route('dashboard')->withErrors($e->getMessage());
        }
    }

    public function update(NotificationSettingRequest $request, string $id)
    {
        $validated = $request->validated();

        try {
            $notificationSetting = NotificationSetting::findOrFail($id);

            $studentIds = array_filter($validated['student_id'], fn($id) => $id !== 'all');
            $validated['student_id'] = implode(',', $studentIds);
            $validated['updated_by'] = Auth::user()->id;

            $notificationSetting->update($validated);

            return Redirect::route($this->modules['route'] . '.index')
                ->withSuccess($this->modules['title'] . ' updated successfully');
        } catch (\Exception $e) {
            return Redirect::route('dashboard')->withErrors($e->getMessage());
        }
    }

    public function destroy(string $id)
    {
        try {
            $notificationSetting = NotificationSetting::findOrFail($id);
            $notificationSetting->delete();

            return Redirect::route($this->modules['route'] . '.index')
                ->withSuccess($this->modules['title'] . ' deleted successfully');
        } catch (\Exception $e) {
            return Redirect::route('dashboard')->withErrors($e->getMessage());
        }
    }

    public function getStudentsByCourse($course_id)
    {
        try {
            $students = CourceRegistration::with('student', 'admission:id,first_name,last_name')
                ->where('course_id', $course_id)
                ->get()
                ->map(function ($reg) {
                    return [
                        'id' => $reg->admission->id ?? null,
                        'name' => ($reg->admission->first_name ?? '') . ' ' . ($reg->admission->last_name ?? ''),
                    ];
                })
                ->filter(fn($s) => $s['id'] != null)
                ->values();

            return response()->json($students);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
