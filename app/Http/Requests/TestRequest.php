<?php

namespace App\Http\Requests;

use App\Models\Master\MasterBatch;
use App\Models\Master\MasterCourse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Test;

class TestRequest extends FormRequest
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
        $id = $request->route('test') ?? 0;
        $rules = [
            'course_id' => [
                'required',
                Rule::exists((new MasterCourse())->getTable(), 'id')
            ],
            'batch_id' => [
                'required',
                Rule::exists((new MasterBatch())->getTable(), 'id')
            ],
            'semester' => [
                'required'
            ],
            'subject_name' => [
                'required'
            ],
            'test_type' => [
                'required',
                Rule::in(['Weekly', 'Full'])
            ],
            'unit_name' => [
                'required'
            ],
            'mark' => [
                'required'
            ],
            
            'date' => [
                'required',
                // Rule::unique((new Test())->getTable())->where(function ($query) use ($request) {
                //     return $query->where('course_id', $request->course_id)
                //     ->where('batch_id', $request->batch_id)
                //     ->where('subject_name', $request->subject_name);
                // })->ignore($id),
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
            'semester.required' => 'The Semester is required.',
            'subject_name.required' => 'The Subject Name is required.',
            'test_type.required' => 'The Test Type is required.',
            'unit_name.required' => 'The Unit Name is required.',
            'mark.required' => 'The Mark is required.',
            'date.required' => 'The Date is required.',
        ];
    }
}
