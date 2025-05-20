<?php

namespace App\Http\Requests;

use App\Models\Master\MasterCourse;
use App\Models\Master\MasterSemester;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MasterSemesterRequest extends FormRequest
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
        $id = $request->route('master_batch') ?? 0;
        $rules = [
            'course_id' => [
                'required',
                Rule::exists((new MasterCourse())->getTable(), 'id')
            ],
            
            // 'name' => 'required|string|max:255|unique:' . (new MasterCity())->getTable() . ',name',
            'semester' => [
                'required',
                Rule::unique((new MasterSemester())->getTable())->where(function ($query) use ($request) {
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
            'semester.required' => 'The Semester is required.',
            
        ];
    }
}
