<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Admission;
use App\Models\CourceRegistration;
use App\Models\FeesCollection;
use App\Models\Master\MasterCourse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\Facades\DataTables;

class StudentsFeesReportController extends Controller
{
    public $modules = [];
    public function __construct()
    {
        $this->modules = [
            'title' => 'Students Fees Report',
            'folder_path' => 'software.module.students-fees-report',
            'route' => 'students-fees-report',
            'table_name' => (new FeesCollection())->getTable(),
            'permisstion_prefix' => 'students-fees-report',
        ];

        // $this->middleware('permission:'.$this->modules['permisstion_prefix'].'-list', ['only' => ['index','show']]);
        // $this->middleware('permission:'.$this->modules['permisstion_prefix'].'-create', ['only' => ['create','store']]);
        // $this->middleware('permission:'.$this->modules['permisstion_prefix'].'-edit', ['only' => ['edit','update']]);
        // $this->middleware('permission:'.$this->modules['permisstion_prefix'].'-delete', ['only' => ['destroy']]);
    }
    /**
     * Display a listing of the resource.
     */
    // public function index(Request $request)
    // {
    //     $modules = $this->modules;
    //     View::share('modules', $modules);

    //     try {
    //         // Define DataTable columns
    //         $columns = [

    //             (object)['data' => 'year_semester', 'name' => 'year_semester', 'td_label' => 'Semester Name'],
    //             (object)['data' => 'total_fee', 'name' => 'total_fee', 'td_label' => 'Total Fees'],
    //             (object)['data' => 'pending_fee', 'name' => 'pending_fee', 'td_label' => 'Pending Fees'],
    //             (object)['data' => 'paid_fee', 'name' => 'paid_fee', 'td_label' => 'Paid Fees'],
    //         ];
    //         View::share("columns", $columns);

    //         $admissions = Admission::whereIn('status', ['created', 'completed'])->get();
    //         $courses = MasterCourse::all();

    //         if ($request->ajax()) {
    //             $sub = CourceRegistration::select(DB::raw('MAX(id) as id'))
    //                 ->where('status', 'active')
    //                 ->groupBy('register_id');

    //             $dataQuery = CourceRegistration::whereIn('id', $sub)
    //                 ->with(['course', 'admission', 'batch', 'class', 'shift', 'feescollection', 'semester'])
    //                 ->withTrashed()
    //                 ->orderBy('created_at', 'desc');

    //             // Filter: Student ID, Name, or Contact No
    //             if ($request->filled('student_filter')) {
    //                 $studentFilter = $request->student_filter;

    //                 $dataQuery->where(function ($q) use ($studentFilter) {
    //                     $q->where('register_id', $studentFilter)
    //                         ->orWhereHas('admission', function ($q2) use ($studentFilter) {
    //                             $q2->where(DB::raw("CONCAT(first_name,' ',father_name,' ',last_name)"), 'like', "%$studentFilter%")
    //                                 ->orWhere('mobile_no', 'like', "%$studentFilter%");
    //                         });
    //                 });
    //             }

    //             // Filter: Course ID
    //             if ($request->filled('course_id')) {
    //                 $dataQuery->where('course_id', $request->course_id);
    //             }

    //             // Filter: Date of Joining
    //             if ($request->filled('single_date')) {
    //                 $date = Carbon::createFromFormat('d-m-Y', $request->single_date)->format('Y-m-d');
    //                 $dataQuery->whereDate('date', $date);
    //             }

    //             $data = $dataQuery->get();

    //             // Group all registrations per student
    //             $allRegistrations = CourceRegistration::with('course')
    //                 ->whereIn('register_id', $data->pluck('register_id'))
    //                 ->get()
    //                 ->groupBy('register_id');

    //             $feesCollection = FeesCollection::select(
    //                 'student_id',
    //                 'year_semester',
    //                 DB::raw('SUM(fees) as total_paid')
    //             )
    //                 ->whereIn('student_id', $data->pluck('register_id'))
    //                 ->groupBy('student_id', 'year_semester')
    //                 ->get()
    //                 ->groupBy('student_id'); // <-- Important: groupBy here returns a collection of arrays



    //             return DataTables::of($data)
    //                 ->addIndexColumn()

    //                 ->addColumn('year_semester', function ($row) use ($feesCollection) {
    //                     $records = $feesCollection[$row->register_id] ?? collect();
    //                     return $records->pluck('year_semester')->unique()->implode(', ');
    //                 })


    //                 ->addColumn('total_fee', function ($row) use ($allRegistrations) {
    //                     $total_fee = ($allRegistrations[$row->register_id] ?? collect())->sum('fee');
    //                     return '₹' . number_format($total_fee, 2);
    //                 })

    //                 ->addColumn('paid_fee', function ($row) use ($feesCollection) {
    //                     $records = $feesCollection[$row->register_id] ?? collect();
    //                     $total_paid = $records->sum('total_paid');
    //                     return '₹' . number_format($total_paid, 2);
    //                 })

    //                 ->addColumn('pending_fee', function ($row) use ($allRegistrations, $feesCollection) {
    //                     $total_fee = ($allRegistrations[$row->register_id] ?? collect())->sum('fee');
    //                     $records = $feesCollection[$row->register_id] ?? collect();
    //                     $paid_fee = $records->sum('total_paid');
    //                     $pending = $total_fee - $paid_fee;
    //                     return '₹' . number_format($pending, 2);
    //                 })


    //                 ->rawColumns(['year_semester', 'date', 'total_fee'])
    //                 ->make(true);
    //         }

    //         return view($modules['folder_path'] . '.index', compact('admissions', 'courses'));
    //     } catch (\Exception $e) {
    //         return Redirector::route('software.dashboard')->withErrors($e->getMessage());
    //     }
    // }

    // public function index(Request $request)
    // {
    //     $modules = $this->modules;
    //     View::share('modules', $modules);
    //     try {
    //         $columns = [
    //             (object)['data' => 'year_semester', 'name' => 'year_semester', 'td_label' => 'Semester Name'],
    //             (object)['data' => 'total_fee', 'name' => 'total_fee', 'td_label' => 'Total Fees'],
    //             (object)['data' => 'pending_fee', 'name' => 'pending_fee', 'td_label' => 'Pending Fees'],
    //             (object)['data' => 'paid_fee', 'name' => 'paid_fee', 'td_label' => 'Paid Fees'],
    //         ];
    //         View::share("columns", $columns);

    //         $data = [];

    //         if ($request->ajax()) {
    //             // dd($request->all());
    //             $data = FeesCollection::select('*')->where('student_id', $request->search);
    //             $data = $data->withTrashed();
    //             $data = $data->with(['course']);
    //             // $editPermisstion = (Auth::user()->can($modules["table_name"].'-edit')) ? true : false;
    //             // $deletePermisstion = (Auth::user()->can($modules["table_name"].'-delete')) ? true : false;
    //             $editPermisstion = true;
    //             $deletePermisstion = true;

    //             $returnData = Datatables::of($data)
    //                 ->addIndexColumn()
    //                 ->filter(function ($query) use ($request) {
    //                     if ($request->has('search') && $request->search) {
    //                         $query->where('student_id', $request->search);
    //                         // $query->orWhere('course_fees', 'like',"%".$request->search."%");
    //                     }
    //                 })
    //                 ->rawColumns(['status', 'action'])
    //                 ->make(true);
    //             return $returnData;
    //         }
    //         return view($modules['folder_path'] . '.index', compact('data'));
    //     } catch (\Exception $e) {
    //         return Redirect::route('dashboard')->withErrors($e->getMessage());
    //     }
    // }

    // public function index(Request $request)
    // {
    //     $modules = $this->modules;
    //     View::share('modules', $modules);

    //     try {
    //         $columns = [
    //             (object)['data' => 'year_semester', 'name' => 'year_semester', 'td_label' => 'Semester'],
    //             (object)['data' => 'total_fee', 'name' => 'total_fee', 'td_label' => 'Total Fees'],
    //             (object)['data' => 'paid_fee', 'name' => 'paid_fee', 'td_label' => 'Paid Fees'],
    //             (object)['data' => 'pending_fee', 'name' => 'pending_fee', 'td_label' => 'Pending Fees'],
    //         ];
    //         View::share("columns", $columns);

    //         if ($request->ajax()) {


    //             // Step 1: Get all course registrations grouped by register_id and year_semester
    //             $registrations = DB::table('cource_registration')
    //                 ->select(
    //                     'register_id',
    //                     DB::raw('1 as year_semester'), // Replace with actual column if exists
    //                     DB::raw('SUM(fee) as total_fee')
    //                 )
    //                 ->groupBy('register_id', 'year_semester')
    //                 ->get();

    //             // Step 2: Get paid fees from fees_collections
    //             $paidFees = DB::table('fees_collections')
    //                 ->select(
    //                     'student_id as register_id',
    //                     'year_semester',
    //                     DB::raw('SUM(fees) as paid_fee')
    //                 )
    //                 ->groupBy('student_id', 'year_semester')
    //                 ->get()
    //                 ->keyBy(function ($item) {
    //                     return $item->register_id . '-' . $item->year_semester;
    //                 });

    //             // Step 3: Merge data
    //             $finalData = $registrations->map(function ($reg) use ($paidFees) {
    //                 $key = $reg->register_id . '-' . $reg->year_semester;
    //                 $paid = $paidFees[$key]->paid_fee ?? 0;

    //                 return [
    //                     'register_id' => $reg->register_id,
    //                     'year_semester' => $reg->year_semester,
    //                     'total_fee' => $reg->total_fee,
    //                     'paid_fee' => $paid,
    //                     'pending_fee' => $reg->total_fee - $paid,
    //                 ];
    //             });

    //             return DataTables::of($finalData)->make(true);
    //         }

    //         return view($modules['folder_path'] . '.index');
    //     } catch (\Exception $e) {
    //         return Redirect::route('dashboard')->withErrors($e->getMessage());
    //     }
    // }

    public function index(Request $request)
    {
        $modules = $this->modules;
        View::share('modules', $modules);

        // Columns for datatable
        $columns = [
            (object)['data' => 'year_semester', 'name' => 'year_semester', 'td_label' => 'Semester'],
            (object)['data' => 'total_fee', 'name' => 'total_fee', 'td_label' => 'Total Fees'],
            (object)['data' => 'paid_fee', 'name' => 'paid_fee', 'td_label' => 'Paid Fees'],
            (object)['data' => 'pending_fee', 'name' => 'pending_fee', 'td_label' => 'Pending Fees'],
        ];
        View::share("columns", $columns);

        if ($request->ajax()) {
            dd($request->student_filter);
            $query = DB::table('cource_registration as cr')
                ->leftJoin('fees_collections as fc', function ($join) {
                    $join->on('cr.register_id', '=', 'fc.student_id')
                        ->on('cr.course_id', '=', 'fc.course_id')
                        ->on('cr.course_year', '=', 'fc.year_semester'); // Corrected here
                })
                ->join('master_course as mc', 'cr.course_id', '=', 'mc.id')
                ->select(
                    'cr.course_year as year_semester', // Corrected here
                    DB::raw('MAX(cr.fee) as total_fee'),
                    DB::raw('IFNULL(SUM(fc.fees), 0) as paid_fee'),
                    DB::raw('MAX(cr.fee) - IFNULL(SUM(fc.fees), 0) as pending_fee')
                )
                ->where('cr.course_id', $request->course_id)
                ->groupBy('cr.register_id', 'cr.course_id', 'cr.course_year');


            return DataTables::of($query)
                ->addIndexColumn()
                ->filter(function ($query) use ($request) {
                    $searchValue = $request->search['value'] ?? null;
                    $hasRequiredFilters = $request->filled('course_id') && $request->filled('batch_id') && $request->filled('subject_name');

                    if (!$hasRequiredFilters && empty($searchValue)) {
                        $query->whereRaw('0=1');
                        return;
                    }

                    if (!empty($searchValue)) {
                        $query->where(function ($q) use ($searchValue) {
                            $q->where(DB::raw("CONCAT(admission.first_name, ' ', admission.last_name)"), 'like', "%{$searchValue}%")
                                ->orWhere('master_course.course_name', 'like', "%{$searchValue}%")
                                ->orWhere('test.marks', 'like', "%{$searchValue}%")
                                ->orWhere('test.subject_name', 'like', "%{$searchValue}%");
                        });
                    }

                    if ($request->filled('course_id')) {
                        $query->where('test.course_id', $request->course_id);
                    }
                })
                ->editColumn('cr_date', function ($row) {
                    return $row->cr_date ? date('d-m-Y', strtotime($row->cr_date)) : '-';
                })
                ->editColumn('subject_name', function ($row) {
                    return $row->subject_name ?? '-';
                })

                ->rawColumns(['input'])
                ->make(true);
        }

        return view($modules['folder_path'] . '.index');
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
