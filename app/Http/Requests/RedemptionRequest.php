<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class RedemptionRequest extends FormRequest
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
        // dd("RedemptionRequest 20",$request->all());
        $id = !empty($request->route('redemption')) ? $request->route('redemption') : 0;
        if(!$id){
            $id = $request?->id;
        }
        $rule = [
            'quantity' => 'required|numeric|min:1',
            'collect_points' => 'required|numeric|min:1',
            'status' => 'nullable|in:active,inactive',
        ];
        // dd("RedemptionRequest 20",$request->all(), $rule);
        return $rule;
    }
}
