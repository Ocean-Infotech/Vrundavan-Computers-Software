<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Admission extends Model
{
    use SoftDeletes;

    public $table = 'admission';

    protected $fillable = [
        'aadhar_card_no',
        'first_name',
        'last_name',
        'father_name',
        'mother_name',
        'temporary_address',
        'permanent_address',
        'mobile_no',
        'parent_mobile_no',
        'other_mobile_no',
        'whatsapp_no',
        'cast',
        'occupation',
        'date_of_birth',
        'email_address',
        'gender',
        'category',
        'enrolment_no',
        'spid',
        'apaar_id_abc_id',
        'status',
        'created_by',
        'updated_by',
    ];
}
