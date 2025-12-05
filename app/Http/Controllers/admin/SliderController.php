<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Slider;
use App\Helpers\Helper;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SliderController extends Controller
{
    public $modules = [];

    public function __construct()
    {
        $this->modules = [
            'title' => 'Slider',
            'folder_path' => 'software.module.slider',
            'route' => 'slider',
            'table_name' => (new Slider())->getTable(),
            'permission_prefix' => 'slider',
        ];
    }

    public function index(Request $request)
    {
        $modules = $this->modules;
        $modules['login_user_role'] = Helper::getLoginUserRole();
        $modules['permission_list'] = Helper::directCan($modules['permission_prefix'] . '-list');
        $modules['permission_add'] = Helper::directCan($modules['permission_prefix'] . '-create');
        $modules['permission_edit'] = Helper::directCan($modules['permission_prefix'] . '-edit');
        $modules['permission_delete'] = Helper::directCan($modules['permission_prefix'] . '-delete');

        if (!$modules['permission_list']) {
            if ($request->ajax()) {
                return $this->sendError('User does not have the right permissions.', [], [], 403);
            }
            abort(403, 'User does not have the right permissions.');
        }

        $columns = [
            (object)['data' => "title", 'name' => 'title', 'td_label' => 'Title'],
            (object)['data' => "image", 'name' => 'image', 'td_label' => 'Image', 'orderable' => false, 'searchable' => false],
            (object)['data' => "link", 'name' => 'link', 'td_label' => 'Link'],
            (object)['data' => "status", 'name' => 'status', 'td_label' => 'Status', 'className' =>  'w-5 text-center', 'width' => '10%'],
            (object)['data' => "action", 'name' => 'action', 'td_label' => 'Action', 'orderable' => false, 'searchable' => false, 'className' => 'w-10 text-center'],
        ];

        View::share("modules", $modules);
        View::share("columns", $columns);

        if ($request->ajax()) {
            $data = Slider::select('*')->withTrashed();

            return Datatables::of($data)
                ->addIndexColumn()
                ->editColumn('image', function ($row) {
                    return '<img src="' . asset($row->image) . '" alt="' . e($row->title) . '" width="80">';
                })
                ->editColumn('link', function ($row) {
                    return $row->link ? '<a href="' . e($row->link) . '" target="_blank">' . e($row->link) . '</a>' : '';
                })
                ->editColumn('status', function ($row) use ($modules) {
                    if ($row->status === "active") {
                        return '<button class="btn btn-success btn-sm dropdown-toggle" data-bs-toggle="dropdown">' . ucfirst($row->status) . '</button>
                                <ul class="dropdown-menu">
                                    <li><a href="javascript:void(0)" class="dropdown-item update-status" data-url="' . route($modules["route"] . '.status-update') . '" data-id="' . $row->id . '" data-update_status="inactive">Inactive</a></li>
                                </ul>';
                    } elseif ($row->status === "inactive") {
                        return '<button class="btn btn-danger btn-sm dropdown-toggle" data-bs-toggle="dropdown">' . ucfirst($row->status) . '</button>
                                <ul class="dropdown-menu">
                                    <li><a href="javascript:void(0)" class="dropdown-item update-status" data-url="' . route($modules["route"] . '.status-update') . '" data-id="' . $row->id . '" data-update_status="active">Active</a></li>
                                </ul>';
                    }
                    return '';
                })
                ->addColumn('action', function ($row) use ($modules) {
                    $btn = '';
                    if (!$row->deleted_at) {
                        if ($modules['permission_edit']) {
                            $btn .= '<a href="' . route($modules["route"] . ".edit", [$row->id]) . '" class="btn btn-light btn-icon mx-1"><i class="fa-solid fa-pen-to-square"></i></a>';
                        }
                        if ($modules['permission_delete']) {
                            $btn .= '<a href="javascript:void(0)" data-id="' . $row->id . '" data-did="' . route($modules["route"] . ".destroy", [$row->id]) . '" class="btn btn-danger btn-icon deletebutton mx-1"><i class="fa-solid fa-trash"></i></a>';
                        }
                        $btn .= '<a href="javascript:void(0)"
                data-did="' . route($modules["route"] . ".permanent-delete", [$row->id]) . '"
                class="btn btn-danger btn-icon permanentDeleteButton mx-1"
                title="Permanent Delete">
                <i class="fa-solid fa-ban"></i>
            </a>';
                    } else {
                        $btn .= '<a href="javascript:void(0)" data-restore="' . route($modules["route"] . ".restore", ['id' => $row->id]) . '" class="btn btn-light mx-1 record-restore" title="Restore Data"><i class="ti ti-history"></i> Restore</a>';
                    }
                    return $btn ?: '-';
                })
                ->rawColumns(['action', 'image', 'link', 'status'])
                ->make(true);
        }

        return view($modules['folder_path'] . '.index');
    }

    public function create()
    {
        $modules = $this->modules;
        View::share('modules', $modules);
        return view($modules['folder_path'] . '.form', compact('modules'));
    }

    public function store(Request $request)
    {
        $modules = $this->modules;
        View::share('modules', $modules);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'link'  => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator->messages())->withInput();
        }

        try {
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('uploads/sliders'), $imageName);

            Slider::create([
                'title' => $request->title,
                'image' => 'uploads/sliders/' . $imageName,
                'link'  => $request->link,
                'created_by' => Auth::user()->id,
            ]);


            return Redirect::route($modules['route'] . '.index')->withSuccess($modules['title'] . ' created successfully.');
        } catch (\Exception $e) {
            return Redirect::route('dashboard')->withErrors($e->getMessage());
        }
    }

    public function edit(string $id)
    {
        $modules = $this->modules;
        View::share('modules', $modules);
        $edit = Slider::findOrFail($id);
        return view($modules['folder_path'] . '.form', compact('modules', 'edit'));
    }

    public function update(Request $request, string $id)
    {
        $modules = $this->modules;
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'link'  => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator->messages())->withInput();
        }

        try {
            $slider = Slider::findOrFail($id);

            if ($request->hasFile('image')) {
                // delete old file if exists
                if ($slider->image && file_exists(public_path($slider->image))) {
                    unlink(public_path($slider->image));
                }

                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->move(public_path('uploads/sliders'), $imageName);

                $slider->image = 'uploads/sliders/' . $imageName;
            }


            $slider->title = $request->title;
            $slider->link = $request->link;
            $slider->updated_by = Auth::user()->id;
            $slider->save();

            return Redirect::route($modules['route'] . '.index')->withSuccess($modules['title'] . ' updated successfully.');
        } catch (\Exception $e) {
            return Redirect::route('dashboard')->withErrors($e->getMessage());
        }
    }
    public function update_status(Request $request)
    {
        $isAjax = $request->ajax();

        $modules = $this->modules;

        $validator = Validator::make($request->all(), [
            'id' => ['required', Rule::exists($modules['table_name'], 'id')],
            'update_status' => ['required', 'in:active,inactive']
        ]);

        if ($validator->fails() && $isAjax) {
            return $this->sendError($validator->messages()->first(), $validator->messages(), [], 401);
        }

        try {
            $slider = Slider::withTrashed()->findOrFail($request->id);

            if ($slider) {
                $slider->status = $request->update_status;
                $slider->save();

                if ($isAjax) {
                    return $this->sendResponse($slider, $modules['title'] . ' status updated successfully.');
                }

                return Redirect::route($modules['route'] . '.index')
                    ->withSuccess($modules['title'] . ' status updated successfully.');
            }

            if ($isAjax) {
                return $this->sendError('Something went wrong. Please try again later.');
            }

            return Redirect::back()
                ->withErrors('Something went wrong. Please try again later.')
                ->withInput();
        } catch (\Exception $e) {
            if ($isAjax) {
                return $this->sendError($e->getMessage(), $e->getMessage(), [], 401);
            }

            return Redirect::route($modules['route'] . '.index')
                ->withErrors($e->getMessage());
        }
    }

    public function destroy(Request $request, string $id)
    {
        $modules = $this->modules;
        try {
            $slider = Slider::findOrFail($id);
            $slider->deleted_by = Auth::user()?->id;
            $slider->save();
            $slider->delete();

            return Redirect::route($modules['route'] . '.index')->withSuccess($modules['title'] . ' deleted successfully.');
        } catch (\Exception $e) {
            return Redirect::route('dashboard')->withErrors($e->getMessage());
        }
    }
    public function restore($id)
    {
        $modules = $this->modules;

        try {
            // Fetch soft deleted record
            $slider = Slider::withTrashed()->findOrFail($id);

            // 🔍 Check if Slider is used anywhere
            // (If not used anywhere, keep it false)
            $isUsed = false;

            if ($isUsed) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'This slider cannot be restored because it is used in other data.'
                ], 400);
            }

            // Restore slider
            $slider->restore();

            return response()->json([
                'status' => 'success',
                'message' => 'Slider restored successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function permanentDelete($id)
    {
        try {
            $modules = $this->modules;

            // Fetch soft-deleted record
            $slider = Slider::withTrashed()->findOrFail($id);

            // Delete image file if exists
            if ($slider->image && file_exists(public_path($slider->image))) {
                unlink(public_path($slider->image));
            }

            // Hard delete
            $slider->forceDelete();

            return response()->json([
                'success' => true,
                'message' => $modules['title'] . ' permanently deleted successfully.'
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
