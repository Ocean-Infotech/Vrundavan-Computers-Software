<?php

namespace App\Http\Requests;

use App\Models\Master\MasterDepartment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;


class MasterDepartmentRequest extends FormRequest
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
    public function rules(Request $request)
    {
        $id = $request  ->route('department') ?? 0;
        // dd($id);

        return [
            'name' => [
                'required',
                'max:255',
                Rule::unique((new MasterDepartment())->getTable(), 'name')->ignore($id),
            ],

        ];
    }
}
