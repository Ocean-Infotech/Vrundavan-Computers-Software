<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Master\MasterBatch;
use App\Models\Master\MasterCourse;

class MasterBatchRequest extends FormRequest
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
        $id = $request->route('batch') ?? 0;
        $rules = [
            'course_id' => [
                'required',
                Rule::exists((new MasterCourse())->getTable(), 'id')
            ],

            // 'name' => 'required|string|max:255|unique:' . (new MasterCity())->getTable() . ',name',
            'batch_name' => [
                'required',
                Rule::unique((new MasterBatch())->getTable())->where(function ($query) use ($request) {
                    return $query->where('course_id', $request->course_id);
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
            'batch_name.required' => 'The Batch Name is required.',

        ];
    }
}
