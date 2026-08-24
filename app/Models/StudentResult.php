<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentResult extends Model
{
    use SoftDeletes;

    protected $table = 'student_results';

    protected $fillable = [
        'admission_id',
        'result_id',
        'semester',
        'obtained_marks',
        'percentage',
        'created_by',
        'updated_by',
    ];

    public function admission()
    {
        return $this->belongsTo(Admission::class, 'admission_id');
    }

    public function resultMaster()
    {
        return $this->belongsTo(ResultMaster::class, 'result_id');
    }
}
