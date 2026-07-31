<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Admission;

class StudentAssignmentReport extends Model
{
    public $table = 'assignment_reports';

    protected $fillable = [
        'assignment_id',
        'gr_no',
        'status',
        'remarks',
    ];

    public function assignment()
    {
        return $this->belongsTo(Assignment::class, 'assignment_id', 'id');
    }

    public function student()
    {
        return $this->belongsTo(Admission::class, 'gr_no');
    }
}
