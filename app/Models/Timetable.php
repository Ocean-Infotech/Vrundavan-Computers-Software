<?php

namespace App\Models;

use App\Models\Master\MasterBatch;
use App\Models\Master\MasterClass;
use App\Models\Master\MasterCourse;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Timetable extends Model
{

  use SoftDeletes;
    use HasFactory;


    public $table = 'timetables';

    protected $fillable = [
        'course_id',
        'batch_id',
        'class_id',
        'semester',
        'attachment',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    // Relation to batch
    public function batch()
    {
        return $this->belongsTo(MasterBatch::class, 'batch_id', 'id');
    }

    // Relation to course
    public function course()
    {
        return $this->belongsTo(MasterCourse::class, 'course_id', 'id');
    }
    public function class()
    {
        return $this->belongsTo(MasterClass::class, 'class_id');
    }
}
