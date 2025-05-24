<?php

namespace App\Http\Requests;

use App\Models\Master\MasterBatch;
use App\Models\Master\MasterCourse;
use App\Models\master\MasterShift;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MasterShiftRequest extends FormRequest
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
        $id = $request->route('shift') ?? 0;
        $rules = [
            'course_id' => [
                'required',
                Rule::exists((new MasterCourse())->getTable(), 'id')
            ],

            'batch_id' => [
                'required',
                Rule::exists((new MasterBatch())->getTable(), 'id')
            ],

            'class_id' => [
                'required',
                //Rule::exists((new MasterBatch())->getTable(), 'id')
            ],

            // 'name' => 'required|string|max:255|unique:' . (new MasterCity())->getTable() . ',name',
            'from_time' => [
                'required',
                Rule::unique((new MasterShift())->getTable())
                    ->where(fn($q) => $q->where('class_id', $request->class_id))
                    ->ignore($id),
                function ($attribute, $value, $fail) use ($request, $id) {

                    // Normalize value
                    $value = Carbon::parse($value)->format('H:i:s');

                    // Retrieve conflicting shift (if any)
                    $conflict = MasterShift::where('class_id', $request->class_id)
                        ->when($id, fn($q) => $q->where('id', '!=', $id))
                        ->whereTime('to_time', '>=', $value)
                        ->orderBy('to_time')
                        ->first();

                    if ($conflict) {
                        $start = Carbon::parse($conflict->from_time)->format('h:i A');
                        $end   = Carbon::parse($conflict->to_time)->format('h:i A');
                        $fail("The shift existing from $start to $end.");
                    }
                },
            ],

            'to_time' => ['required', 'after:from_time'],
        ];
        // dd("L-33", $rules, $id, $request->all(), $request->route('master-city'), $request->route()->parameters());
        return $rules;
    }

    public function messages()
    {
        return [
            'course_id.required' => 'The Course Name is required.',
            'batch_id.required' => 'The Batch Name is required.',
            'class_id.required' => 'The Batch Name is required.',
            'from_time.required' => 'The Batch Name is required.',
            'to_time.required' => 'The Batch Name is required.',

        ];
    }
}
