<?php

namespace App\Models\Master;

use App\Models\CourceRegistration;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterBatch extends Model
{
    use SoftDeletes;

    public $table = 'master_batch';

    protected $fillable = [
        'course_id',
        'batch_name',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function course()
    {
        return $this->belongsTo(MasterCourse::class, 'course_id', 'id');
    }
    public function students()
    {
        return $this->hasMany(CourceRegistration::class, 'batch_id', 'id');
    }
}
