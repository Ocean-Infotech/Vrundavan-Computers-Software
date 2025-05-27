<?php

namespace App\Http\Requests;

use App\Models\Admission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Attedance;

class AttedanceRequest extends FormRequest
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
        $id = $request->route('attedance') ?? 0;
        $rules = [
            'admission_id' => [
                'required',
                Rule::exists((new Admission())->getTable(), 'id')
            ],
            'date' => [
                'required'
            ],
            'in_time' => [
                'nullable'
            ],
            'out_time' => [
                'nullable'
            ],
            'leave' => [
                'nullable'
            ],
        ];
        // dd("L-33", $rules, $id, $request->all(), $request->route('master-city'), $request->route()->parameters());
        return $rules;
    }

    public function messages()
    {
        return [
            'admission_id.required' => 'The Admission is required.',
            'date.required' => 'The Date is required.',
        ];
    }
}
