<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\CourceRegistration;
use App\Models\FeesCollection;
use App\Models\Master\MasterCourse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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
                (object)['data' => 'student_name', 'name' => 'register_id', 'td_label' => 'Student Name'],
                (object)['data' => 'contact_no', 'name' => 'contact_no', 'td_label' => 'Contact No'],
                (object)['data' => 'course_name', 'name' => 'course_id', 'td_label' => 'Course Name'],
                // (object)['data' => 'fee', 'name' => 'fee', 'td_label' => 'Course Fees'],
                (object)['data' => 'date', 'name' => 'date', 'td_label' => 'DOJ'],
                (object)['data' => 'total_fee', 'name' => 'total_fee', 'td_label' => 'Total Fees'],
                (object)['data' => 'paid_fee', 'name' => 'paid_fee', 'td_label' => 'Paid Fees'],
                (object)['data' => 'pending_fee', 'name' => 'pending_fee', 'td_label' => 'Pending Fees'],
            ];
            View::share("columns", $columns);

            if ($request->ajax()) {
                $sub = CourceRegistration::select(DB::raw('MAX(id) as id'))
                    ->where('status', 'active')
                    ->groupBy('register_id');

                $data = CourceRegistration::whereIn('id', $sub)
                    ->with(['course', 'admission', 'batch', 'class', 'shift', 'feescollection'])
                    ->withTrashed()
                    ->orderBy('created_at', 'desc')
                    ->get();


                $allRegistrations = CourceRegistration::with('course')
                    ->whereIn('register_id', $data->pluck('register_id'))
                    ->get()
                    ->groupBy('register_id');

                $feesCollection = FeesCollection::select('student_id', DB::raw('SUM(fees) as total_paid'))
                    ->whereIn('student_id', $data->pluck('register_id'))
                    ->groupBy('student_id')
                    ->pluck('total_paid', 'student_id');

                return Datatables::of($data)
                    ->addIndexColumn()

                    // Student name
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

                    // Course Name and Fee
                    ->editColumn('course_name', function ($row) use ($allRegistrations) {
                        $registrations = $allRegistrations[$row->register_id] ?? collect();
                        return $registrations->map(function ($item) {
                            return $item->course->course_name . ' (' . number_format($item->fee, 2) . ')';
                        })->implode('<br> ');
                    })

                    // Date of Joining
                    ->editColumn('date', function ($row) use ($allRegistrations) {
                        $registrations = $allRegistrations[$row->register_id] ?? collect();
                        return $registrations->map(function ($item) {
                            return Carbon::parse($item->date)->format('d-m-Y');
                        })->implode('<br> ');
                    })

                    // Total Fee
                    ->addColumn('total_fee', function ($row) use ($allRegistrations) {
                        $total_fee = ($allRegistrations[$row->register_id] ?? collect())->sum('fee');
                        return '₹' . number_format($total_fee, 2);
                    })

                    // Paid Fee
                    ->addColumn('paid_fee', function ($row) use ($feesCollection) {
                        $paid_fee = $feesCollection[$row->register_id] ?? 0;
                        return '₹' . number_format($paid_fee, 2);
                    })

                    // Pending Fee
                    ->addColumn('pending_fee', function ($row) use ($allRegistrations, $feesCollection) {
                        $total_fee = ($allRegistrations[$row->register_id] ?? collect())->sum('fee');
                        $paid_fee = $feesCollection[$row->register_id] ?? 0;
                        $pending = $total_fee - $paid_fee;
                        return '₹' . number_format($pending, 2);
                    })

                    ->rawColumns(['total_fee', 'course_name', 'date'])
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
