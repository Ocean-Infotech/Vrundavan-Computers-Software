<?php

namespace App\Models;

use App\Models\Master\MasterCourse;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class Admission extends Authenticatable
{
    use SoftDeletes;
    use HasRoles;
    use HasApiTokens, Notifiable;

    public $table = 'admission';

    protected $fillable = [
        'biometric_id',
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
        'profile_pic',
        'category',
        'enrolment_no',
        'spid',
        'apaar_id_abc_id',
        'udise',
        'gdrivefolderurl',
        'gdrivefolderid',
        'gdrivefoldername',
        'status',
        'created_by',
        'updated_by',
    ];

    // Admission.php
    // public function courseRegistrations()
    // {
    //     return $this->hasMany(CourceRegistration::class, 'register_id');
    // }
    // app/Models/Admission.php
    public function feesCollections()
    {
        return $this->hasMany(FeesCollection::class);
    }
    public function courses()
    {
        return $this->hasMany(CourceRegistration::class, 'register_id', 'id');
    }


    public function courceRegistration()
    {
        return $this->hasOne(CourceRegistration::class, 'admission_id');
    }

    public function course()
    {
        return $this->belongsTo(MasterCourse::class, 'course_id');
    }
    public function educationDetails()
    {
        return $this->hasMany(EducationDetails::class, 'admission_id');
    }
    public function testMarks()
    {
        return $this->hasMany(\App\Models\StudentTestMark::class, 'register_id', 'id');
    }

    public function getFullNameAttribute()
    {
        #full_name
        $first = $this?->first_name ?? '';
        $father = $this?->father_name ?? '';
        $last = $this?->last_name ?? '';
        return trim("{$last} {$father} {$first}");
    }
}
