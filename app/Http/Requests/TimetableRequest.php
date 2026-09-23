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
                            ->where('batch_id', $this->batch_id)
                            ->whereNull('deleted_at');
                    })
                    ->ignore($id)
            ],
            'attachment' => [
                'nullable',
                // 'file',
                'mimes:pdf,doc,docx,txt,text,jpg,jpeg,png,webp',
                'max:10240'
            ],
        ];
    }
}
