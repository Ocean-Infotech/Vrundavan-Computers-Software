<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Master\MasterCourse;
use App\Models\Master\MasterBatch;

class Attedance extends Model
{
    use SoftDeletes;

    public $table = 'attedance';

    protected $fillable = [
        'biometric_id',
        'admission_id',
        'course_id',
        'batch_id',
        'date',
        'in_time',
        'out_time',
        'leave',
        'DeviceKey',
        'DeviceName',
        'UserId',
        'EmpCode',
        'UserName',
        'IOTime',
        'IOMode',
        'VerifyMode',
        'WorkCode',
        'ImagePath',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_at',
    ];

    public function admission()
    {
        return $this->belongsTo(Admission::class, 'admission_id', 'id');
    }

    public function course()
    {
        return $this->belongsTo(MasterCourse::class, 'course_id', 'id');
    }

    public function batch()
    {
        return $this->belongsTo(MasterBatch::class, 'batch_id', 'id');
    }
}
