<?php

namespace App\Http\Requests;

use App\Models\NotificationSetting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


class NotificationSettingRequest extends FormRequest
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
    public function rules()
    {
        $id = $this->route('notification_setting') ?? 0; // route param name table par depend kare
        // dd($id);
        return [
            'title' => [
                'required',
                'string',
                'max:255',
            ],
            'body' => 'required|string',
            'course_id' => 'required|string',
            'student_id' => 'required|array',
            'student_id.*' => 'integer|exists:admission,id',
        ];
    }
}
