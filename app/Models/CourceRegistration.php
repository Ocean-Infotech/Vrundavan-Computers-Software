<?php

namespace App\Models;

use App\Models\Master\MasterCourse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CourceRegistration extends Model
{
    use SoftDeletes;

    public $table = 'cource_registration';

    protected $fillable = [
        'register_id',
        'course_id',
        'fee',
        'date',
        'note',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
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
}
