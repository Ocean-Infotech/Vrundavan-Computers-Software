<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admission;
use App\Models\StudentMarksheetIssue;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\Facades\DataTables;

class MarksheetIssueController extends Controller
{
    public $modules = [];

    public function __construct()
    {
        $this->modules = [
            'title' => 'Marksheet Issue',
            'folder_path' => 'software.module.marksheet-issue',
            'route' => 'marksheet-issue',
            'table_name' => (new Admission())->getTable(),
            'permission_prefix' => 'admission',
        ];
    }

    public function index(Request $request)
    {
        $modules = $this->modules;
        $modules['permission_add'] = false;
        $modules['permission_edit'] = false;
        $modules['permission_delete'] = false;
        $modules['permission_view'] = false;
        $modules['permission_status'] = false;

        $page_title = 'Marksheet Issue';
        $route = $modules['route'];
        $folder_path = $modules['folder_path'];

        $columns = [
            (object) ['data' => 'admission_name', 'name' => 'first_name', 'td_label' => 'Name'],
            (object) ['data' => "marksheet_semesters", 'name' => 'marksheet_semesters', 'td_label' => 'Issued Marksheets', 'orderable' => false, 'searchable' => false],
        ];

        View::share('modules', $modules);
        View::share('columns', $columns);

        if ($request->ajax()) {
            // If no filter is applied at all, return empty result
            $hasFilter = $request->filled('course_id')
                || $request->filled('batch_id')
                || $request->filled('class_id')
                || $request->filled('semester_id');

            if (!$hasFilter) {
                return Datatables::of(Admission::query()->whereRaw('0=1'))
                    ->addIndexColumn()
                    ->addColumn('marksheet_semesters', fn() => '')
                    ->rawColumns(['marksheet_semesters'])
                    ->make(true);
            }

            $data = Admission::withTrashed();

            return Datatables::of($data)
                ->addIndexColumn()
                ->filter(function ($query) use ($request) {
                    $searchValue = '';
                    if ($request->has('search')) {
                        $search = $request->input('search');
                        $searchValue = is_array($search) ? ($search['value'] ?? '') : $search;
                    }

                    if (!empty($searchValue)) {
                        $query->where(function($q) use ($searchValue) {
                            $q->where('first_name', 'like', "%" . $searchValue . "%")
                              ->orWhere('last_name', 'like', "%" . $searchValue . "%")
                              ->orWhere('father_name', 'like', "%" . $searchValue . "%");
                        });
                    }

                    // Filters: Course, Batch, Class, Semester
                    if ($request->filled('course_id')) {
                        $query->whereHas('courses', function ($q) use ($request) {
                            $q->where('course_id', $request->course_id);
                        });
                    }
                    if ($request->filled('batch_id')) {
                        $query->whereHas('courses', function ($q) use ($request) {
                            $q->where('batch_id', $request->batch_id);
                        });
                    }
                    if ($request->filled('class_id')) {
                        $query->whereHas('courses', function ($q) use ($request) {
                            $q->where('class_id', $request->class_id);
                        });
                    }
                    if ($request->filled('semester_id')) {
                        $query->whereHas('feesCollections', function ($q) use ($request) {
                            $q->where('year_semester', $request->semester_id);
                        });
                    }
                })
                ->editColumn('admission_name', function ($row) {
                    $first  = $row->first_name ?? '';
                    $last   = $row->last_name ?? '';
                    $father = $row->father_name ?? '';
                    return trim(preg_replace('/\s+/', ' ', $first . ' ' . $last . ' ' . $father));
                })
                ->addColumn('marksheet_semesters', function ($row) use ($request) {
                    $html = '<div class="d-flex flex-wrap">';
                    
                    $activeSemester = $request->get('semester_id');
                    $issuedSemesters = $row->marksheetIssues->pluck('semester')->toArray();

                    // Get total semesters for the current student's course
                    $totalSemesters = 0;
                    $courseRegistration = $row->courses->first();
                    if ($courseRegistration && $courseRegistration->course) {
                        $totalSemesters = (int) $courseRegistration->course->semester;
                    }

                    if (!empty($activeSemester)) {
                        // Show only the selected semester checkbox
                        $checked = in_array($activeSemester, $issuedSemesters) ? 'checked' : '';
                        $html .= '<div class="form-check me-3">
                                    <input type="checkbox" class="form-check-input marksheet-toggle-checkbox" 
                                           data-admission-id="' . $row->id . '" 
                                           data-semester="' . $activeSemester . '" 
                                           ' . $checked . ' 
                                           id="marksheet_' . $row->id . '_' . $activeSemester . '">
                                    <label class="form-check-label" for="marksheet_' . $row->id . '_' . $activeSemester . '">Sem ' . $activeSemester . '</label>
                                  </div>';
                    } else {
                        // Show only the actual number of semesters for this course
                        for ($sem = 1; $sem <= $totalSemesters; $sem++) {
                            $checked = in_array($sem, $issuedSemesters) ? 'checked' : '';
                            $html .= '<div class="form-check me-3">
                                        <input type="checkbox" class="form-check-input marksheet-toggle-checkbox" 
                                               data-admission-id="' . $row->id . '" 
                                               data-semester="' . $sem . '" 
                                               ' . $checked . ' 
                                               id="marksheet_' . $row->id . '_' . $sem . '">
                                        <label class="form-check-label" for="marksheet_' . $row->id . '_' . $sem . '">Sem ' . $sem . '</label>
                                      </div>';
                        }
                    }
                    $html .= '</div>';
                    return $html;
                })
                ->rawColumns(['marksheet_semesters'])
                ->make(true);
        }

        return view($folder_path . '.index', compact('page_title', 'route', 'folder_path'));
    }

    public function toggle(Request $request)
    {
        $admissionId = $request->input('admission_id');
        $semester = $request->input('semester');
        $isIssued = $request->input('is_issued');

        if ($isIssued) {
            StudentMarksheetIssue::firstOrCreate([
                'admission_id' => $admissionId,
                'semester'     => $semester,
            ], [
                'is_issued'    => true,
                'created_by'   => \Illuminate\Support\Facades\Auth::user()->id ?? null,
            ]);
        } else {
            StudentMarksheetIssue::where('admission_id', $admissionId)
                ->where('semester', $semester)
                ->delete();
        }

        return response()->json(['success' => true]);
    }
}
