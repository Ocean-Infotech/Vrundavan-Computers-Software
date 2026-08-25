<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'course_id' => 'required|integer|exists:master_course,id',
            'batch_id' => 'required|integer|exists:master_batch,id',
            'semester' => 'required|string',
            'subject' => 'required|string|max:255',
            'unit' => 'nullable|string|max:255',
            'date' => 'required|date',
        ];
    }

    public function messages(): array
    {
        return [
            'course_id.required' => 'The Course is required.',
            'course_id.exists' => 'The selected Course is invalid.',
            'batch_id.required' => 'The Batch is required.',
            'batch_id.exists' => 'The selected Batch is invalid.',
            'semester.required' => 'The Semester is required.',
            'subject.required' => 'The Subject is required.',
            'date.required' => 'The Date is required.',
        ];
    }
}
