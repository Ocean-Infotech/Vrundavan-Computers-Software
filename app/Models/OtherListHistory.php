<?php

namespace App\Models;

use App\Models\Master\MasterBatch;
use App\Models\Master\MasterCourse;
use Illuminate\Database\Eloquent\Model;

class OtherListHistory extends Model
{
    protected $table = 'other_list_histories';

    protected $fillable = [
        'module_code',
        'page_heading',
        'course_id',
        'batch_id',
        'semester_id',
        'action_type',
        'student_count',
        'created_by',
    ];

    public function course()
    {
        return $this->belongsTo(MasterCourse::class, 'course_id');
    }

    public function batch()
    {
        return $this->belongsTo(MasterBatch::class, 'batch_id');
    }
}
