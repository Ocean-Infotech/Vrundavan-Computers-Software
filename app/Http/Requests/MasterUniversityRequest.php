<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Master\MasterUniversity;

class MasterUniversityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Route model binding parameter name → university
        $id = $this->route('university') ?? 0;

        return [
            'name' => [
                'required',
                'max:255',
                Rule::unique((new MasterUniversity())->getTable(), 'name')
                    ->ignore($id)
                    ->whereNull('deleted_at'),
            ],

        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'University name is required.',
            'name.unique'   => 'This university already exists.',
        ];
    }
}
