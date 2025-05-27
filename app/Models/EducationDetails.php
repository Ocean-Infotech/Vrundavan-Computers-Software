<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EducationDetails extends Model
{
    use SoftDeletes;

    public $table = 'education_details';

    protected $fillable = [
        'admission_id',
        'education',
        'percentage_cgpa',
        'seat_no_nrollment_no',
        'board_university',
        'passing_year',
        'school_name_college_name',
        'created_by',
        'updated_by',
    ];
}
