<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Slider;
use App\Helpers\Helper;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

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

        // Example permissions (adjust as per your project)
        $modules['permission_list'] = Helper::directCan($modules['permission_prefix'] . '-list');
        $modules['permission_add']  = Helper::directCan($modules['permission_prefix'] . '-create');
        $modules['permission_edit'] = Helper::directCan($modules['permission_prefix'] . '-edit');
        $modules['permission_delete'] = Helper::directCan($modules['permission_prefix'] . '-delete');

        if (!$modules['permission_list']) {
            if ($request->ajax()) {
                return response()->json(['status' => false, 'message' => 'User does not have the right permissions.'], 403);
            }
            abort(403, 'User does not have the right permissions.');
        }

        View::share('modules', $modules);

        try {
            $data = Slider::query();

            if ($request->ajax()) {
                return Datatables::of($data)
                    ->addIndexColumn()

                    ->editColumn('image', function ($row) {
                        return '<img src="' . url('storage/' . $row->image) . '" width="100"/>';
                    })

                    ->addColumn('action', function ($row) use ($modules) {
                        $btn = '';
                        if ($modules['permission_edit']) {
                            $btn .= '<a href="' . route($modules["route"] . ".edit", [$row->id]) . '" class="btn btn-light btn-icon mx-1"><i class="fa-solid fa-pen-to-square"></i></a>';
                        }
                        if ($modules['permission_delete']) {
                            $btn .= '<a href="javascript:void(0)" data-id="' . $row->id . '" data-did="' . route($modules["route"] . ".destroy", [$row->id]) . '" class="btn btn-danger btn-icon deletebutton mx-1"><i class="fa-solid fa-trash"></i></a>';
                        }
                        return $btn ?: '-';
                    })

                    ->rawColumns(['image', 'action'])
                    ->make(true);
            }

            return view($modules['folder_path'] . '.index', compact('data'));
        } catch (\Exception $e) {
            return Redirect::route('dashboard')->withErrors($e->getMessage());
        }
    }


    // Add Slider
    public function slider_add(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'title' => 'required|string|max:255',
                    'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                    'link'  => 'nullable|string|max:255',
                ]
            );

            if ($validator->fails()) {
                return $this->sendError(
                    $validator->errors()->first(),
                    $validator->errors(),
                    [],
                    422
                );
            }

            $imagePath = $request->file('image')->store('sliders', 'public');

            $slider = Slider::create([
                'title' => $request->title,
                'image' => $imagePath,
                'link'  => $request->link,
            ]);

            unset($slider['created_at'], $slider['updated_at']);

            return $this->sendResponse($slider, 'Slider added successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', $e->getMessage(), [], 500);
        }
    }

    // List Sliders
    public function slider_list()
    {
        $sliders = Slider::orderBy('id', 'desc')->get();

        if ($sliders->isEmpty()) {
            return $this->sendError('No sliders found.', [], [], 404);
        }

        $sliders = $sliders->map(function ($slider) {
            unset($slider->created_by, $slider->updated_by, $slider->deleted_by, $slider->created_at, $slider->updated_at);
            $slider->image = asset('storage/' . $slider->image);
            return $slider;
        });

        return $this->sendResponse($sliders, 'Sliders retrieved successfully.');
    }

    // Update Slider
    public function slider_update(Request $request)
    {
        try {
            $id = $request->id;

            if (!$id) {
                return $this->sendError('Slider ID is required.', [], [], 422);
            }

            $slider = Slider::find($id);

            if (!$slider) {
                return $this->sendError('Slider not found.', [], [], 404);
            }

            $validator = Validator::make(
                $request->all(),
                [
                    'title' => 'required|string|max:255',
                    'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                    'link'  => 'nullable|string|max:255',
                ]
            );

            if ($validator->fails()) {
                return $this->sendError(
                    $validator->errors()->first(),
                    $validator->errors(),
                    [],
                    422
                );
            }

            if ($request->hasFile('image')) {
                if ($slider->image && Storage::disk('public')->exists($slider->image)) {
                    Storage::disk('public')->delete($slider->image);
                }
                $slider->image = $request->file('image')->store('sliders', 'public');
            }

            $slider->title = $request->title;
            $slider->link  = $request->link;
            $slider->save();

            unset($slider['created_at'], $slider['updated_at'], $slider['created_by'], $slider['updated_by'], $slider['deleted_by']);

            return $this->sendResponse($slider, 'Slider updated successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', $e->getMessage(), [], 500);
        }
    }

    // Delete Slider
    public function slider_destroy($id)
    {
        $slider = Slider::find($id);

        if (!$slider) {
            return $this->sendError('Slider not found.', [], [], 404);
        }

        if ($slider->image && Storage::disk('public')->exists($slider->image)) {
            Storage::disk('public')->delete($slider->image);
        }

        $slider->delete();

        return $this->sendResponse(null, 'Slider deleted successfully.');
    }
}
