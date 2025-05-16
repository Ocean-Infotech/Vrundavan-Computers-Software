<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Master\MasterCourse;
use App\Models\Master\MasterBatch;

class CourseRegister extends Model
{
    use SoftDeletes;

	public $table = 'attedance';

	protected $fillable = [
        'admission_id',
        'course_id',
        'batch_id',
        'fee',
        'date',
        'note',
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
