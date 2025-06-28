<?php

namespace App\Models;

use App\Models\Master\MasterCourse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeesCollection extends Model
{
    use SoftDeletes;

 protected $fillable = [
    'student_id',
    'admission_id',
    'course_id',
    'student_name',
    'year_semester',
    'date',
    'fees',
    'mode',
    'upi_id',
    'cheque_no',
    'return_reason',
    'password',
    'status',
];

    public function course()
    {
        return $this->belongsTo(MasterCourse::class, 'course_id');
    }
    public function admission()
    {
        return $this->belongsTo(Admission::class, 'student_id', 'id');
    }
}
