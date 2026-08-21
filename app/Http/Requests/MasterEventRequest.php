<?php

namespace App\Http\Requests;

use App\Models\Master\MasterEvent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MasterEventRequest extends FormRequest
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
        $id = $this->route('event') ?? 0;

        return [
            'name' => [
                'required',
                'max:255',
                Rule::unique((new MasterEvent())->getTable(), 'name')->ignore($id),
            ],
        ];
    }
}
