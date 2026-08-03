<?php

namespace App\Models;

use App\Models\Master\MasterCourse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class NotificationSetting extends Model
{
    use HasFactory;

    protected $table = 'notification_settings';

    protected $fillable = [
        'title',
        'body',
        'course_id',
        'batch_id',
        'class_id',
        'student_id',
        'image',
        'pdf',
        'created_by',
        'updated_by',
    ];
    public function student()
    {
        return $this->belongsTo(Admission::class, 'admission_id');
    }
    public function course()
    {
        return $this->belongsTo(MasterCourse::class, 'course_id', 'id');
    }
    public function batch()
    {
        return $this->belongsTo(\App\Models\Master\MasterBatch::class, 'batch_id', 'id');
    }
    public function class()
    {
        return $this->belongsTo(\App\Models\Master\MasterClass::class, 'class_id', 'id');
    }
    public function getStudentNamesAttribute()
    {
        if (!$this->student_id) return '-';

        $ids = explode(',', $this->student_id);

        return \App\Models\Admission::whereIn('id', $ids)
            ->get()
            ->map(fn($s) => $s->first_name . ' ' . $s->last_name)
            ->implode(', ');
    }
}
