<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MasterHolidayRequest extends FormRequest
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
     */
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'type' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:1000',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Holiday name is required.',
            'from_date.required' => 'Start date is required.',
            'to_date.required' => 'End date is required.',
            'to_date.after_or_equal' => 'End date must be greater than or equal to start date.',
        ];
    }
}
