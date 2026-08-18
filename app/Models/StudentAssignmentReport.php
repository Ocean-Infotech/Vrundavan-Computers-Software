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
        'created_by',
        'updated_by',
    ];

    public function assignment()
    {
        return $this->belongsTo(Assignment::class, 'assignment_id', 'id');
    }

    public function student()
    {
        return $this->belongsTo(Admission::class, 'gr_no');
    }

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedByUser()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
