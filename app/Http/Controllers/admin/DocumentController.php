<?php

namespace App\Http\Controllers\admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\DocumentRequest;
use App\Models\Documents;
use App\Models\DocumentType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\DataTables;

class DocumentController extends Controller
{
    public $modules = [];
    public function __construct()
    {
        $this->modules = [
            'title' => 'Document\'s',
            'folder_path' => 'software.module.documents',
            'route' => 'documents',
            'table_name' => (new Documents())->getTable(),
            'permission_prefix' => 'documents',
            'public_folder' => public_path(Documents::$folderPath),
        ];

        // $this->middleware('check.direct.permission:' . $this->modules['permission_prefix'] . '-list', ['only' => ['index', 'show']]);
        // $this->middleware('check.direct.permission:' . $this->modules['permission_prefix'] . '-create', ['only' => ['create', 'store']]);
        // $this->middleware('check.direct.permission:' . $this->modules['permission_prefix'] . '-edit', ['only' => ['edit', 'update']]);
        // $this->middleware('check.direct.permission:' . $this->modules['permission_prefix'] . '-delete', ['only' => ['destroy']]);

        View::share("document_types", DocumentType::where('status', "active")->get());
    }

    public function index(Request $request)
    {
        $modules = $this->modules;
        View::share('modules', $modules);
        try {

            $data = [];
            // dd(Auth::user()->can($modules["permission_prefix"] . '-edit'), Auth::user()->hasDirectPermission($modules["permission_prefix"] . '-edit'), $modules["permission_prefix"]. '-edit', $this->middleware('check.direct.permission:' . $this->modules['permission_prefix'] . '-list'));

            $editPermisstion = (Auth::user()->hasDirectPermission($modules["permission_prefix"] . '-edit')) ? true : false;
            $deletePermisstion = (Auth::user()->hasDirectPermission($modules["permission_prefix"] . '-delete')) ? true : false;
            // $editPermisstion = false; $deletePermisstion = true;

            if ($request->ajax()) {
                // dd($request->all());
                $data = Documents::select('*');
                $data = $data->with(['document_type']);

                $returnData = DataTables::of($data)
                    ->addIndexColumn()
                    ->filter(function ($query) use ($request) {
                        if ($request->has('filter_document_type') && $request->filter_document_type) {
                            $query->where('document_type_id', $request->filter_document_type);
                        }
                        if ($request->has('status') && $request->status) {
                            $query->where('status', $request->status);
                        }
                        if ($request->has('search')) {
                            $query->where('name', 'like', "%" . $request->search . "%");
                        }
                    })
                    ->addColumn('document_type_name', function ($record) {
                        $response = $record?->document_type?->name;
                        return $response;
                    })
                    ->editColumn('document_file_url', function ($record) {
                        $btn = "";
                        $btn .= '<div class="edit"><a href="' . $record?->document_file_url . '" class="btn btn-info btn-sm mr-2" target="_blank">View</a></div>';;
                        // $response = $record?->created_at;
                        return $btn;
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
                    ->rawColumns(['document_type_name', 'document_file_url', 'status', 'action'])
                    ->make(true);
                return $returnData;
            }

            $columns = [
                // (object)[ 'data' => "id", 'name' => 'id', 'td_label' => 'Id' ],
                // (object)['data' => "created_at", 'name' => 'created_at', 'td_label' => 'Created Date'],
                (object)['data' => "document_type_name", 'name' => 'name', 'td_label' => 'Document Type'],
                (object)['data' => "name", 'name' => 'name', 'td_label' => 'Document Name'],
                (object)['data' => "document_file_url", 'name' => 'name', 'td_label' => 'Document View', 'orderable' => false, 'searchable' => false, 'className' =>  'w-10 text-center'],
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
        $createPermisstion = (Auth::user()->hasDirectPermission($modules["permission_prefix"] . '-create')) ? true : false;
        if (!$createPermisstion) {
            return abort(403, "You have not permission to access this url.");
        }

        View::share('modules', $modules);
        return view($modules['folder_path'] . '.form', compact('modules'));
    }

    public function store(DocumentRequest $request)
    {
        $modules = $this->modules;
        View::share('modules', $modules);

        DB::beginTransaction();
        try {
            $input = $request->all();
            $input['created_by'] = Auth::user()->id;

            if ($request->hasFile('document_file')) {
                $image_name = Helper::makeSlug($request?->name) . '-' . date('Ymd-His');

                $file = $request->file('document_file');

                $extenstion = $file->getClientOriginalExtension();

                $filename = $image_name . '.' . $extenstion;

                $uploadedPath = $modules['public_folder'];
                $sub_folder_path = Documents::$folderPath;
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
                $input['folder_path'] = $filename;
                $input['file_mime_type'] = $extenstion;
            }

            // return $input;
            $redemption = Documents::create($input);
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

        $createPermisstion = (Auth::user()->hasDirectPermission($modules["permission_prefix"] . '-edit')) ? true : false;
        if(!$createPermisstion){ return abort(403, "You have not permission to access this url."); }
        try {
            $edit = Documents::findOrFail($id);
            return view($modules['folder_path'] . '.form', compact('modules', 'edit'));
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage())->withInput();
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DocumentRequest $request, string $id)
    {
        $modules = $this->modules;
        View::share('modules', $modules);

        DB::beginTransaction();
        try {

            $updateDocument = Documents::findOrFail($id);
            $update = $request->all();
            $update['updated_by'] = Auth::user()->id;


            if ($request->hasFile('document_file')) {
                $image_name = Helper::makeSlug($request?->name) . '-' . date('Ymd-His');

                $file = $request->file('document_file');

                $extenstion = $file->getClientOriginalExtension();

                $filename = $image_name . '.' . $extenstion;

                $uploadedPath = $modules['public_folder'];
                $sub_folder_path = Documents::$folderPath;
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
                $update['folder_path'] = $filename;
                $update['file_mime_type'] = $extenstion;
            }
            $updateDocument->update($update);
            DB::commit();

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
            $dataDelete = Documents::findOrFail($id);
            if ($dataDelete) {
                unset($dataDelete->folder_path);

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
