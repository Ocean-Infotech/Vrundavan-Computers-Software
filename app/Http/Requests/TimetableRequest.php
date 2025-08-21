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
                'string'
            ],
            'semester' => [
                'required',
                'string'
            ],
            'attachment' => [
                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png,doc,docx',
                'max:2048'
            ],
        ];
    }
}
