<?php

namespace App\Http\Controllers\admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Admission;
use App\Models\FacultyComplaintReport;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\DataTables;

class FacultyComplaintReportController extends Controller
{
    public $modules = [];

    public function __construct()
    {
        $this->modules = [
            'title' => 'Faculty Complaint Report',
            'folder_path' => 'software.module.faculty-complaint-report',
            'route' => 'faculty-complaint-report',
            'table_name' => (new FacultyComplaintReport())->getTable(),
            'permission_prefix' => 'faculty-complaint-report',
            'public_folder' => public_path(),
        ];
    }

    public function index(Request $request)
    {
        $modules = $this->modules;
        View::share('modules', $modules);

        try {
            $data = [];
            if ($request->ajax()) {
                $data = FacultyComplaintReport::with(['admission', 'faculty'])->select('faculty_complaint_report.*');

                $editPermission = Auth::user()->hasDirectPermission($modules["permission_prefix"] . '-edit');
                $deletePermission = Auth::user()->hasDirectPermission($modules["permission_prefix"] . '-delete');

                $returnData = DataTables::of($data)
                    ->addIndexColumn()
                    ->filter(function ($query) use ($request) {
                        if ($request->has('search') && !empty($request->search['value'])) {
                            $searchValue = $request->search['value'];
                            $query->where(function ($q) use ($searchValue) {
                                $q->where('gr_no', 'like', "%{$searchValue}%")
                                  ->orWhere('complaint', 'like', "%{$searchValue}%")
                                  ->orWhereHas('admission', function ($sq) use ($searchValue) {
                                      $sq->where('first_name', 'like', "%{$searchValue}%")
                                         ->orWhere('last_name', 'like', "%{$searchValue}%")
                                         ->orWhere('gr_no', 'like', "%{$searchValue}%");
                                  })
                                  ->orWhereHas('faculty', function ($sq) use ($searchValue) {
                                      $sq->where('name', 'like', "%{$searchValue}%");
                                  });
                            });
                        }
                    })
                    ->editColumn('date', function ($record) {
                        return $record->date ? Helper::convert_date($record->date, 'Y-m-d', 'd-m-Y') : '-';
                    })
                    ->addColumn('student_name', function ($record) {
                        if ($record->admission) {
                            $first = $record->admission->first_name ?? '';
                            $last = $record->admission->last_name ?? '';
                            $father = $record->admission->father_name ?? '';
                            return trim($first . ' ' . $last . ' ' . $father);
                        }
                        return '-';
                    })
                    ->addColumn('faculty_name', function ($record) {
                        return $record->faculty ? $record->faculty->name : '-';
                    })
                    ->addColumn('action', function ($row) use ($modules, $editPermission, $deletePermission) {
                        $btn = '<div class="d-flex gap-2 justify-content-center">';
                        if ($editPermission) {
                            $btn .= '<div class="edit"><a href="' . route($modules["route"] . ".edit", [$row["id"]]) . '" class="btn btn-info btn-icon mr-2"><i class="bx bx-edit-alt"></i></a></div>';
                        }
                        if ($deletePermission) {
                            $btn .= '<div class="remove"><a href="javascript:void(0)" data-id="' . $row->id . '" data-did="' . route($modules["route"] . ".destroy", [$row["id"]]) . '" class="btn btn-danger btn-icon deletebutton"><i class="bx bx-trash"></i></a></div>';
                        }
                        $btn .= '</div>';
                        return $btn;
                    })
                    ->rawColumns(['action'])
                    ->make(true);

                return $returnData;
            }

            $columns = [
                (object)['data' => "gr_no", 'name' => 'gr_no', 'td_label' => 'GR No.'],
                (object)['data' => "student_name", 'name' => 'student_name', 'td_label' => 'Student Name', 'orderable' => false],
                (object)['data' => "date", 'name' => 'date', 'td_label' => 'Date'],
                (object)['data' => "faculty_name", 'name' => 'faculty_name', 'td_label' => 'Faculty', 'orderable' => false],
                (object)['data' => "complaint", 'name' => 'complaint', 'td_label' => 'Complaint'],
                (object)['data' => "action", 'name' => 'action', 'td_label' => 'Action', 'orderable' => false, 'searchable' => false, 'className' => 'w-10 text-center'],
            ];
            View::share("columns", $columns);

            return view($modules['folder_path'] . '.index', compact('data'));
        } catch (\Exception $e) {
            return Redirect::route('software.dashboard')->withErrors($e->getMessage());
        }
    }

    public function create()
    {
        $modules = $this->modules;
        View::share('modules', $modules);

        $students = Admission::where('status', 'active')
            ->select('id', 'gr_no', 'first_name', 'last_name', 'father_name')
            ->orderBy('id', 'asc')
            ->get();

        $faculties = User::where('status', 'active')
            ->whereHas('roles', function ($q) {
                $q->where(DB::raw('LOWER(name)'), 'like', '%faculty%');
            })->where('name', 'not like', '%Ocean%')->get();

        if ($faculties->isEmpty()) {
            $faculties = User::where('status', 'active')->where('name', 'not like', '%Ocean%')->get();
        }

        return view($modules['folder_path'] . '.form', compact('modules', 'students', 'faculties'));
    }

    public function store(Request $request)
    {
        $modules = $this->modules;
        View::share('modules', $modules);

        $request->validate([
            'admission_id' => 'required',
            'date' => 'required|date',
            'complaint' => 'required|string',
            'faculty_id' => 'required|exists:users,id',
        ]);

        DB::beginTransaction();
        try {
            $admission = Admission::find($request->admission_id);

            FacultyComplaintReport::create([
                'admission_id' => $request->admission_id,
                'gr_no' => $admission ? $admission->gr_no : $request->gr_no,
                'date' => $request->date,
                'complaint' => $request->complaint,
                'faculty_id' => $request->faculty_id,
            ]);

            DB::commit();

            return Redirect::route($modules['route'] . '.index')->withSuccess($modules['title'] . ' created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage())->withInput();
        }
    }

    public function edit(string $id)
    {
        $modules = $this->modules;
        View::share('modules', $modules);

        try {
            $edit = FacultyComplaintReport::findOrFail($id);

            $students = Admission::where('status', 'active')
                ->select('id', 'gr_no', 'first_name', 'last_name', 'father_name')
                ->orderBy('id', 'asc')
                ->get();

            $faculties = User::where('status', 'active')
                ->whereHas('roles', function ($q) {
                    $q->where(DB::raw('LOWER(name)'), 'like', '%faculty%');
                })->where('name', 'not like', '%Ocean%')->get();

            if ($faculties->isEmpty()) {
                $faculties = User::where('status', 'active')->where('name', 'not like', '%Ocean%')->get();
            }

            return view($modules['folder_path'] . '.form', compact('modules', 'edit', 'students', 'faculties'));
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage())->withInput();
        }
    }

    public function update(Request $request, string $id)
    {
        $modules = $this->modules;
        View::share('modules', $modules);

        $request->validate([
            'admission_id' => 'required',
            'date' => 'required|date',
            'complaint' => 'required|string',
            'faculty_id' => 'required|exists:users,id',
        ]);

        try {
            $report = FacultyComplaintReport::findOrFail($id);
            $admission = Admission::find($request->admission_id);

            $report->update([
                'admission_id' => $request->admission_id,
                'gr_no' => $admission ? $admission->gr_no : $request->gr_no,
                'date' => $request->date,
                'complaint' => $request->complaint,
                'faculty_id' => $request->faculty_id,
            ]);

            return Redirect::route($modules['route'] . '.index')->withSuccess($modules['title'] . ' updated successfully');
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage())->withInput();
        }
    }

    public function destroy(Request $request, string $id)
    {
        $modules = $this->modules;
        View::share('modules', $modules);

        try {
            $report = FacultyComplaintReport::findOrFail($id);
            if ($report) {
                if ($report->delete()) {
                    return response()->json(true);
                }
            }
            return response()->json(false);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
