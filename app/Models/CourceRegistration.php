<?php

namespace App\Models;

use App\Models\Master\MasterBatch;
use App\Models\Master\MasterClass;
use App\Models\Master\MasterCourse;
use App\Models\Master\MasterShift;
use App\Models\Master\MasterUniversity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CourceRegistration extends Model
{
    use SoftDeletes;

    public $table = 'cource_registration';

    protected $fillable = [
        'register_id',
        'course_id',
        'batch_id',
        'class_id',
        'shift_id',
        'admission_id',
        'university',
        'department',
        'fee',
        'date',
        'note',
        'is_lateral_entry',
        'joining_semester',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public static $universities = [
        'Vrundavan Computers',
        'BKNMU',
    ];
    public function admission()
    {
        return $this->belongsTo(Admission::class, 'register_id', 'id');
    }

    public function attedance()
    {
        return $this->belongsTo(Attedance::class);
    }

    public function course()
    {
        return $this->belongsTo(MasterCourse::class, 'course_id');
    }

  public function batch()
{
    return $this->belongsTo(MasterBatch::class, 'batch_id', 'id');
}


    public function class()
    {
        return $this->belongsTo(MasterClass::class, 'class_id');
    }

    public function shift()
    {
        return $this->belongsTo(MasterShift::class, 'shift_id');
    }

    // public function feescollection()
    // {
    //     return $this->belongsTo(FeesCollection::class, 'register_id', 'student_id');
    // }
    public function feescollection()
    {
        return $this->hasMany(FeesCollection::class, 'student_id', 'register_id');
    }


    public function semester()
    {
        return $this->belongsTo(FeesCollection::class, 'year_semester'); // adjust name/key as needed
    }
    public function student()
    {
        return $this->belongsTo(Admission::class, 'admission_id', 'id');
    }
    public function registrations()
    {
        return $this->hasMany(CourceRegistration::class, 'admission_id', 'id');
    }
       public function university()
    {
        return $this->hasMany(MasterUniversity::class, 'university', 'id');
    }
    public static function calculateTotalFee($registration)
    {
        return (float) $registration->fee;
    }
}
