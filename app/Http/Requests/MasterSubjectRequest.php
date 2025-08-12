<?php

namespace App\Http\Requests;

use App\Models\Master\MasterSubject;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MasterSubjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Change to your authorization logic if needed
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules()
    {
        $id = $this->route('subject') ?? 0;

        return [
            'name' => [
                'required',
                'max:255',
                Rule::unique((new MasterSubject())->getTable(), 'name')->ignore($id),
            ],

        ];
    }
}
