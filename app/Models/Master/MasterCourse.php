<?php

namespace App\Models\Master;

use App\Models\StudentRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterCourse extends Model
{
    use SoftDeletes;

    public $table = 'master_course';

    protected $fillable = [
        'biometric_id',
        'course_name',
        'course_fees',
        'course_year',
        'semester',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public static function course_year_type()
    {
        return [
            'year' => 'Year',
            'month' => 'Month',
            'day' => 'Day',
        ];
    }
    public function batches()
    {
        return $this->hasMany(MasterBatch::class, 'course_id', 'id');
        // 'course_id' → the actual foreign key column in master_batch table
        // 'id' → the local primary key
    }
    public function studentRequests()
    {
        return $this->hasMany(StudentRequest::class, 'course_id', 'id');
    }
}
