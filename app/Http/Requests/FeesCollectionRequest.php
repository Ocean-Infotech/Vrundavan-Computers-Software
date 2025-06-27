<?php

namespace App\Http\Requests;

use App\Models\Admission;
use App\Models\CourceRegistration;
use App\Models\Master\MasterCourse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FeesCollectionRequest extends FormRequest
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
        $id = $request->route('fees-collection') ?? 0;
        $rules = [
            'student_id' => [
                'required',
                Rule::exists((new Admission())->getTable(), 'id')
            ],

            'admission_id' => [
                'required',
                Rule::exists((new CourceRegistration())->getTable(), 'id')
            ],

            'course_id' => [
                'required',
                Rule::exists((new MasterCourse())->getTable(), 'id')
            ],

            'student_name' => [
                'required',
            ],

            'year_semester' => [
                'required',
            ],
            //  'password' => [
            //     'required',
            // ],
            'date' => [
                'required',
            ],

            'fees' => [
                'required',
            ],

            'mode' => [
                'required',
            ],

            'upi_id' => [
                'nullable',
            ],

            'cheque_no' => [
                'nullable',
            ],

            'return_reason' => [
                'nullable',
            ],
        ];
        // dd("L-33", $rules, $id, $request->all(), $request->route('master-city'), $request->route()->parameters());
        return $rules;
    }
}
