<?php

namespace App\Http\Requests;

use App\Models\Master\MasterCourse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MasterCourceRequest extends FormRequest
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
        $id = $request->route('course') ?? 0;
        $rules = [
            'course_name' => [
                'required',
                'max:255',
                Rule::unique((new MasterCourse())->getTable(), 'course_name')->ignore($id),
            ],
            'course_fees' => 'required|max:255',
            'course_year' => 'required|max:255',
            'semester' => 'required|max:255',
        ];
        return $rules;
    }
}
