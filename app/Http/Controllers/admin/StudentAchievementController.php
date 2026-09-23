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
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
            $hasFilter = !empty($request->gr_no) || !empty($request->event_id) || !empty($request->month) || !empty($request->year);

            if (!$hasFilter) {
                $query = StudentAchievement::whereRaw('1 = 0');
            } else {
                $query = StudentAchievement::with(['admission', 'event']);

                if ($request->filled('gr_no')) {
                    $term = trim($request->gr_no);
                    $query->where(function ($q) use ($term) {
                        $q->where('gr_no', 'like', '%' . $term . '%')
                            ->orWhereHas('admission', function ($qa) use ($term) {
                                $qa->where('gr_no', 'like', '%' . $term . '%')
                                    ->orWhereRaw("CONCAT_WS(' ', first_name, last_name, father_name) LIKE ?", ['%' . $term . '%'])
                                    ->orWhereRaw("CONCAT_WS(' ', first_name, father_name, last_name) LIKE ?", ['%' . $term . '%'])
                                    ->orWhere('first_name', 'like', '%' . $term . '%')
                                    ->orWhere('last_name', 'like', '%' . $term . '%')
                                    ->orWhere('father_name', 'like', '%' . $term . '%');
                            });
                    });
                }

                if ($request->filled('event_id')) {
                    $query->where('event_id', $request->event_id);
                }

                if ($request->filled('month')) {
                    $query->whereMonth('date', $request->month);
                }

                if ($request->filled('year')) {
                    $query->whereYear('date', $request->year);
                }

                $query->orderByDesc('id');
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('gr_no', function ($row) {
                    return $row->gr_no ?? ($row->admission->gr_no ?? '-');
                })
                ->addColumn('student_name', function ($row) {
                    if ($row->admission) {
                        $name = trim(($row->admission->first_name ?? '') . ' ' . ($row->admission->last_name ?? '') . ' ' . ($row->admission->father_name ?? ''));
                        return '<a href="javascript:void(0)" class="view-student-achievements text-primary fw-bold" data-id="' . $row->admission_id . '" data-name="' . $name . '">' . $name . '</a>';
                    }
                    return '-';
                })
                ->addColumn('event_name', function ($row) {
                    if ($row->event) {
                        $eventName = $row->event->name ?? '-';
                        return '<a href="javascript:void(0)" class="view-event-achievements text-primary fw-bold" data-id="' . $row->event_id . '" data-name="' . $eventName . '">' . $eventName . '</a>';
                    }
                    return '-';
                })
                ->addColumn('rank', function ($row) {
                    return $row->rank ?? '-';
                })
                ->addColumn('remark', function ($row) {
                    return $row->remark ?? '-';
                })
                ->editColumn('date', function ($row) {
                    return $row->date ? Carbon::parse($row->date)->format('d-m-Y') : '-';
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
                ->rawColumns(['student_name', 'event_name', 'action'])
                ->make(true);
        }

        $events = MasterEvent::where('status', 'active')->orderBy('name', 'asc')->get();
        $grNos = Admission::whereIn('status', ['active', 'created'])
            ->whereNotNull('gr_no')
            ->where('gr_no', '!=', '')
            ->orderBy('gr_no', 'asc')
            ->get(['gr_no', 'first_name', 'last_name', 'father_name']);

        $months = [
            1 => 'January',
            2 => 'February',
            3 => 'March',
            4 => 'April',
            5 => 'May',
            6 => 'June',
            7 => 'July',
            8 => 'August',
            9 => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December'
        ];
        $currentYear = (int)date('Y');
        $years = range($currentYear - 6, $currentYear);

        return view($modules['folder_path'] . '.index', compact('events', 'grNos', 'months', 'years'));
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
        $events = MasterEvent::where('status', 'active')->orderBy('id', 'asc')->get();

        $months = [
            1 => 'January',
            2 => 'February',
            3 => 'March',
            4 => 'April',
            5 => 'May',
            6 => 'June',
            7 => 'July',
            8 => 'August',
            9 => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December'
        ];
        $currentMonth = (int)date('n');
        $currentYear = (int)date('Y');
        $years = range($currentYear - 6, $currentYear);

        return view($modules['folder_path'] . '.form', compact('courses', 'events', 'months', 'years', 'currentMonth', 'currentYear'));
    }

    public function store(Request $request)
    {
        // 1. Check if bulk student_ids + events format is submitted
        if ($request->has('student_ids') && is_array($request->student_ids) && count($request->student_ids) > 0) {
            $studentIds = $request->input('student_ids', []);
            $eventsInput = $request->input('events', []); // array of event objects or event IDs
            $defaultDate = $request->input('date') ?: date('Y-m-d');

            if (empty($eventsInput)) {
                return redirect()->back()->withErrors('Please select at least one Event / Award.')->withInput();
            }

            try {
                $userId = Auth::id();
                DB::beginTransaction();

                $students = Admission::whereIn('id', $studentIds)->get()->keyBy('id');

                $savedCount = 0;
                foreach ($studentIds as $stuId) {
                    $student = $students->get($stuId);
                    if (!$student) continue;

                    foreach ($eventsInput as $evtKey => $evtData) {
                        // Check if event is selected
                        $eventId = is_array($evtData) ? ($evtData['event_id'] ?? null) : $evtData;
                        $isSelected = is_array($evtData) ? !empty($evtData['selected']) : true;

                        if (!$isSelected || !$eventId) {
                            continue;
                        }

                        $rank = is_array($evtData) && !empty($evtData['rank']) ? $evtData['rank'] : '1st';
                        $date = is_array($evtData) && !empty($evtData['date']) ? $evtData['date'] : $defaultDate;
                        $remark = is_array($evtData) && isset($evtData['remark']) && trim($evtData['remark']) !== '' ? trim($evtData['remark']) : null;

                        StudentAchievement::create([
                            'admission_id' => $student->id,
                            'gr_no' => $student->gr_no ?? '',
                            'event_id' => $eventId,
                            'rank' => $rank,
                            'date' => $date,
                            'remark' => $remark,
                            'created_by' => $userId,
                            'updated_by' => $userId,
                        ]);
                        $savedCount++;
                    }
                }

                DB::commit();

                if ($savedCount === 0) {
                    return redirect()->back()->withErrors('No events selected to assign.')->withInput();
                }

                return redirect()->route('achievement.index')->withSuccess("{$savedCount} Achievement record(s) created successfully.");
            } catch (\Exception $e) {
                DB::rollBack();
                return redirect()->back()->withErrors($e->getMessage())->withInput();
            }
        }

        // 2. Fallback: Check if achievements array is submitted
        if ($request->has('achievements') && is_array($request->achievements)) {
            try {
                $userId = Auth::id();
                DB::beginTransaction();
                foreach ($request->achievements as $ach) {
                    if (empty($ach['admission_id']) || empty($ach['event_id'])) {
                        continue;
                    }
                    $student = Admission::findOrFail($ach['admission_id']);
                    StudentAchievement::create([
                        'admission_id' => $ach['admission_id'],
                        'gr_no' => $student->gr_no ?? '',
                        'event_id' => $ach['event_id'],
                        'rank' => $ach['rank'] ?? '1st',
                        'date' => $ach['date'] ?? date('Y-m-d'),
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

        return redirect()->back()->withErrors('Please select at least one student and one event/award.')->withInput();
    }

    public function edit($id)
    {
        $modules = $this->modules;
        $modules['permission_edit'] = Helper::directCan($modules['permission_prefix'] . '-edit');
        if (!$modules['permission_edit']) {
            abort(403, 'User does not have the right permissions.');
        }

        View::share('modules', $modules);

        $edit = StudentAchievement::with(['admission', 'event'])->findOrFail($id);
        $courses = MasterCourse::where('status', 'active')->orderBy('course_name', 'asc')->get();
        $events = MasterEvent::where('status', 'active')->orderBy('id', 'asc')->get();

        $months = [
            1 => 'January',
            2 => 'February',
            3 => 'March',
            4 => 'April',
            5 => 'May',
            6 => 'June',
            7 => 'July',
            8 => 'August',
            9 => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December'
        ];
        $editDate = $edit->date ? Carbon::parse($edit->date) : Carbon::now();
        $selectedMonth = (int)$editDate->format('n');
        $selectedYear = (int)$editDate->format('Y');
        $currentYear = (int)date('Y');
        $years = range($currentYear - 6, $currentYear + 1);

        $student = $edit->admission;
        $preselectedStudent = null;
        if ($student) {
            $fullName = trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? '') . ' ' . ($student->father_name ?? ''));
            $preselectedStudent = [
                'id' => $student->id,
                'gr_no' => $student->gr_no ?? '-',
                'first_name' => $student->first_name ?? '',
                'last_name' => $student->last_name ?? '',
                'father_name' => $student->father_name ?? '',
                'full_name' => $fullName,
            ];
        }

        // Fetch all achievements of this student for this month and year
        $studentMonthAchievements = StudentAchievement::where('admission_id', $edit->admission_id)
            ->whereMonth('date', $selectedMonth)
            ->whereYear('date', $selectedYear)
            ->get();

        $existingEventsMap = [];
        foreach ($studentMonthAchievements as $item) {
            $existingEventsMap[$item->event_id] = [
                'id' => $item->id,
                'rank' => $item->rank ?? '1st',
                'remark' => $item->remark ?? '',
            ];
        }

        // Ensure the current edited event is in the map
        if (!isset($existingEventsMap[$edit->event_id])) {
            $existingEventsMap[$edit->event_id] = [
                'id' => $edit->id,
                'rank' => $edit->rank ?? '1st',
                'remark' => $edit->remark ?? '',
            ];
        }

        return view($modules['folder_path'] . '.form', compact(
            'edit',
            'courses',
            'events',
            'months',
            'years',
            'selectedMonth',
            'selectedYear',
            'preselectedStudent',
            'existingEventsMap'
        ));
    }

    public function update(StudentAchievementRequest $request, $id)
    {
        try {
            $ach = StudentAchievement::findOrFail($id);
            $userId = Auth::id();

            // 1. If form submitted via unified student_ids & events format
            if ($request->has('student_ids') && is_array($request->student_ids) && count($request->student_ids) > 0) {
                $studentIds = $request->input('student_ids', []);
                $eventsInput = $request->input('events', []);
                $defaultDate = $request->input('date') ?: ($ach->date ? Carbon::parse($ach->date)->format('Y-m-d') : date('Y-m-d'));

                $selectedEvents = [];
                foreach ($eventsInput as $evtKey => $evtData) {
                    $isSelected = is_array($evtData) ? !empty($evtData['selected']) : true;
                    $eventId = is_array($evtData) ? ($evtData['event_id'] ?? null) : $evtData;
                    if ($isSelected && $eventId) {
                        $selectedEvents[$eventId] = [
                            'event_id' => $eventId,
                            'rank' => is_array($evtData) && !empty($evtData['rank']) ? $evtData['rank'] : '1st',
                            'date' => is_array($evtData) && !empty($evtData['date']) ? $evtData['date'] : $defaultDate,
                            'remark' => is_array($evtData) && isset($evtData['remark']) && trim($evtData['remark']) !== '' ? trim($evtData['remark']) : null,
                        ];
                    }
                }

                if (empty($selectedEvents)) {
                    return redirect()->back()->withErrors('Please select at least one Event / Award.')->withInput();
                }

                DB::beginTransaction();

                $primaryStudentId = $studentIds[0];
                $primaryStudent = Admission::findOrFail($primaryStudentId);
                $originalMonth = $ach->date ? Carbon::parse($ach->date)->format('n') : Carbon::now()->format('n');
                $originalYear = $ach->date ? Carbon::parse($ach->date)->format('Y') : Carbon::now()->format('Y');

                // Find all existing achievements of this primary student for this original month & year
                $existingAchievements = StudentAchievement::where('admission_id', $ach->admission_id)
                    ->whereMonth('date', $originalMonth)
                    ->whereYear('date', $originalYear)
                    ->get()
                    ->keyBy('event_id');

                $processedEventIds = [];

                foreach ($selectedEvents as $eventId => $evt) {
                    if ($existingAchievements->has($eventId)) {
                        // Update existing record
                        $existingRecord = $existingAchievements->get($eventId);
                        $existingRecord->update([
                            'admission_id' => $primaryStudent->id,
                            'gr_no' => $primaryStudent->gr_no ?? '',
                            'rank' => $evt['rank'],
                            'date' => $evt['date'],
                            'remark' => $evt['remark'],
                            'updated_by' => $userId,
                        ]);
                        $processedEventIds[] = $eventId;
                    } else {
                        // Create new record for this event
                        StudentAchievement::create([
                            'admission_id' => $primaryStudent->id,
                            'gr_no' => $primaryStudent->gr_no ?? '',
                            'event_id' => $evt['event_id'],
                            'rank' => $evt['rank'],
                            'date' => $evt['date'],
                            'remark' => $evt['remark'],
                            'created_by' => $userId,
                            'updated_by' => $userId,
                        ]);
                        $processedEventIds[] = $eventId;
                    }
                }

                // Delete any existing record that was unchecked by user for this student & month/year
                foreach ($existingAchievements as $eventId => $oldRecord) {
                    if (!in_array($eventId, $processedEventIds)) {
                        $oldRecord->delete();
                    }
                }

                // If additional students were selected in edit, create achievements for them
                if (count($studentIds) > 1) {
                    for ($i = 1; $i < count($studentIds); $i++) {
                        $extraStudent = Admission::find($studentIds[$i]);
                        if (!$extraStudent) continue;

                        foreach ($selectedEvents as $evt) {
                            StudentAchievement::create([
                                'admission_id' => $extraStudent->id,
                                'gr_no' => $extraStudent->gr_no ?? '',
                                'event_id' => $evt['event_id'],
                                'rank' => $evt['rank'],
                                'date' => $evt['date'],
                                'remark' => $evt['remark'],
                                'created_by' => $userId,
                                'updated_by' => $userId,
                            ]);
                        }
                    }
                }

                DB::commit();
                return redirect()->route('achievement.index')->withSuccess('Achievements updated successfully.');
            }

            // 2. Direct fallback
            if ($request->filled('admission_id') && $request->filled('event_id')) {
                $student = Admission::findOrFail($request->admission_id);
                $ach->update([
                    'admission_id' => $student->id,
                    'gr_no' => $student->gr_no ?? '',
                    'event_id' => $request->event_id,
                    'rank' => $request->rank ?? '1st',
                    'date' => $request->date ?? date('Y-m-d'),
                    'remark' => $request->remark ?? '',
                    'updated_by' => $userId,
                ]);

                return redirect()->route('achievement.index')->withSuccess('Achievement updated successfully.');
            }

            return redirect()->back()->withErrors('Please select at least one student and one event/award.')->withInput();
        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
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
        $courseIds = $request->input('course_id');
        $search = $request->input('search');

        if (is_string($courseIds)) {
            $courseIds = explode(',', $courseIds);
        }
        $courseIds = array_filter((array)$courseIds);

        // Subquery to get latest course registration per student
        $sub = CourceRegistration::select(DB::raw('MAX(id) as id'))
            ->whereNull('deleted_at')
            ->whereNotIn('status', ['Cancel', 'cancel'])
            ->groupBy('register_id');

        $regQuery = CourceRegistration::whereIn('id', $sub)
            ->with(['admission', 'course', 'batch', 'class']);

        if (!empty($courseIds)) {
            $regQuery->whereIn('course_id', $courseIds);
        }

        $registrations = $regQuery->get();

        $students = $registrations->map(function ($reg) {
            $adm = $reg->admission;
            if (!$adm || !in_array($adm->status, ['active', 'created'])) {
                return null;
            }
            $fullName = trim(($adm->first_name ?? '') . ' ' . ($adm->last_name ?? '') . ' ' . ($adm->father_name ?? ''));
            return [
                'id' => $adm->id,
                'gr_no' => $adm->gr_no ?? '-',
                'first_name' => $adm->first_name ?? '',
                'last_name' => $adm->last_name ?? '',
                'father_name' => $adm->father_name ?? '',
                'full_name' => $fullName,
                'course_id' => $reg->course_id,
                'course_name' => $reg->course->course_name ?? '-',
                'batch_name' => $reg->batch->batch_name ?? '-',
                'class_name' => $reg->class->class ?? '-',
            ];
        })->filter()->values();

        // Apply search if provided
        if (!empty($search)) {
            $searchLower = strtolower($search);
            $students = $students->filter(function ($item) use ($searchLower) {
                return str_contains(strtolower($item['full_name']), $searchLower) ||
                    str_contains(strtolower((string)$item['gr_no']), $searchLower) ||
                    str_contains(strtolower($item['course_name']), $searchLower);
            })->values();
        }

        // Sort by GR No
        $students = $students->sortBy('gr_no')->values();

        return response()->json([
            'success' => true,
            'data' => $students,
            'total' => $students->count()
        ]);
    }

    public function studentData($id)
    {
        $data = StudentAchievement::where('admission_id', $id)
            ->with(['event', 'admission'])
            ->orderByDesc('date')
            ->get();

        $userIds = $data->pluck('created_by')->filter()->unique();
        $users = \App\Models\User::whereIn('id', $userIds)->pluck('name', 'id');

        $result = $data->map(function ($row) use ($users) {
            return [
                'event' => $row->event->name ?? 'N/A',
                'rank' => $row->rank ?? '-',
                'remark' => $row->remark ?? '-',
                'date' => $row->date ? Carbon::parse($row->date)->format('d-m-Y') : '-',
                'by_user' => $users[$row->created_by] ?? 'N/A'
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    }

    public function eventData($id)
    {
        $data = StudentAchievement::where('event_id', $id)
            ->with(['admission'])
            ->orderByDesc('date')
            ->get();

        $userIds = $data->pluck('created_by')->filter()->unique();
        $users = \App\Models\User::whereIn('id', $userIds)->pluck('name', 'id');

        $result = $data->map(function ($row) use ($users) {
            $student_name = '';
            if ($row->admission) {
                $student_name = trim(($row->admission->first_name ?? '') . ' ' . ($row->admission->last_name ?? '') . ' ' . ($row->admission->father_name ?? ''));
            }
            return [
                'student_name' => $student_name ?: 'N/A',
                'gr_no' => $row->admission->gr_no ?? $row->gr_no ?? '-',
                'rank' => $row->rank ?? '-',
                'remark' => $row->remark ?? '-',
                'date' => $row->date ? Carbon::parse($row->date)->format('d-m-Y') : '-',
                'by_user' => $users[$row->created_by] ?? 'N/A'
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    }
}
