<?php

namespace App\Models\Master;

use App\Models\Master\MasterBatch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterShift extends Model
{
    use SoftDeletes;

	public $table = 'master_shift';

	protected $fillable = [
        'course_id',
        'batch_id',
        'class_id',
        'from_time',
        'to_time',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function course()
    {
        return $this->belongsTo(MasterCourse::class, 'course_id', 'id');
    }

    public function batch()
    {
        return $this->belongsTo(MasterBatch::class, 'batch_id', 'id');
    }

    public function class()
    {
        return $this->belongsTo(MasterClass::class, 'class_id', 'id');
    }
}
