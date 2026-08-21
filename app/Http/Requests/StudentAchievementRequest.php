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
                'admission_id' => 'required|exists:admission,id',
                'event_id' => 'required|exists:master_events,id',
                'rank' => 'required|string|max:255',
                'date' => 'required|date',
                'remark' => 'nullable|string',
            ];
        }

        return [
            'achievements' => 'required|array',
            'achievements.*.admission_id' => 'required|exists:admission,id',
            'achievements.*.event_id' => 'required|exists:master_events,id',
            'achievements.*.rank' => 'required|string|max:255',
            'achievements.*.date' => 'required|date',
            'achievements.*.remark' => 'nullable|string',
        ];
    }
}
