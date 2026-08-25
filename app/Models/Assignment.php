<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Assignment extends Model
{
    use SoftDeletes;

    public $table = 'assignments';

    protected $fillable = [
        'course_id',
        'batch_id',
        'semester',
        'subject',
        'unit',
        'date',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function course()
    {
        return $this->belongsTo(\App\Models\Master\MasterCourse::class, 'course_id', 'id');
    }

    public function batch()
    {
        return $this->belongsTo(\App\Models\Master\MasterBatch::class, 'batch_id', 'id');
    }
}
