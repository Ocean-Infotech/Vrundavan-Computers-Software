<?php

namespace App\Http\Requests;

use App\Models\Master\MasterCourse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Master\MasterBatch;
use App\Models\TimeTable;

class TimeTableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('timetable') ?? 0;
        // dd($id);
        return [
            'course_id' => [
                'required',
                'integer',
                Rule::exists((new MasterCourse())->getTable(), 'id')
            ],
            'batch_id' => [
                'required',
                'integer',
                Rule::exists((new MasterBatch())->getTable(), 'id')
            ],
            'class_id' => [
                'required',
                'string',
                 Rule::unique((new TimeTable())->getTable())
                    ->where(function ($query) {
                        return $query->where('course_id', $this->course_id)
                            ->where('batch_id', $this->batch_id);
                    })
                    ->ignore($id)
            ],
             'attachment' => [
                'nullable',
                // 'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:5120'
            ],
            // 'semester' => [
            //     'required',
            //     'string',
            //     Rule::unique((new TimeTable())->getTable())
            //         ->where(function ($query) {
            //             return $query->where('course_id', $this->course_id)
            //                 ->where('batch_id', $this->batch_id)
            //                 ->where('class_id', $this->class_id);
            //         })
            //         ->ignore($id)
            // ],

        ];
    }
}
