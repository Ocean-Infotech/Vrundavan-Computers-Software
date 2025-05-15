<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Admission;

class AdmissionRequest extends FormRequest
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
        $id = $request->route('admission') ?? 0;
        $rules = [
            'aadhar_card_no' => [
                'required',
                Rule::unique((new Admission())->getTable(), 'aadhar_card_no')->ignore($id)
                // Rule::unique((new Admission())->getTable())->where(function ($query) use ($request) {
                //     return $query->where('aadhar_card_no', $request->aadhar_card_no);
                // })->ignore($id),
            ],

            'first_name' => [
                'required'
            ],
            
            'last_name' => [
                'required'
            ],

            'father_name' => [
                'required'
            ],
            
            'mother_name' => [
                'required'
            ],
            
            'temporary_address' => [
                'required'
            ],

            'permanent_address' => [
                'required'
            ],
            
            'mobile_no' => [
                'required',
            ],
            
            'parent_mobile_no' => [
                'nullable',
            ],

            'other_mobile_no' => [
                'nullable',
            ],

            'whatsapp_no' => [
                'nullable',
            ],
            
            'cast' => [
                'nullable'
            ],
            
            'occupation' => [
                'nullable'
            ],
            
            'date_of_birth' => [
                'required'
            ],
            
            'email_address' => [
                'required',
            ],
            'gender' => [
                'required'
            ],
            'category' => [
                'required'
            ],
            'enrolment_no' => [
                'nullable'
            ],
            'spid' => [
                'nullable'
            ],
            'apaar_id_abc_id' => [
                'nullable'
            ],
            
            'education' => 'nullable|array|min:1',
            'education.*' => 'nullable|string',

            'percentage_cgpa' => 'required|array|min:1',
            'percentage_cgpa.*' => 'required|string|max:255',

            'seat_no_nrollment_no' => 'nullable|array',
            'seat_no_nrollment_no.*' => 'nullable|numeric',

            'board_university' => 'nullable|array',
            'board_university.*' => 'nullable|string',

            'passing_year' => 'nullable|array',
            'passing_year.*' => 'nullable|string',

            'school_name_college_name' => 'nullable|array',
            'school_name_college_name.*' => 'nullable|string',
        ];
        // dd("L-33", $rules, $id, $request->all(), $request->route('master-city'), $request->route()->parameters());
        return $rules;
    }

    public function messages()
    {
        return [
            'aadhar_card_no.required' => 'The Aadhar Card No. is required.',
            'first_name.required' => 'The First Name is required.',
            'last_name.required' => 'The Last Name is required.',
            'father_name.required' => 'The Father Name is required.',
            'mother_name.required' => 'The Mother Name is required.',
            'temporary_address.required' => 'The Temporary Address is required.',
            'permanent_address.required' => 'The Permanent Address is required.',
            'mobile_no.required' => 'The Mobile No. is required.',
            'date_of_birth.required' => 'The Date of Birth is required.',
            'email_address.required' => 'The Email Address is required.',
            'gender.required' => 'The Gender is required.',
            'category.required' => 'The Category is required.',
        ];
    }
}
