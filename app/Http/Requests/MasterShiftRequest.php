<?php

namespace App\Http\Requests;

use App\Models\Master\MasterBatch;
use App\Models\Master\MasterCourse;
use App\Models\Master\MasterShift;
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
                Rule::exists((new MasterCourse())->getTable(), 'id'),
            ],
            'batch_id' => [
                'required',
                Rule::exists((new MasterBatch())->getTable(), 'id'),
            ],
            'class_id' => ['required'],

            'from_time' => [
                'required',
                Rule::unique((new MasterShift())->getTable())
                    ->where(function ($q) use ($request) {
                        return $q->where('class_id', $request->class_id)
                            ->where('batch_id', $request->batch_id)
                            ->where('course_id', $request->course_id);
                    })
                    ->ignore($id),

                function ($attribute, $value, $fail) use ($request, $id) {
                    $fromTime = Carbon::parse($value)->format('H:i:s');
                    $toTime = Carbon::parse($request->to_time)->format('H:i:s');

                    $conflict = MasterShift::where('class_id', $request->class_id)
                        ->where('batch_id', $request->batch_id)
                        ->where('course_id', $request->course_id)
                        ->when($id, fn($q) => $q->where('id', '!=', $id))
                        ->where(function ($query) use ($fromTime, $toTime) {
                            $query->where(function ($q) use ($fromTime, $toTime) {
                                $q->where('from_time', '<', $toTime)
                                ->where('to_time', '>', $fromTime);
                            });
                        })
                        ->first();

                    if ($conflict) {
                        $start = Carbon::parse($conflict->from_time)->format('h:i A');
                        $end = Carbon::parse($conflict->to_time)->format('h:i A');
                        $fail("Shift already exists from $start to $end.");
                    }
                }
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
