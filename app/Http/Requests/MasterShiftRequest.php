<?php

namespace App\Http\Requests;

use App\Models\Master\MasterBatch;
use App\Models\Master\MasterCourse;
use App\Models\master\MasterShift;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MasterShiftRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(Request $request): array
    {
        $id = $request->route('shift') ?? 0;
        $rules = [
            'course_id' => [
                'required',
                Rule::exists((new MasterCourse())->getTable(), 'id')
            ],

            'batch_id' => [
                'required',
                Rule::exists((new MasterBatch())->getTable(), 'id')
            ],

            'class_id' => [
                'required',
                //Rule::exists((new MasterBatch())->getTable(), 'id')
            ],
            
            // 'name' => 'required|string|max:255|unique:' . (new MasterCity())->getTable() . ',name',
            'from_time' => [
                'required',
                Rule::unique((new MasterShift())->getTable())->where(function ($query) use ($request) {
                    return $query->where('course_id', $request->course_id)
                    ->where('batch_id', $request->batch_id)
                    ->where('class_id', $request->class_id);
                })->ignore($id),
            ],

            'to_time' => [
                'required',
                Rule::unique((new MasterShift())->getTable())->where(function ($query) use ($request) {
                    return $query->where('course_id', $request->course_id)
                    ->where('batch_id', $request->batch_id)
                    ->where('class_id', $request->class_id);
                })->ignore($id),
            ],
        ];
        // dd("L-33", $rules, $id, $request->all(), $request->route('master-city'), $request->route()->parameters());
        return $rules;
    }

    public function messages()
    {
        return [
            'course_id.required' => 'The Course Name is required.',
            'batch_id.required' => 'The Batch Name is required.',
            'class_id.required' => 'The Batch Name is required.',
            'from_time.required' => 'The Batch Name is required.',
            'to_time.required' => 'The Batch Name is required.',
            
        ];
    }
}
