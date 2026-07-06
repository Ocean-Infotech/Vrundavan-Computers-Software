<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IssueCertificateHistory extends Model
{
    protected $table = 'issue_certificate_histories';

    protected $fillable = [
        'certificate_type',
        'register_id',
        'student_name',
        'issue_date',
        'academic_year',
        'semester_start',
        'semester_end',
        'created_by',
    ];

    public function admission()
    {
        return $this->belongsTo(Admission::class, 'register_id');
    }
}
