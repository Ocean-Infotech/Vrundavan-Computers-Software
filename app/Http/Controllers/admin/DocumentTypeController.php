<?php

namespace App\Http\Controllers\admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\DocumentTypeRequest;
use App\Models\DocumentType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\DataTables;

class DocumentTypeController extends Controller
{
    public $modules = [];
    public function __construct()
    {
        $this->modules = [
            'title' => 'Document Type',
            'folder_path' => 'software.module.document-type',
            'route' => 'document-type',
            'table_name' => (new DocumentType())->getTable(),
            'permission_prefix' => 'document-type',
            // 'public_folder' => public_path(Redemption::$folderPath),
            'public_folder' => public_path(),
        ];

        // $this->middleware('check.direct.permission:' . $this->modules['permission_prefix'] . '-list', ['only' => ['index', 'show']]);
        // $this->middleware('check.direct.permission:' . $this->modules['permission_prefix'] . '-create', ['only' => ['create', 'store']]);
        // $this->middleware('check.direct.permission:' . $this->modules['permission_prefix'] . '-edit', ['only' => ['edit', 'update']]);
        // $this->middleware('check.direct.permission:' . $this->modules['permission_prefix'] . '-delete', ['only' => ['destroy']]);

    }

    public function index(Request $request)
    {
        $modules = $this->modules;
        View::share('modules', $modules);
        try {

            $data = [];
            // dd(Auth::user()->can($modules["permission_prefix"] . '-edit'), Auth::user()->hasDirectPermission($modules["permission_prefix"] . '-edit'), $modules["permission_prefix"]. '-edit', $this->middleware('check.direct.permission:' . $this->modules['permission_prefix'] . '-list'));
            if ($request->ajax()) {
                // dd($request->all());
                $data = DocumentType::select('*');
                $editPermisstion = (Auth::user()->hasDirectPermission($modules["permission_prefix"] . '-edit')) ? true : false;
                $deletePermisstion = (Auth::user()->hasDirectPermission($modules["permission_prefix"] . '-delete')) ? true : false;
                // $editPermisstion = false; $deletePermisstion = true;

                $returnData = DataTables::of($data)
                    ->addIndexColumn()
                    ->filter(function ($query) use ($request) {
                        if ($request->has('status') && $request->status) {
                            $query->where('status', $request->status);
                        }
                        if ($request->has('search')) {
                            $query->where('name', 'like',"%".$request->search."%");
                        }
                    })
                    ->editColumn('created_at', function ($record) {
                        $response = Helper::convert_date($record?->created_at, 'Y-m-d H:i:s', "d-m-Y");
                        // $response = $record?->created_at;
                        return $response;
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
                        $btn = '<div class="d-flex gap-2 justify-content-center">';
                        // $btn = '<a href="javascript:void(0)" class="edit btn btn-primary btn-sm">View</a>';
                        // $btn .= '<div class="show"><a href="' . route($modules["route"] . ".show", [$row["id"]]) . '" class="btn btn-info btn-icon mr-2"><i class="bi bi-eye"></i></a></div>';
                        // $btn .= '<div class="show"><a href="' . route($modules["route"] . ".show", [$row["id"]]) . '" class="btn btn-info mr-2">View</a></div>';
                        if ($editPermisstion) {
                            $btn .= '<div class="edit"><a href="' . route($modules["route"] . ".edit", [$row["id"]]) . '" class="btn btn-info btn-icon mr-2"><i class="bx bx-edit-alt"></i></a></div>';
                        }
                        if ($deletePermisstion) {
                            $btn .= '<div class="remove"><a href="javascript:void(0)" data-id="' . $row->id . '" data-did="' . route($modules["route"] . ".destroy", [$row["id"]]) . '" class="btn btn-danger btn-icon deletebutton"><i class="bx bx-trash"></i></i></a></div>';
                        }
                        $btn .= '</div>';
                        return $btn;
                    })
                    ->rawColumns(['status', 'action'])
                    ->make(true);
                return $returnData;
            }

            $columns = [
                // (object)[ 'data' => "id", 'name' => 'id', 'td_label' => 'Id' ],
                // (object)['data' => "created_at", 'name' => 'created_at', 'td_label' => 'Created Date'],
                (object)['data' => "name", 'name' => 'name', 'td_label' => 'Document Type'],
                (object)['data' => "status", 'name' => 'status', 'td_label' => 'Status', 'className' =>  'w-5 text-center'],
                (object)['data' => "action", 'name' => 'action', 'td_label' => 'Action', 'orderable' => false, 'searchable' => false, 'className' =>  'w-10 text-center'],
            ];
            View::share("columns", $columns);
            return view($modules['folder_path'] . '.index', compact('data'));
        } catch (\Exception $e) {
            return Redirect::route('dashboard')->withErrors($e->getMessage());
        }
    }


    public function create()
    {
        $modules = $this->modules;
        View::share('modules', $modules);
        return view($modules['folder_path'] . '.form', compact('modules'));
    }

    public function store(DocumentTypeRequest $request)
    {
        $modules = $this->modules;
        View::share('modules', $modules);

        DB::beginTransaction();
        try {
            $input = $request->all();
            $input['slug'] = $request->name . ' ' . Helper::getTableWiseGetAutoIncrementId((new DocumentType())->getTable());
            $input['slug'] = Helper::makeSlug($input['slug']);
            $input['created_by'] = Auth::user()->id;
            // return $input;
            $redemption = DocumentType::create($input);
            DB::commit();

            return Redirect::route($modules['route'] . '.index')->withSuccess($modules['title'] . ' create successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $e->getMessage();
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage())->withInput();
        }
    }

    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {

        $modules = $this->modules;
        View::share('modules', $modules);
        try {

            $edit = DocumentType::findOrFail($id);
            return view($modules['folder_path'] . '.form', compact('modules', 'edit'));
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage())->withInput();
        }
    }


    public function update(DocumentTypeRequest $request, string $id)
    {
        $modules = $this->modules;
        View::share('modules', $modules);
        try {

            $update = $request->all();
            $update['slug'] = $request->name . ' ' . Helper::getTableWiseGetAutoIncrementId((new DocumentType())->getTable());
            $update['slug'] = Helper::makeSlug($update['slug']);
            $update['updated_by'] = Auth::user()->id;
            $updateSlider = DocumentType::findOrFail($id);
            $updateSlider->update($update);

            return Redirect::route($modules['route'] . '.index')->withSuccess($modules['title'] . ' update successfully');
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage())->withInput();
        }
    }

    public function destroy(Request $request, string $id)
    {
        $modules = $this->modules;
        View::share('modules', $modules);
        try {
            $dataDelete = DocumentType::findOrFail($id);
            if ($dataDelete) {
                unset($dataDelete->image);
                $dataDelete->deleted_by = Auth::user()?->id;
                $dataDelete->deleted_at = Carbon::now();
                $dataDelete->save();

                if ($request->ajax()) {
                    if ($dataDelete->delete()) {
                        // return Redirect::route($modules['route'].'.index');
                        return true;
                    }
                    return false;
                }
                if ($dataDelete->delete()) {
                    return Redirect::route($modules['route'] . '.index');
                }
            }
            return Redirect::back()->withErrors('something went wrong please try again later');
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage())->withInput();
        }
    }
}
