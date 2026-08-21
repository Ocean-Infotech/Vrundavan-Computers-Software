<?php

namespace App\Http\Controllers\admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Admission;
use App\Models\CourceRegistration;
use App\Models\Master\MasterBatch;
use App\Models\Master\MasterClass;
use App\Models\Master\MasterCourse;
use App\Models\Master\MasterEvent;
use App\Models\StudentAchievement;
use App\Http\Requests\StudentAchievementRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\Facades\DataTables;

class StudentAchievementController extends Controller
{
    public $modules = [];

    public function __construct()
    {
        $this->modules = [
            'title' => 'Achievement',
            'folder_path' => 'software.module.achievement',
            'route' => 'achievement',
            'permission_prefix' => 'achievement',
        ];
    }

    public function index(Request $request)
    {
        $modules = $this->modules;
        $modules['permission_list'] = Helper::directCan($modules['permission_prefix'] . '-list');
        $modules['permission_add'] = Helper::directCan($modules['permission_prefix'] . '-create');
        $modules['permission_edit'] = Helper::directCan($modules['permission_prefix'] . '-edit');
        $modules['permission_delete'] = Helper::directCan($modules['permission_prefix'] . '-delete');

        if (!$modules['permission_list']) {
            abort(403, 'User does not have the right permissions.');
        }

        View::share('modules', $modules);

        if ($request->ajax()) {
            $query = StudentAchievement::with(['admission', 'event'])->orderByDesc('id');

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('student_name', function ($row) {
                    if ($row->admission) {
                        return trim(($row->admission->first_name ?? '') . ' ' . ($row->admission->last_name ?? '') . ' ' . ($row->admission->father_name ?? ''));
                    }
                    return '-';
                })
                ->addColumn('event_name', function ($row) {
                    return $row->event->name ?? '-';
                })
                ->editColumn('date', function ($row) {
                    return $row->date ? \Carbon\Carbon::parse($row->date)->format('d-m-Y') : '-';
                })
                ->addColumn('action', function ($row) use ($modules) {
                    $btn = '<div class="d-flex gap-2 justify-content-center">';
                    if ($modules['permission_edit']) {
                        $btn .= '<div class="edit"><a href="' . route($modules['route'] . '.edit', [$row->id]) . '" class="btn btn-info btn-icon mr-2"><i class="bx bx-edit-alt"></i></a></div>';
                    }
                    if ($modules['permission_delete']) {
                        $btn .= '<div class="remove"><a href="javascript:void(0)" data-id="' . $row->id . '" data-did="' . route($modules['route'] . '.destroy', [$row->id]) . '" class="btn btn-danger btn-icon deletebutton"><i class="bx bx-trash"></i></a></div>';
                    }
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view($modules['folder_path'] . '.index');
    }

    public function create()
    {
        $modules = $this->modules;
        $modules['permission_add'] = Helper::directCan($modules['permission_prefix'] . '-create');
        if (!$modules['permission_add']) {
            abort(403, 'User does not have the right permissions.');
        }

        View::share('modules', $modules);

        $courses = MasterCourse::where('status', 'active')->orderBy('course_name', 'asc')->get();
        $batches = MasterBatch::where('status', 'active')->orderBy('batch_name', 'asc')->get();
        $classes = MasterClass::where('status', 'active')->orderBy('class', 'asc')->get();
        $events = MasterEvent::where('status', 'active')->orderBy('name', 'asc')->get();

        return view($modules['folder_path'] . '.form', compact('courses', 'batches', 'classes', 'events'));
    }

    public function store(Request $request)
    {
        if ($request->has('achievements') && is_array($request->achievements)) {
            try {
                $userId = Auth::id();
                DB::beginTransaction();
                foreach ($request->achievements as $ach) {
                    $student = Admission::findOrFail($ach['admission_id']);
                    StudentAchievement::create([
                        'admission_id' => $ach['admission_id'],
                        'gr_no' => $student->gr_no ?? '',
                        'event_id' => $ach['event_id'],
                        'rank' => $ach['rank'],
                        'date' => $ach['date'],
                        'remark' => $ach['remark'] ?? '',
                        'created_by' => $userId,
                        'updated_by' => $userId,
                    ]);
                }
                DB::commit();

                return redirect()->route('achievement.index')->withSuccess('Achievements saved successfully.');
            } catch (\Exception $e) {
                DB::rollBack();
                return redirect()->back()->withErrors($e->getMessage())->withInput();
            }
        }

        return redirect()->route('achievement.index')->withErrors('No achievements data submitted.');
    }

    public function edit($id)
    {
        $modules = $this->modules;
        $modules['permission_edit'] = Helper::directCan($modules['permission_prefix'] . '-edit');
        if (!$modules['permission_edit']) {
            abort(403, 'User does not have the right permissions.');
        }

        View::share('modules', $modules);

        $edit = StudentAchievement::findOrFail($id);
        $courses = MasterCourse::where('status', 'active')->orderBy('course_name', 'asc')->get();
        $batches = MasterBatch::where('status', 'active')->orderBy('batch_name', 'asc')->get();
        $classes = MasterClass::where('status', 'active')->orderBy('class', 'asc')->get();
        $events = MasterEvent::where('status', 'active')->orderBy('name', 'asc')->get();
        $students = Admission::whereIn('status', ['active', 'created'])->orderBy('first_name', 'asc')->get();

        return view($modules['folder_path'] . '.form', compact('edit', 'courses', 'batches', 'classes', 'events', 'students'));
    }

    public function update(StudentAchievementRequest $request, $id)
    {
        $validated = $request->validated();

        try {
            $ach = StudentAchievement::findOrFail($id);
            $student = Admission::findOrFail($validated['admission_id']);
            $validated['gr_no'] = $student->gr_no ?? '';
            $validated['updated_by'] = Auth::id();
            $ach->update($validated);

            return redirect()->route('achievement.index')->withSuccess('Achievement updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage())->withInput();
        }
    }

    public function destroy(Request $request, $id)
    {
        $modules = $this->modules;
        $modules['permission_delete'] = Helper::directCan($modules['permission_prefix'] . '-delete');
        if (!$modules['permission_delete']) {
            abort(403, 'User does not have the right permissions.');
        }

        try {
            $ach = StudentAchievement::findOrFail($id);
            $ach->delete();
            return redirect()->route('achievement.index')->withSuccess('Achievement deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->route('achievement.index')->withErrors($e->getMessage());
        }
    }

    public function getStudents(Request $request)
    {
        $course_id = $request->input('course_id');
        $batch_id = $request->input('batch_id');
        $class_id = $request->input('class_id');

        $query = CourceRegistration::query();

        if ($course_id) {
            $query->where('course_id', $course_id);
        }
        if ($batch_id) {
            $query->where('batch_id', $batch_id);
        }
        if ($class_id) {
            if (is_array($class_id)) {
                $query->whereIn('class_id', $class_id);
            } else {
                $query->where('class_id', $class_id);
            }
        }

        $studentIds = $query->pluck('register_id');

        $students = Admission::whereIn('id', $studentIds)
            ->whereIn('status', ['active', 'created'])
            ->select('id', 'gr_no', 'first_name', 'last_name', 'father_name')
            ->orderBy('id', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $students
        ]);
    }
}
