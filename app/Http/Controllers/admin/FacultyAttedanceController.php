<?php

namespace App\Http\Controllers\admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Attedance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FacultyAttedanceController extends Controller
{
    public $modules = [];

    public function __construct()
    {
        $this->modules = [
            'title' => 'Faculty Attendance',
            'folder_path' => 'software.module.faculty_attendance',
            'route' => 'faculty-attendance',
            'table_name' => (new Attedance())->getTable(),
            'permission_prefix' => 'faculty-attendance',
        ];
    }

    public function index(Request $request)
    {
        $modules = $this->modules;
        $modules['login_user_role'] = Helper::getLoginUserRole();
        $modules['permission_list'] = Helper::directCan($modules['permission_prefix'] . '-list');
        
        if (!$modules['permission_list']) {
            if (isset($request) && $request->ajax()) {
                return response()->json(['error' => 'User does not have the right permissions.'], 403);
            }
            abort(403, 'User does not have the right permissions.');
        }

        View::share('modules', $modules);
        
        try {
            $columns = [
                (object)['data' => 'biometric_id', 'name' => 'biometric_id', 'td_label' => 'Biometric ID'],
                (object)['data' => 'faculty_name', 'name' => 'faculty_name', 'td_label' => 'Faculty Name', 'orderable' => false, 'searchable' => false],
                (object)['data' => 'date', 'name' => 'date', 'td_label' => 'Date'],
                (object)['data' => 'in_time', 'name' => 'in_time', 'td_label' => 'Punch In Time', 'orderable' => false, 'searchable' => false],
                (object)['data' => 'out_time', 'name' => 'out_time', 'td_label' => 'Punch Out Time', 'orderable' => false, 'searchable' => false],
                (object)['data' => 'DeviceKey', 'name' => 'DeviceKey', 'td_label' => 'Device Key', 'orderable' => false, 'searchable' => false],
                (object)['data' => 'DeviceName', 'name' => 'DeviceName', 'td_label' => 'Device Name', 'orderable' => false, 'searchable' => false],
            ];
            View::share("columns", $columns);

            if ($request->ajax()) {
                $user = Auth::user();
                $isFaculty = Helper::getLoginUserRole() === 'Facility';

                $data = Attedance::select([
                        'attedance.biometric_id',
                        'attedance.date',
                        DB::raw('MIN(attedance.in_time) as first_punch'),
                        DB::raw('MAX(attedance.in_time) as last_punch'),
                        DB::raw('MIN(attedance.DeviceKey) as DeviceKey'),
                        DB::raw('MIN(attedance.DeviceName) as DeviceName'),
                        'users.name as faculty_name',
                    ])
                    ->join('users', 'users.biometric_id', '=', 'attedance.biometric_id');

                if ($isFaculty) {
                    $biometricId = $user->biometric_id ?? '---';
                    $data = $data->where('attedance.biometric_id', $biometricId);
                } else {
                    $facultyBiometrics = User::whereHas('roles', function ($query) {
                        $query->where('name', 'Facility');
                    })->pluck('biometric_id')->filter()->toArray();

                    $data = $data->whereIn('attedance.biometric_id', $facultyBiometrics);
                }

                $data = $data->groupBy('attedance.biometric_id', 'attedance.date', 'users.name')
                    ->orderByDesc('attedance.date');

                $returnData = DataTables::of($data)
                    ->addIndexColumn()
                    ->filter(function ($query) use ($request) {
                        $search = trim((string) ($request->input('search.value') ?? $request->search ?? ''));
                        if ($search === '') {
                            return;
                        }
                        $query->where(function ($q) use ($search) {
                            $q->where('attedance.date', 'like', "%{$search}%")
                              ->orWhere('users.name', 'like', "%{$search}%")
                              ->orWhere('attedance.DeviceKey', 'like', "%{$search}%")
                              ->orWhere('attedance.DeviceName', 'like', "%{$search}%");
                        });
                    })
                    ->editColumn('biometric_id', function ($row) {
                        return $row->biometric_id ?: '-';
                    })
                    ->addColumn('faculty_name', function ($row) {
                        return $row->faculty_name ?: '-';
                    })
                    ->editColumn('date', function ($row) {
                        return Carbon::parse($row->date)->format('d-m-Y');
                    })
                    ->editColumn('in_time', function ($row) {
                        if (empty($row->first_punch)) {
                            return '<p class="text-center">-</p>';
                        }
                        return Carbon::parse($row->first_punch)->format('h:i A');
                    })
                    ->addColumn('out_time', function ($row) {
                        if (empty($row->last_punch) || $row->last_punch === $row->first_punch) {
                            return '<p class="text-center">-</p>';
                        }
                        return Carbon::parse($row->last_punch)->format('h:i A');
                    })
                    ->editColumn('DeviceKey', function ($row) {
                        return $row->DeviceKey ?: '-';
                    })
                    ->editColumn('DeviceName', function ($row) {
                        return $row->DeviceName ?: '-';
                    })
                    ->rawColumns(['date', 'in_time', 'out_time'])
                    ->make(true);
                    
                return $returnData;
            }
            
            return view($modules['folder_path'] . '.index');
        } catch (\Exception $e) {
            return Redirect::route('dashboard')->withErrors($e->getMessage());
        }
    }
}
