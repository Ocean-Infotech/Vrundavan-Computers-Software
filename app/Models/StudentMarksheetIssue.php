<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentMarksheetIssue extends Model
{
    protected $table = 'student_marksheet_issues';

    protected $fillable = [
        'admission_id',
        'gr_no',
        'date',
        'series',
        'note',
        'semester',
        'created_by',
        'updated_by',
    ];

    public function admission()
    {
        return $this->belongsTo(Admission::class, 'admission_id');
    }
}