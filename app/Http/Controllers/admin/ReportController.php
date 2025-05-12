<?php

namespace App\Http\Controllers\admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Redemption;
use App\Models\RedemptionItem;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\DataTables;

class ReportController extends Controller
{

    public $modules = [];
    public function __construct()
    {
        $this->modules = [
            'title' => 'Scanned Report',
            'folder_path' => 'reports',
            'route' => null,
            'table_name' => null,
            'permisstion_prefix' => null,
            'public_folder' => public_path(),
        ];

        $this->middleware('permission:reports');
        // $this->middleware('permission:' . $this->modules['permisstion_prefix'] . '-list', ['only' => ['index', 'show']]);
        // $this->middleware('permission:' . $this->modules['permisstion_prefix'] . '-create', ['only' => ['create', 'store']]);
        // $this->middleware('permission:' . $this->modules['permisstion_prefix'] . '-edit', ['only' => ['edit', 'update']]);
        // $this->middleware('permission:' . $this->modules['permisstion_prefix'] . '-delete', ['only' => ['destroy']]);
    }

    public function scannedReport(Request $request)
    {
        $this->authorize('scan-report');

        $modules = $this->modules;
        $modules['route'] = 'scanned-report';
        View::share('modules', $modules);

        try {

            View::share('redeption_list', Redemption::all());

            $columns = [
                // (object)[ 'data' => "id", 'name' => 'id', 'td_label' => 'Id' ],
                // (object)['data' => "id", 'name' => 'id', 'td_label' => 'Sr. No.'],
                (object)['data' => "redemption_label", 'name' => 'redemption_id', 'td_label' => 'Redemption Label'],
                (object)['data' => "redemption_created", 'name' => 'redemption_created', 'td_label' => 'Redemption Created'],
                (object)['data' => "redemption_code_label", 'name' => 'redemption_code_label', 'td_label' => 'Redemption Code'],
                (object)['data' => "collect_points", 'name' => 'collect_points', 'td_label' => 'Collect Point', 'className' =>  'text-center'],
                (object)['data' => "scanned_user_detail", 'name' => 'scanned_user_id', 'td_label' => 'Scanned By'],
                (object)['data' => "scanned_date", 'name' => 'scanned_date', 'td_label' => 'Scanned Date', 'className' =>  'text-center'],
                // (object)['data' => "status", 'name' => 'status', 'td_label' => 'Status', 'className' =>  'text-center'],
                // (object)['data' => "action", 'name' => 'action', 'td_label' => 'Action', 'orderable' => false, 'searchable' => false, 'className' =>  'w-10 text-center'],
            ];
            View::share("columns", $columns);

            $data = [];
            if ($request->ajax()) {
                $data = RedemptionItem::with(['parent_redeption'])->select('*');
                $data = $data->whereNotNull('scanned_user_id');

                // $editPermisstion = (Auth::user()->can($modules["permisstion_prefix"] . '-edit')) ? true : false;
                // $deletePermisstion = (Auth::user()->can($modules["permisstion_prefix"] . '-delete')) ? true : false;
                $editPermisstion = true; $deletePermisstion = true;

                $returnData = DataTables::of($data)
                    ->addIndexColumn()
                    ->filter(function ($query) use ($request) {
                        if ($request->has('search') && $request->search) {
                            $query->where('redemption_code_label', "LIKE", "%".$request->search."%");
                            // $query->where('users.name', "LIKE", "%".$request->search."%");
                        }
                        if ($request->has('filter_by_redeption_id') && $request->filter_by_redeption_id) {
                            $query->where('redemption_id', $request->filter_by_redeption_id);
                        }
                    })
                    ->addColumn('redemption_label', function ($record) {
                        $redemption_label = "-";
                        if($record?->parent_redeption && $record?->parent_redeption?->id){
                            $redemption_label = '<a href="' . route("redemption.show", [$record?->parent_redeption?->id]) . '" target="_blank">';
                            $redemption_label .= $record?->parent_redeption?->label;
                            $redemption_label .= '</a>';
                            // $redemption_label .= '<a href="" class="btn btn-info btn-sm btn-icon mx-1"><i class="ri ri-external-link-fill"></i></a>';
                            // $redemption_label .= '<br /><a href="" class="btn btn-info btn-sm w-100">View</a>';
                        }
                        return $redemption_label;
                    })
                    ->addColumn('redemption_created', function ($record) {
                        $redemption_created_at = "-";
                        if($record?->parent_redeption){
                            $redemption_created_at = Helper::convert_date($record?->parent_redeption?->created_at, "Y-m-d H:i:s", 'd-m-Y H:i A');
                        }
                        return $redemption_created_at;
                    })
                    ->editColumn('scanned_user_detail', function ($record) {
                        $scanned_user = '';
                        if ($record?->scanned_user) {
                            $record?->scanned_user;
                            $scannedUserDetail = $record?->scanned_user;
                            $scanned_user = 'Name :- '.$scannedUserDetail?->name;
                            $scanned_user .= '<br>No :- '.$scannedUserDetail?->phone;
                        } else {
                            $scanned_user = '-';
                        }
                        return $scanned_user;
                    })
                    ->editColumn('scanned_date', function ($record) {
                        $scanned_date = $record?->scanned_date;
                        $scanned_date = Helper::convert_date($scanned_date, "Y-m-d H:i:s", 'd-m-Y H:i A');
                        return $scanned_date;
                    })
                    ->editColumn('status', function ($record) {
                        $status = '';
                        if ($record?->status == "active") {
                            $status = '<span class="badge bg-success-subtle text-success text-uppercase">Active</span>';
                        } else if ($record?->status == "inactive") {
                            $status = '<span class="badge bg-danger-subtle text-danger text-uppercase">InActive</span>';
                        } else {
                            $status = '-';
                        }
                        return $status;
                    })
                    ->addColumn('action', function ($row) use ($modules, $editPermisstion, $deletePermisstion) {
                        $btn = "";
                        // $btn .= '<div class="d-flex gap-2 justify-content-center">';
                        // // $btn = '<a href="javascript:void(0)" class="edit btn btn-primary btn-sm">View</a>';
                        // if ($editPermisstion) {
                        //     $btn .= '<div class="edit"><a href="' . route($modules["route"] . ".edit", [$row["id"]]) . '" class="btn btn-info btn-icon mr-2"><i class="bx bx-edit-alt"></i></a></div>';
                        // }
                        // if ($deletePermisstion) {
                        //     $btn .= '<div class="remove"><a href="javascript:void(0)" data-id="' . $row->id . '" data-did="' . route($modules["route"] . ".destroy", [$row["id"]]) . '" class="btn btn-danger btn-icon deletebutton"><i class="bx bx-trash"></i></i></a></div>';
                        // }
                        // $btn .= '</div>';
                        return $btn;
                    })
                    ->rawColumns(['redemption_label','scanned_user_detail', 'status', 'action'])
                    ->make(true);
                return $returnData;
                return $this->sendResponse($returnData, "Response scanned data");
            }
            return view('admin.module.' . $modules['folder_path'] . '.scanned-report', compact('data'));
            return $request->all();
        } catch (\Exception $e) {
            return Redirect::route('dashboard')->withErrors($e->getMessage());
        }
    }
}
