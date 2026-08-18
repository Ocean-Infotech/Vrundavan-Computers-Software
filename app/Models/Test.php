<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Master\MasterCourse;
use App\Models\Master\MasterBatch;

class Test extends Model
{
    use SoftDeletes;

	public $table = 'test';

	protected $fillable = [
        'course_id',
        'batch_id',
        'semester',
        'subject_name',
        'test_type',
        'unit_name',
        'mark',
        'date',
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

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedByUser()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
