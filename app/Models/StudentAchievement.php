<?php

namespace App\Models;

use App\Models\Master\MasterEvent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentAchievement extends Model
{
    use SoftDeletes;

    protected $table = 'student_achievements';

    protected $fillable = [
        'admission_id',
        'gr_no',
        'event_id',
        'rank',
        'date',
        'remark',
        'created_by',
        'updated_by',
    ];

    public function admission()
    {
        return $this->belongsTo(Admission::class, 'admission_id');
    }

    public function event()
    {
        return $this->belongsTo(MasterEvent::class, 'event_id');
    }
}
