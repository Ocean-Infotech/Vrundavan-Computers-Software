<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Slider;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class SliderController extends Controller
{
    // Add Slider
    public function slider_add(Request $request)
    {
        try {
            // Validation
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

            // Store image
            $imagePath = $request->file('image')->store('sliders', 'public');
            // Create slider record
            $slider = Slider::create([
                'title' => $request->title,
                'image' => $imagePath,
                'link'  => $request->link,
            ]);

            // Hide timestamps in the response
            unset($slider['created_at'], $slider['updated_at']);

            return $this->sendResponse($slider, 'Slider added successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', $e->getMessage(), [], 500);
        }
    }


    // List Sliders
    public function slider_list()
    {
        $sliders = Slider::orderBy('id', 'desc')->get()->map(function ($slider) {
            unset($slider->created_by, $slider->updated_by, $slider->deleted_by, $slider->created_at, $slider->updated_at);

            $slider->image = url('storage/' . $slider->image);
            return $slider;
        });

        return response()->json(['status' => true, 'data' => $sliders]);
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

            // Handle image replacement if provided
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
            return response()->json(['status' => false, 'message' => 'Slider not found'], 404);
        }

        if ($slider->image && Storage::disk('public')->exists($slider->image)) {
            Storage::disk('public')->delete($slider->image);
        }

        $slider->delete();

        return response()->json(['status' => true, 'message' => 'Slider deleted successfully']);
    }
}
