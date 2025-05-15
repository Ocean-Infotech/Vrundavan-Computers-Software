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
        'registrar_id',
        'course_id',
        'fees',
        'date',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];
    public function course()
    {
        return $this->belongsTo(MasterCourse::class, 'course_id');
    }
}
