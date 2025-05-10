<?php

namespace App\Http\Controllers\admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\SliderRequest;
use App\Models\Sliders;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\Facades\DataTables;

class SliderController extends Controller
{
    public $modules = [];
    public function __construct()
    {
        $this->modules = [
            'title' => 'Slider',
            'folder_path' => 'slider',
            'route' => 'sliders',
            'table_name' => (new Sliders())->getTable(),
            'permisstion_prefix' => 'sliders',
            'public_folder' => public_path(Sliders::$folderPath),
        ];

        $this->middleware('permission:' . $this->modules['permisstion_prefix'] . '-list', ['only' => ['index', 'show']]);
        $this->middleware('permission:' . $this->modules['permisstion_prefix'] . '-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:' . $this->modules['permisstion_prefix'] . '-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:' . $this->modules['permisstion_prefix'] . '-delete', ['only' => ['destroy']]);

        View::share('slider_type', (new Sliders())->getSliderTypes());
    }

    public function index(Request $request)
    {
        $modules = $this->modules;
        View::share('modules', $modules);
        try {
            $columns = [
                // (object)[ 'data' => "id", 'name' => 'id', 'td_label' => 'Id' ],
                (object)['data' => "slider_type", 'name' => 'slider_type', 'td_label' => 'Slider Type'],
                (object)['data' => "show_image", 'name' => 'show_image', 'td_label' => 'Image / Video'],
                (object)['data' => "status", 'name' => 'status', 'td_label' => 'Status', 'className' =>  'w-5 text-center'],
                (object)['data' => "action", 'name' => 'action', 'td_label' => 'Action', 'orderable' => false, 'searchable' => false, 'className' =>  'w-10 text-center'],
            ];
            View::share("columns", $columns);

            $data = [];

            if ($request->ajax()) {
                // dd($request->all());
                $data = Sliders::select('*');
                $editPermisstion = (Auth::user()->hasDirectPermission($modules["permisstion_prefix"] . '-edit')) ? true : false;
                $deletePermisstion = (Auth::user()->hasDirectPermission($modules["permisstion_prefix"] . '-delete')) ? true : false;
                // $editPermisstion = true; $deletePermisstion = true;

                $returnData = DataTables::of($data)
                    ->addIndexColumn()
                    ->filter(function ($query) use ($request) {
                        if ($request->has('filter_by_slider_type') && $request->filter_by_slider_type) {
                            $query->where('slider_type', $request->filter_by_slider_type);
                        }
                        // if ($request->has('search')) {
                        //     $query->where('name', 'like',"%".$request->search."%");
                        //     $query->orWhere('short_name', 'like',"%".$request->search."%");
                        // }
                    })
                    ->editColumn('slider_type', function ($row)  use ($modules) {
                        return (new Sliders())->getSliderTypes($row?->slider_type);
                    })
                    ->addColumn('show_image', function ($row)  use ($modules) {
                        if ($row->video_url && in_array($row->slider_type, ["dashboard_video"])) {
                            return '<a href="' . $row->video_url . '" target="_blank"> View </a>';
                        } else if ($row?->image_url) {
                            return '<img src="' . $row?->image_url . '"  class="text-center"  width="120px" />';
                        }
                        return "N/F";
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
                        if ($editPermisstion || $deletePermisstion) {
                            if ($editPermisstion) {
                                $btn .= '<div class="edit"><a href="' . route($modules["route"] . ".edit", [$row["id"]]) . '" class="btn btn-info btn-icon mr-2"><i class="bx bx-edit-alt"></i></a></div>';
                            }
                            if ($deletePermisstion) {
                                $btn .= '<div class="remove"><a href="javascript:void(0)" data-id="' . $row->id . '" data-did="' . route($modules["route"] . ".destroy", [$row["id"]]) . '" class="btn btn-danger btn-icon deletebutton"><i class="bx bx-trash"></i></i></a></div>';
                            }
                        } else {
                            $btn .= "-";
                        }
                        $btn .= '</div>';
                        return $btn;
                    })
                    ->rawColumns(['slider_type', 'show_image', 'status', 'action'])
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

    public function store(SliderRequest $request)
    {
        $modules = $this->modules;
        View::share('modules', $modules);

        try {
            //    return
            $input = $request->all();
            $slider_type = isset($request->slider_type) ? $request->slider_type : 'slider image';
            $filename = null;

            if ($request->hasFile('image')) {
                $image_name = Helper::makeSlug($request->slider_type) . '-' . date('Ymd-His');

                $file = $request->file('image');
                $extenstion = $file->getClientOriginalExtension();
                $filename = $image_name . '.' . $extenstion;

                $uploadedPath = $modules['public_folder'];
                $sub_folder_path = Sliders::$folderPath;
                if ($file->move($uploadedPath, $filename)) {
                    $uploadedImage = $sub_folder_path . $filename;
                    $webP = Helper::existingImageConverToWebp($extenstion, $uploadedPath, $filename, $image_name, 80, true);
                    if ($webP) {
                        $uploadedImage = $sub_folder_path . $webP;
                    }
                    $filename = $uploadedImage;
                }
            }
            if (in_array($slider_type, ["dashboard_video"])) {
                $input['video_url'] = $request->video_url;
            } else {
                $input['image'] = $filename;
            }
            //    return $input;
            $input['created_by'] = Auth::user()->id;
            Sliders::create($input);

            return Redirect::route($modules['route'] . '.index')->withSuccess($modules['title'] . ' create successfully');
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        try {
            $modules = $this->modules;
            View::share('modules', $modules);

            $edit = Sliders::findOrFail($id);
            return view('admin.module.' . $modules['folder_path'] . '.form', compact('modules', 'edit'));
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage())->withInput();
        }
    }


    public function update(Request $request, string $id)
    {
        $modules = $this->modules;
        View::share('modules', $modules);
        try {

            $update = $request->all();
            $slider_type = isset($request->slider_type) ? $request->slider_type : 'slider image';

            $updateSlider = Sliders::findOrFail($id);
            $updateSlider->slider_type = $request?->slider_type;
            $updateSlider->redirect_url = $request?->redirect_url;
            $updateSlider->status = $request?->status;
            $updateSlider->updated_by = Auth::user()->id;
            $filename = $updateSlider->image;
            if ($request->hasFile('image')) {
                if ($updateSlider->image && file_exists(public_path($updateSlider->image))) {
                    $full_path = public_path($updateSlider->image);
                    unset($full_path);
                }

                $image_name = Helper::makeSlug($request->slider_type) . '-' . date('Ymd-His');

                $file = $request->file('image');
                $extenstion = $file->getClientOriginalExtension();
                $filename = $image_name . '.' . $extenstion;

                $uploadedPath = $modules['public_folder'];
                $sub_folder_path = Sliders::$folderPath;
                if ($file->move($uploadedPath, $filename)) {
                    $uploadedImage = $sub_folder_path . $filename;
                    $webP = Helper::existingImageConverToWebp($extenstion, $uploadedPath, $filename, $image_name, 80, true);
                    if ($webP) {
                        $uploadedImage = $sub_folder_path . $webP;
                    }
                    $filename = $uploadedImage;
                }
            }
            if (in_array($slider_type, ["dashboard_video"])) {
                $updateSlider->video_url = $request->video_url;
            } else {
                $updateSlider->image = $filename;
            }
            // return $updateSlider;
            $updateSlider->save();

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
            $dataDelete = Sliders::findOrFail($id);
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
