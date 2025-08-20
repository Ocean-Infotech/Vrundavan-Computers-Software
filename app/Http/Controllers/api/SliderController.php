<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Slider;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class SliderController extends Controller
{
    public $modules = [];
    public function __construct()
    {
        $this->modules = [
            'title' => 'Slider',
            'folder_path' => 'software.module.banner',
            'route' => 'banner',
            'table_name' => (new Slider())->getTable(),
            'permission_prefix' => 'banner',
        ];
    }
    public function index()
    {
        $sliders = Slider::orderBy('id', 'desc')->get()->map(function ($slider) {
            unset($slider->created_by, $slider->updated_by, $slider->deleted_by);
            $slider->image = url('storage/' . $slider->image);
            return $slider;
        });

        return response()->json(['status' => true, 'data' => $sliders]);
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

            // Save to public/sliders
            $fileName = time() . '_' . $request->file('image')->getClientOriginalName();
            $request->file('image')->move(public_path('sliders'), $fileName);
            $imagePath = 'sliders/' . $fileName;

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
        $sliders = Slider::where('status', 'active')
            ->orderBy('id', 'desc')
            ->get();

        if ($sliders->isEmpty()) {
            return $this->sendError('No sliders found.', [], [], 404);
        }

        $sliders = $sliders->map(function ($slider) {
            unset(
                $slider->created_by,
                $slider->updated_by,
                $slider->deleted_by,
                $slider->created_at,
                $slider->updated_at
            );
            $slider->image = url($slider->image);
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
                // delete old file
                if ($slider->image && file_exists(public_path($slider->image))) {
                    unlink(public_path($slider->image));
                }

                $fileName = time() . '_' . $request->file('image')->getClientOriginalName();
                $request->file('image')->move(public_path('sliders'), $fileName);
                $slider->image = 'sliders/' . $fileName;
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
