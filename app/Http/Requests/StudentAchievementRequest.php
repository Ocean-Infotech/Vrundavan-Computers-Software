<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StudentAchievementRequest extends FormRequest
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
    public function rules(): array
    {
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            return [
                'student_ids' => 'sometimes|array',
                'student_ids.*' => 'nullable|exists:admission,id',
                'admission_id' => 'sometimes|nullable|exists:admission,id',
                'events' => 'sometimes|array',
                'event_id' => 'sometimes|nullable|exists:master_events,id',
                'rank' => 'sometimes|nullable|string|max:255',
                'date' => 'nullable|date',
                'remark' => 'nullable|string',
            ];
        }

        return [
            'student_ids' => 'sometimes|array',
            'events' => 'sometimes|array',
            'date' => 'nullable|date',
            'achievements' => 'sometimes|array',
        ];
    }
}
