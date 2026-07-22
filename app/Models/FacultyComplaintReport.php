<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FacultyComplaintReport extends Model
{
    use SoftDeletes;

    protected $table = 'faculty_complaint_report';

    protected $fillable = [
        'admission_id',
        'gr_no',
        'date',
        'complaint',
        'faculty_id',
    ];

    public function admission()
    {
        return $this->belongsTo(Admission::class, 'admission_id');
    }

    public function faculty()
    {
        return $this->belongsTo(User::class, 'faculty_id');
    }
}
