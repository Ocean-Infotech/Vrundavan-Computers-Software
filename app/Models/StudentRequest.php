<?php

namespace App\Models;

use App\Models\Master\MasterSubject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentRequest extends Model
{
    public $table = 'requests';
    use SoftDeletes;

    protected $fillable = [
        'subject',
        'subject_id',
        'detail',
        'student_id',
        'status',
        'response',
        'created_by',
        'updated_by',
        'deleted_by'
    ];
    public function subject()
    {
        return $this->belongsTo(MasterSubject::class, 'subject_id');
    }
    public function subjectRelation()
    {
        return $this->belongsTo(MasterSubject::class, 'subject_id');
    }
    public function student()
    {
        return $this->belongsTo(Admission::class, 'student_id', 'id');
    }
}
