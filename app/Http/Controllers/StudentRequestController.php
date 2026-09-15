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
            (object)['data' => "subject", 'name' => 'subject', 'td_label' => 'Subject'],
            (object)['data' => "detail", 'name' => 'detail', 'td_label' => 'Detail'],
            (object)['data' => "response", 'name' => 'response', 'td_label' => 'Admin Response'],
            (object)['data' => "status", 'name' => 'status', 'td_label' => 'Status'],
            (object)['data' => "created_at", 'name' => 'requests.created_at', 'td_label' => 'Created At'],
            (object)['data' => "action", 'name' => 'action', 'td_label' => 'Action', 'orderable' => false, 'searchable' => false],
        ];

        View::share("columns", $columns);

        try {
            if ($request->ajax()) {
                $data = StudentRequest::select('requests.*')->with(['subjectRelation', 'student'])
                    ->orderBy('requests.id', 'desc');

                return Datatables::of($data)
                    ->addIndexColumn()
                    ->filter(function ($query) use ($request) {
                        if ($request->has('search') && $request->search['value'] != '') {
                            $search = $request->search['value'];
                            $query->whereHas('student', function ($q) use ($search) {
                                    $q->where('first_name', 'like', "%$search%")
                                      ->orWhere('last_name', 'like', "%$search%");
                                })
                                ->orWhere('subject', 'like', "%$search%")
                                ->orWhere('detail', 'like', "%$search%")
                                ->orWhere('response', 'like', "%$search%")
                                ->orWhere('status', 'like', "%$search%");
                        }
                    })

                    ->addColumn('student_id', function ($row) {
                        $first = $row->student?->first_name;
                        $last  = $row->student?->last_name;

                        return $first ? "$first $last" : '---';
                    })

                    ->addColumn('subject', function ($row) {
                        $subjectVal = $row->getRawOriginal('subject');
                        if (!$subjectVal && $row->subjectRelation) {
                            $subjectVal = $row->subjectRelation->name;
                        }
                        return $subjectVal ? e($subjectVal) : '-';
                    })

                    ->editColumn('response', function ($row) {
                        return $row->response ? e($row->response) : '<span class="text-muted">---</span>';
                    })

                    ->editColumn('status', function ($row) {
                        if (($row->status ?? 'Pending') == 'Completed') {
                            return '<span class="badge bg-success font-size-12 px-2 py-1">Completed</span>';
                        }
                        return '<span class="badge bg-warning font-size-12 px-2 py-1">Pending</span>';
                    })

                    ->editColumn('created_at', function ($row) {
                        return $row->created_at ? $row->created_at->format('d-m-Y H:i') : '-';
                    })

                    ->addColumn('action', function ($row) {
                        $currentStatus = $row->status ?? 'Pending';
                        if ($currentStatus == 'Completed') {
                            return '<button type="button" class="btn btn-sm btn-outline-success" disabled><i class="ri-check-line me-1"></i> Completed</button>';
                        } else {
                            $studentName = e(($row->student?->first_name ?? '') . ' ' . ($row->student?->last_name ?? ''));
                            $subjectVal = $row->getRawOriginal('subject');
                            if (!$subjectVal && $row->subjectRelation) {
                                $subjectVal = $row->subjectRelation->name;
                            }
                            $subjectVal = e($subjectVal ?? '-');
                            $detail = e($row->detail ?? '');
                            $response = e($row->response ?? '');

                            return '<button type="button" class="btn btn-sm btn-success respond-btn" data-id="' . $row->id . '" data-student="' . $studentName . '" data-subject="' . $subjectVal . '" data-detail="' . $detail . '" data-response="' . $response . '"><i class="ri-check-line me-1"></i> Mark Completed</button>';
                        }
                    })

                    ->rawColumns(['response', 'status', 'action'])
                    ->make(true);
            }

            return view($modules['folder_path'] . '.index');
        } catch (\Exception $e) {
            return Redirect::route('dashboard')->withErrors($e->getMessage());
        }
    }

    /**
     * Submit admin response and update request status to Completed.
     */
    public function respond(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:requests,id',
            'response' => 'required|string|max:2000',
        ]);

        try {
            $studentRequest = StudentRequest::findOrFail($request->id);

            if ($studentRequest->status === 'Completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'This request is already marked as Completed.'
                ], 422);
            }

            $studentRequest->response = $request->response;
            $studentRequest->status = 'Completed';
            $studentRequest->updated_by = Auth::id();
            $studentRequest->save();

            return response()->json([
                'success' => true,
                'message' => 'Response submitted and status updated to Completed successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
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
