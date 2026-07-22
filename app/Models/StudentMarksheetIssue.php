<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentMarksheetIssue extends Model
{
    public $table = 'student_marksheet_issues';

    protected $fillable = [
        'admission_id',
        'semester',
        'is_issued',
        'created_by',
        'updated_by',
    ];

    public function admission()
    {
        return $this->belongsTo(Admission::class, 'admission_id');
    }
}
