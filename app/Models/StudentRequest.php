<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentRequest extends Model
{
    public $table = 'requests';
    use SoftDeletes;
    public static function getSubjects()
    {
        return [
            'Leave Request',
            'Change of Address',
            'Complaint',
            'Other'
        ];
    }
    protected $fillable = [
        'subject',
        'detail',
        'student_id',
        'created_by',
        'updated_by',
        'deleted_by'
    ];
}
