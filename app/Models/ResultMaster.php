<?php

namespace App\Models;

use App\Models\Master\MasterCourse;
use App\Models\Master\MasterBatch;
use App\Models\Master\MasterClass;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ResultMaster extends Model
{
    use SoftDeletes;

    protected $table = 'result_masters';

    protected $fillable = [
        'course_id',
        'batch_id',
        'class_id',
        'semester',
        'total_marks',
        'created_by',
        'updated_by',
    ];

    public function course()
    {
        return $this->belongsTo(MasterCourse::class, 'course_id');
    }

    public function batch()
    {
        return $this->belongsTo(MasterBatch::class, 'batch_id');
    }

    public function class()
    {
        return $this->belongsTo(MasterClass::class, 'class_id');
    }
}
