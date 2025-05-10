<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class SliderRequest extends FormRequest
{
    /**
    * Determine if the user is authorized to make this request.
    */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(Request $request)
    {
        $id = !empty($request->route('sliders')) ? $request->route('sliders') : 0;
        if(!$id){
            $id = $request?->id;
        }
        $image = 'required|image|mimes:jpeg,jpg,png|max:1024';
        if($id){
            $image = 'nullable|image|mimes:jpeg,jpg,png|max:1024';
        }
        $rule = [
            'slider_type' => 'required',
            'redirect_url' => 'required',
            'video_url' => 'nullable',
            'image' => $image,
            'status' => 'required|in:active,inactive',
        ];
        return $rule;
    }
}
