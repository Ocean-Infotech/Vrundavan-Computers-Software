<?php

namespace App\Http\Controllers\admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\RedemptionRequest;
use App\Models\Redemption;
use App\Models\RedemptionItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\DataTables;

class RedemptionController extends Controller
{
    public $modules = [];
    public function __construct()
    {
        $this->modules = [
            'title' => 'Redemption',
            'folder_path' => 'redemption',
            'route' => 'redemption',
            'table_name' => (new Redemption())->getTable(),
            'permisstion_prefix' => 'redemption',
            // 'public_folder' => public_path(Redemption::$folderPath),
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
                // (object)[ 'data' => "id", 'name' => 'id', 'td_label' => 'Id' ],
                (object)['data' => "created_at", 'name' => 'created_at', 'td_label' => 'Created Date'],
                (object)['data' => "label", 'name' => 'label', 'td_label' => 'Label'],
                (object)['data' => "quantity", 'name' => 'quantity', 'td_label' => 'Quantity'],
                (object)['data' => "collect_points", 'name' => 'collect_points', 'td_label' => 'collect points'],
                (object)['data' => "status", 'name' => 'status', 'td_label' => 'Status', 'className' =>  'w-5 text-center'],
                (object)['data' => "action", 'name' => 'action', 'td_label' => 'Action', 'orderable' => false, 'searchable' => false, 'className' =>  'w-10 text-center'],
            ];
            View::share("columns", $columns);

            $data = [];

            if ($request->ajax()) {
                // dd($request->all());
                $data = Redemption::select('*');
                $editPermisstion = (Auth::user()->can($modules["permisstion_prefix"] . '-edit')) ? true : false;
                $deletePermisstion = (Auth::user()->can($modules["permisstion_prefix"] . '-delete')) ? true : false;
                $editPermisstion = false;
                // $deletePermisstion = true;

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
                        $btn .= '<div class="show"><a href="' . route($modules["route"] . ".show", [$row["id"]]) . '" class="btn btn-info mr-2">View</a></div>';
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

    public function store(RedemptionRequest $request)
    {
        $modules = $this->modules;
        View::share('modules', $modules);

        DB::beginTransaction();
        try {
            // return
            $input = $request->all();
            $input['label'] = "QR-" . date("YmdHis");
            $input['created_by'] = Auth::user()->id;
            // return $input;
            $redemption = Redemption::create($input);
            DB::commit();
            if ($redemption) {
                $redemption = Redemption::find($redemption?->id);
                for ($i = 0; $i < (int)$redemption?->quantity; $i++) {
                    $redemptionItemInput = [];
                    $redemptionItemInput['redemption_id'] = $redemption?->id;
                    $redemptionItemInput['redemption_code_label'] = $input['label'] . Helper::getTableWiseGetAutoIncrementId((new RedemptionItem())->getTable()) . "-" . sprintf("%04d", rand(0, 9999));
                    // $redemptionItemInput['redemption_code_label'] = "QR-".date("YmdHis")."-".sprintf("%04d", rand(0, 9999));
                    $redemptionItemInput['collect_points'] = $redemption?->collect_points;
                    $redemptionItemInput['status'] = $redemption?->status;
                    $redemptionItemInput['created_by'] = Auth::user()->id;
                    RedemptionItem::create($redemptionItemInput);
                }
            }
            return Redirect::route($modules['route'] . '.index')->withSuccess($modules['title'] . ' create successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $e->getMessage();
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage())->withInput();
        }
    }


    public function show(Request $request, string $id)
    {
        $modules = $this->modules;
        View::share('modules', $modules);
        try {
            $show = Redemption::with(['redemption_items'])->findOrFail($id);
            return view('admin.module.' . $modules['folder_path'] . '.show', compact('show'));
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage())->withInput();
        }
    }

    public function edit(string $id)
    {
        return Redirect::route("redemption.create")->with('info', 'Redemption can/t edited.');
        try {
            $modules = $this->modules;
            View::share('modules', $modules);

            $edit = Redemption::findOrFail($id);
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

            $updateSlider = Redemption::findOrFail($id);
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
                $sub_folder_path = Redemption::$folderPath;
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
            $dataDelete = Redemption::findOrFail($id);
            if ($dataDelete) {
                unset($dataDelete->image);
                $dataDelete->deleted_by = Auth::user()?->id;
                $dataDelete->deleted_at = Carbon::now();
                $dataDelete->save();

                RedemptionItem::where('redemption_id', $dataDelete?->id)->update([
                    'deleted_by' => Auth::user()?->id,
                    'deleted_at' => Carbon::now(),
                ]);

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

    public function redemptionItemStatuUpdate(Request $request)
    {
        $modules = $this->modules;
        View::share('modules', $modules);
        $isAjax = ($request->ajax()) ? true : false;

        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:' . ((new RedemptionItem())->getTable()) . ',id',
                'status' => 'required|in:active,inactive',
            ]);

            if ($validator->fails()) {
                if ($isAjax) {
                    return $this->sendError('Validation Error.', $validator->errors()->first(), $validator->errors(), 200);
                }
                return Redirect::back()->withErrors($validator->errors());
            }
            // return $request->all();

            $editStatus = RedemptionItem::findOrFail($request?->id);
            if ($editStatus) {
                $editStatus->status = $request?->status;
                $editStatus->save();

                if ($isAjax) {
                    return $this->sendResponse([], "Status Update successfully.");
                }
                return Redirect::back()->with('success', 'Status Update successfully.');
            }
            return Redirect::back()->withErrors('something went wrong please try again later');
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage())->withInput();
        }
    }
}
