<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Slider;
use Illuminate\Support\Facades\Validator;

/**
 * @group Slider
 *
 * APIs for managing sliders shown in the application.
 */
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

    /**
     * List all sliders (admin)
     *
     * Returns every slider in the system (active and inactive). Intended for internal admin use.
     *
     * @authenticated
     * @hideFromAPIDocumentation
     */
    public function index()
    {
        $sliders = Slider::orderBy('id', 'desc')->get()->map(function ($slider) {
            unset($slider->created_by, $slider->updated_by, $slider->deleted_by);
            $slider->image = url($slider->image); // public/uploads/sliders/... url
            return $slider;
        });

        return response()->json(['status' => true, 'data' => $sliders]);
    }

    /**
     * Add a new slider
     *
     * Uploads a new slider image with a title and an optional link. The image will be stored under
     * `uploads/sliders` and its full URL will be returned in the response.
     *
     * @authenticated
     *
     * @bodyParam title string required The slider title to display on the UI. Example: Summer Offers
     * @bodyParam image file required The image file (jpeg, png, jpg, gif or webp; max 2MB).
     * @bodyParam link string A URL to open when the slider is tapped. Example: https://example.com/offers
     *
     * @response 200 {
     *   "status": "true",
     *   "message": "Slider added successfully.",
     *   "data": {
     *     "id": 1,
     *     "title": "Summer Offers",
     *     "image": "uploads/sliders/1710300000_banner.webp",
     *     "link": "https://example.com/offers"
     *   }
     * }
     *
     * @response 422 scenario="Validation failed" {
     *   "status": "false",
     *   "message": "The title field is required.",
     *   "messages": {
     *     "title": [
     *       "The title field is required."
     *     ]
     *   }
     * }
     */
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

            $fileName = time() . '_' . $request->file('image')->getClientOriginalName();
            $request->file('image')->move(public_path('uploads/sliders'), $fileName);
            $imagePath = 'uploads/sliders/' . $fileName;

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

    /**
     * List active sliders
     *
     * Returns all active sliders in reverse creation order. Each slider includes the absolute image URL.
     *
     * @authenticated
     *
     * @response 200 {
     *   "status": "true",
     *   "message": "Sliders retrieved successfully.",
     *   "data": [
     *     {
     *       "id": 1,
     *       "title": "Summer Offers",
     *       "image": "https://example.com/uploads/sliders/1710300000_banner.webp",
     *       "link": "https://example.com/offers",
     *       "status": "active"
     *     }
     *   ]
     * }
     *
     * @response 404 scenario="No sliders" {
     *   "status": "false",
     *   "message": "No sliders found.",
     *   "data": []
     * }
     */
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
            $slider->image = asset($slider->image);
            return $slider;
        });

        return $this->sendResponse($sliders, 'Sliders retrieved successfully.');
    }

    /**
     * Update an existing slider
     *
     * Updates the title, link and (optionally) the image for a specific slider.
     * If a new image is uploaded, the previous image file will be deleted.
     *
     * @authenticated
     *
     * @bodyParam id integer required The ID of the slider to update. Example: 1
     * @bodyParam title string required The new title for the slider. Example: Updated Summer Offers
     * @bodyParam image file The new image file to replace the old one (jpeg, png, jpg, gif or webp; max 2MB).
     * @bodyParam link string The new URL to open when the slider is tapped. Example: https://example.com/new-offers
     *
     * @response 200 {
     *   "status": "true",
     *   "message": "Slider updated successfully.",
     *   "data": {
     *     "id": 1,
     *     "title": "Updated Summer Offers",
     *     "image": "uploads/sliders/1710301000_banner.webp",
     *     "link": "https://example.com/new-offers"
     *   }
     * }
     *
     * @response 404 scenario="Slider not found" {
     *   "status": "false",
     *   "message": "Slider not found.",
     *   "data": []
     * }
     */
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
                $request->file('image')->move(public_path('uploads/sliders'), $fileName);
                $slider->image = 'uploads/sliders/' . $fileName;
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

    /**
     * Delete a slider
     *
     * Deletes a slider by its ID and removes its image file from storage.
     *
     * @authenticated
     *
     * @urlParam id integer required The ID of the slider to delete. Example: 1
     *
     * @response 200 {
     *   "status": "true",
     *   "message": "Slider deleted successfully.",
     *   "data": null
     * }
     *
     * @response 404 scenario="Slider not found" {
     *   "status": "false",
     *   "message": "Slider not found.",
     *   "data": []
     * }
     */
    public function slider_destroy($id)
    {
        $slider = Slider::find($id);

        if (!$slider) {
            return $this->sendError('Slider not found.', [], [], 404);
        }

        if ($slider->image && file_exists(public_path($slider->image))) {
            unlink(public_path($slider->image));
        }

        $slider->delete();

        return $this->sendResponse(null, 'Slider deleted successfully.');
    }
}
