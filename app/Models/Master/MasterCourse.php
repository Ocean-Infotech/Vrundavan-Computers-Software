<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterCourse extends Model
{
    use SoftDeletes;

	public $table = 'master_course';

	protected $fillable = [
        'course_name',
        'course_fees',
        'course_year',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];
}
