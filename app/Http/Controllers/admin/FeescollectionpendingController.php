<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\CourceRegistration;
use App\Models\FeesCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class FeescollectionpendingController extends Controller
{
    public $modules = [];
    public function __construct()
    {
        $this->modules = [
            'title' => 'Fees Pedding',
            'folder_path' => 'software.module.fees-collection-pending',
            'route' => 'fees-collection-pending',
            'table_name' => (new FeesCollection())->getTable(),
            'permisstion_prefix' => 'fees-collection-pending',
        ];

        // $this->middleware('permission:'.$this->modules['permisstion_prefix'].'-list', ['only' => ['index','show']]);
        // $this->middleware('permission:'.$this->modules['permisstion_prefix'].'-create', ['only' => ['create','store']]);
        // $this->middleware('permission:'.$this->modules['permisstion_prefix'].'-edit', ['only' => ['edit','update']]);
        // $this->middleware('permission:'.$this->modules['permisstion_prefix'].'-delete', ['only' => ['destroy']]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $modules = $this->modules;
        View::share('modules', $modules);

        try {
            // Define DataTable columns
            $columns = [
                (object)['data' => 'register_id', 'name' => 'register_id', 'td_label' => 'Student ID'],
                (object)['data' => 'student_name', 'name' => 'admission_id', 'td_label' => 'Student Name'],
                (object)['data' => 'contact_no', 'name' => 'contact_no', 'td_label' => 'Contact No'],
                (object)['data' => 'course.course_name', 'name' => 'course_id', 'td_label' => 'Course Name'],
                (object)['data' => 'fee', 'name' => 'fee', 'td_label' => 'Course Fees'],
                (object)['data' => 'date', 'name' => 'date', 'td_label' => 'DOJ'],
                (object)['data' => 'total_fee', 'name' => 'total_fee', 'td_label' => 'Total Fees'],
                (object)['data' => 'paid_fee', 'name' => 'paid_fee', 'td_label' => 'Paid Fees'],
                (object)['data' => 'pending_fee', 'name' => 'pending_fee', 'td_label' => 'Pending Fees'],
            ];
            View::share("columns", $columns);

            if ($request->ajax()) {
                $data = CourceRegistration::with(['course', 'admission', 'batch', 'class', 'shift'])
                    ->withTrashed()
                    ->orderBy('created_at', 'desc');

                return Datatables::of($data)
                    ->addIndexColumn()

                    // Combine student name
                    ->editColumn('student_name', function ($row) {
                        $first = $row->admission->first_name ?? '';
                        $father = $row->admission->father_name ?? '';
                        $last = $row->admission->last_name ?? '';
                        return trim("{$first} {$father} {$last}");
                    })

                    // Contact number
                    ->addColumn('contact_no', function ($row) {
                        return $row->admission->mobile_no ?? '-';
                    })

                    // Course fee
                    ->editColumn('fee', function ($row) {
                        return '₹' . number_format($row->fee, 2);
                    })

                    // Format DOJ
                    ->editColumn('date', function ($row) {
                        return Carbon::parse($row->date)->format('d-m-Y');
                    })

                    // Total Fee
                    ->addColumn('total_fee', function ($row) {
                        return '₹' . number_format($row->total_fee ?? $row->fee, 2);
                    })

                    // Paid Fee
                    ->addColumn('paid_fee', function ($row) {
                        return '₹' . number_format($row->paid_fee ?? 0, 2);
                })

                    // Pending Fee
                    ->addColumn('pending_fee', function ($row) {
                        $total = $row->total_fee ?? $row->fee;
                        $paid = $row->paid_fee ?? 0;
                        return '₹' . number_format($total - $paid, 2);
                    })

                    // Action buttons
                    ->addColumn('action', function ($row) use ($modules) {
                        $editPermisstion = false;
                        $deletePermisstion = false;

                        $btn = '';

                    if (!$row?->deleted_at) {
                            if ($editPermisstion) {
                                $btn .= '<a href="' . route($modules["route"] . ".edit", [$row["id"]]) . '" class="btn btn-light btn-icon mx-1"><i class="fa-solid fa-pen-to-square"></i></a>';
                            }
                            if ($deletePermisstion) {
                            $btn .= '<a href="javascript:void(0)" data-id="' . $row->id . '" data-did="' . route($modules["route"] . ".destroy", [$row["id"]]) . '" class="btn btn-danger btn-icon deletebutton mx-1"><i class="fa-solid fa-trash"></i></a>';
                            }
                        } else {
                        $btn .= '<a href="javascript:void(0)" data-restore="' . route($modules["route"] . ".restore", ['id' => $row["id"]]) . '" class="btn btn-light mx-1 record-restore" data-bs-toggle="tooltip" title="Restore Data"><i class="ti ti-history"></i> Restore</a>';
                        }

                    return $btn ?: '-';
                    })

                    ->rawColumns(['action'])
                    ->make(true);
            }

            return view($modules['folder_path'] . '.index');
        } catch (\Exception $e) {
            return Redirect::route('software.dashboard')->withErrors($e->getMessage());
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
