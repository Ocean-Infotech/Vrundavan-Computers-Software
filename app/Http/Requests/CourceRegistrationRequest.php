<?php

namespace App\Http\Requests;

use App\Models\CourceRegistration;
use App\Models\Master\MasterBatch;
use App\Models\master\MasterClass;
use App\Models\master\MasterShift;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CourceRegistrationRequest extends FormRequest
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
        $id = $request->route('cource_registration') ?? 0;
        $rules = [
            'register_id' => 'required|exists:admission,id',
            
            'course_id' => [
                'required',
                Rule::unique((new CourceRegistration())->getTable())->where(function ($query) use ($request) {
                    return $query->where('register_id', $request->register_id);
                })->ignore($id),
            ],
            'batch_id' => [
                'required',
                Rule::exists((new MasterBatch())->getTable(), 'id')
            ],
            'class_id' => [
                'required',
                Rule::exists((new MasterClass())->getTable(), 'id')
            ],
            'shift_id' => [
                'required',
                Rule::exists((new MasterShift())->getTable(), 'id')
            ],
            'fee' => 'required|numeric|min:0',
            'date' => 'required|date',
            'note' => 'nullable|string',
        ];
        return $rules;
    }
}
