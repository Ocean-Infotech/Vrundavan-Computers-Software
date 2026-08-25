<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use App\Http\Controllers\Controller;
use App\Models\NotificationSetting;
use App\Models\Master\MasterCourse;
use App\Models\StudentRequest;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class StudentRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $modules = [
            'title' => 'Student Requests',
            'folder_path' => 'software.module.student_request',
            'route' => 'student-requests',
            'table_name' => (new StudentRequest())->getTable(),
            'permission_prefix' => 'student-requests',
        ];

        $modules['login_user_role'] = Helper::getLoginUserRole();
        $modules['permission_list'] = Helper::directCan($modules['permission_prefix'] . '-list');
        $modules['permission_add'] = Helper::directCan($modules['permission_prefix'] . '-create');
        $modules['permission_edit'] = Helper::directCan($modules['permission_prefix'] . '-edit');
        $modules['permission_delete'] = Helper::directCan($modules['permission_prefix'] . '-delete');

        // Permission check
        if (!$modules['permission_list']) {
            if ($request->ajax()) {
                return response()->json(['error' => 'User does not have the right permissions.'], 403);
            }
            abort(403, 'User does not have the right permissions.');
        }

        View::share('modules', $modules);

        $columns = [
            (object)['data' => "student_id", 'name' => 'student_id', 'td_label' => 'Student Name'],
            (object)['data' => "subject", 'name' => 'subject.name', 'td_label' => 'Subject'],
            (object)['data' => "detail", 'name' => 'detail', 'td_label' => 'Detail'],
            (object)['data' => "created_at", 'name' => 'requests.created_at', 'td_label' => 'Created At'],
        ];

        View::share("columns", $columns);

        try {
            if ($request->ajax()) {
                $data = StudentRequest::select('requests.*')->with('subject')
                    ->orderBy('requests.id', 'desc');

                return Datatables::of($data)
                    ->addIndexColumn()
                    ->filter(function ($query) use ($request) {
                        if ($request->has('search') && $request->search['value'] != '') {
                            $search = $request->search['value'];
                            $query->whereHas('subject', function ($q) use ($search) {
                                $q->where('name', 'like', "%$search%");
                            })
                                ->orWhere('detail', 'like', "%$search%");
                        }
                    })

                    ->addColumn('student_id', function ($row) {
                        $first = $row->student?->first_name;
                        $last  = $row->student?->last_name;

                        return $first ? "$first $last" : '---';
                    })

                    ->editColumn('created_at', function ($row) {
                        return $row->created_at ? $row->created_at->format('d-m-Y H:i') : '-';
                    })



                    ->addColumn('subject', function ($row) {
                        return $row->subject ? $row->subject->name : '-';
                    })

                    ->rawColumns(['action'])
                    ->make(true);
            }

            return view($modules['folder_path'] . '.index');
        } catch (\Exception $e) {
            return Redirect::route('dashboard')->withErrors($e->getMessage());
        }
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
