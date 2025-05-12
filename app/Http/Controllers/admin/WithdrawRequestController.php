<?php

namespace App\Http\Controllers\admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\DocumentRequest;
use App\Http\Requests\RedemptionRequest;
use App\Models\WithdrawRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\DataTables;

class WithdrawRequestController extends Controller
{
    public $modules = [];

    public function __construct()
    {
        $this->modules = [
            'title' => 'Withdraw Request',
            'folder_path' => 'withdraw-request',
            'route' => 'withdraw-request',
            'table_name' => (new WithdrawRequest())->getTable(),
            'permisstion_prefix' => 'withdraw-request',
            'public_folder' => public_path(),
        ];

        $this->middleware('permission:' . $this->modules['permisstion_prefix'] . '-list', ['only' => ['index', 'show']]);
        $this->middleware('permission:' . $this->modules['permisstion_prefix'] . '-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:' . $this->modules['permisstion_prefix'] . '-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:' . $this->modules['permisstion_prefix'] . '-delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $modules = $this->modules;
        View::share('modules', $modules);
        try {
            $columns = [
                (object)['data' => "withdraw_request_date", 'name' => 'withdraw_request_date', 'td_label' => 'Date'],
                (object)['data' => 'user_name', 'name' => 'user_id', 'td_label' => 'User'],
                (object)['data' => "request_points", 'name' => 'request_points', 'td_label' => 'Request Points'],
                (object)['data' => "request_amount", 'name' => 'request_amount', 'td_label' => 'Request Amount'],
                (object)['data' => "withdraw_request_status", 'name' => 'withdraw_request_status', 'td_label' => 'Status', 'className' =>  'w-5 text-center'],
                (object)['data' => "action", 'name' => 'action', 'td_label' => 'Action', 'orderable' => false, 'searchable' => false, 'className' =>  'w-10 text-center'],
            ];
            View::share("columns", $columns);

            $data = [];

            if ($request->ajax()) {
                // dd($request->all());
                $data = WithdrawRequest::select('*');
                $editPermisstion = (Auth::user()->hasDirectPermission($modules["permisstion_prefix"] . '-edit')) ? true : false;
                // $deletePermisstion = (Auth::user()->hasDirectPermission($modules["permisstion_prefix"] . '-delete')) ? true : false;
                // $editPermisstion = true;
                // $deletePermisstion = true;

                $returnData = DataTables::of($data)
                    ->addIndexColumn()
                    ->filter(function ($query) use ($request) {
                        if ($request->has('filter_by_slider_type') && $request->filter_by_slider_type) {
                            $query->where('slider_type', $request->filter_by_slider_type);
                        }
                    })
                    ->editColumn('withdraw_request_date', function ($record) {
                        $response = Helper::convert_date($record?->withdraw_request_date, 'Y-m-d', "d-m-Y");
                        // $response = $record?->created_at;
                        return $response;
                    })
                    ->editColumn('withdraw_request_status', function ($record) {
                        $status = '';
                        if ($record?->withdraw_request_status == "Pending") {
                            $status = '<span class="badge bg-secondary-subtle text-secondary text-uppercase">Pending </span>';
                        } else if ($record?->withdraw_request_status == "Approve") {
                            $status = '<span class="badge bg-success-subtle text-success text-uppercase">Approve</span>';
                        } else if ($record?->withdraw_request_status == "Reject") {
                            $status = '<span class="badge bg-danger-subtle text-danger text-uppercase">Reject</span>';
                        } else {
                            $status = '-';
                        }
                        return $status;
                    })
                    ->addColumn('action', function ($row) use ($modules, $editPermisstion) {
                        $btn = '<div class="d-flex gap-2 justify-content-center">';
                        // $btn = '<a href="javascript:void(0)" class="edit btn btn-primary btn-sm">View</a>';
                        // $btn .= '<div class="show"><a href="' . route($modules["route"] . ".show", [$row["id"]]) . '" class="btn btn-info btn-icon mr-2"><i class="bi bi-eye"></i></a></div>';
                        // $btn .= '<div class="show"><a href="' . route($modules["route"] . ".show", [$row["id"]]) . '" class="btn btn-info mr-2">View</a></div>';
                        if ($editPermisstion) {
                            $btn .= '<div class="edit"><a href="' . route($modules["route"] . ".edit", [$row["id"]]) . '" class="btn btn-info btn-icon mr-2"><i class="bx bx-edit-alt"></i></a></div>';
                        } else {
                            $btn .= '-';
                        }
                        // if ($deletePermisstion) {
                        //     $btn .= '<div class="remove"><a href="javascript:void(0)" data-id="' . $row->id . '" data-did="' . route($modules["route"] . ".destroy", [$row["id"]]) . '" class="btn btn-danger btn-icon deletebutton"><i class="bx bx-trash"></i></i></a></div>';
                        // }
                        $btn .= '</div>';
                        return $btn;
                    })
                    ->rawColumns(['withdraw_request_status', 'action'])
                    ->make(true);
                return $returnData;
            }
            return view('admin.module.' . $modules['folder_path'] . '.index', compact('data'));
        } catch (\Exception $e) {
            return Redirect::route('dashboard')->withErrors($e->getMessage());
        }
    }

    public function create()
    {
        $modules = $this->modules;
        View::share('modules', $modules);
        return view('admin.module.' . $modules['folder_path'] . '.form', compact('modules'));
    }
    
    public function edit(string $id)
    {
        try {
            $modules = $this->modules;
            View::share('modules', $modules);

            $edit = WithdrawRequest::findOrFail($id);
            return view('admin.module.' . $modules['folder_path'] . '.form', compact('modules', 'edit'));
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage())->withInput();
        }
    }

    public function update(Request $request, string $id)
    {
        $modules = $this->modules;
        View::share('modules', $modules);

        DB::beginTransaction();
        try {

            $updateDocument = WithdrawRequest::findOrFail($id);
            // dd(135, $updateDocument);

            $update = $request->all();
            $update['updated_by'] = Auth::user()->id;


            if ($request->hasFile('image')) {
                $image_name = Helper::makeSlug((string)$id) . '-' . $request?->withdraw_request_status . '-' . date('Ymd-His');

                $file = $request->file('image');

                $extenstion = $file->getClientOriginalExtension();

                $filename = $image_name . '.' . $extenstion;

                $uploadedPath = $modules['public_folder'];
                $sub_folder_path = WithdrawRequest::$folderPath;
                if ($file->move($uploadedPath, $filename)) {
                    $uploadedImage = $sub_folder_path . $filename;
                    if (in_array($extenstion, ["jpeg", "png", "jpg"])) {
                        $webP = Helper::existingImageConverToWebp($extenstion, $uploadedPath, $filename, $image_name, 80, true);
                        if ($webP) {
                            $uploadedImage = $sub_folder_path . $webP;
                            $extenstion = "webp";
                        }
                    }
                    $filename = $uploadedImage;
                }
                $update['image'] = $filename;
                // $update['file_mime_type'] = $extenstion;
            }
            $updateDocument->update($update);
            DB::commit();

            return Redirect::route($modules['route'] . '.index')->withSuccess($modules['title'] . ' update successfully');
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage())->withInput();
        }
    }
}
