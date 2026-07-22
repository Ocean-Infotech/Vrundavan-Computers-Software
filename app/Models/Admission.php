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
        'gr_no',
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

    public function marksheetIssues()
    {
        return $this->hasMany(StudentMarksheetIssue::class, 'admission_id');
    }

    public function getMarksheetSemestersAttribute()
    {
        return $this->marksheetIssues()->pluck('semester')->toArray();
    }

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

    /**
     * Machine punch biometric_id → admission_id
     * Example: biometric_id 232 → VORA SHRUTI (admission id 61)
     */
    public static function findByBiometricId($biometricId): ?self
    {
        $biometricId = trim((string) $biometricId);

        if ($biometricId === '') {
            return null;
        }

        return static::query()
            ->where('biometric_id', $biometricId)
            ->first();
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

    public function getProfilePicUrlAttribute()
    {
        # profile_pic_url
        if (!$this?->profile_pic) {
            return "";
        }
        $profilePic = $this?->profile_pic;
        if (strpos($profilePic, 'http') === 0) {
            return $profilePic;
        }
        return asset($profilePic);
    }
}
