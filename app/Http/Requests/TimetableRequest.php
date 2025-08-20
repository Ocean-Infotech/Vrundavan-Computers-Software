<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\MasterCourse;
use App\Models\MasterBatch;
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

        return [
          
            'batch' => [
                'required',
            ],
            'class' => [
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
                'mimes:pdf,jpg,jpeg,png',
                'max:2048'
            ],
        ];
    }
}
