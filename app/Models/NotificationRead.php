<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NotificationRead extends Model
{
    use HasFactory;

    protected $table = 'notification_reads';

    protected $fillable = [
        'notification_id',
        'student_id',
        'read_at',
    ];

    public function notification()
    {
        return $this->belongsTo(NotificationSetting::class, 'notification_id');
    }

    public function student()
    {
        return $this->belongsTo(Admission::class, 'student_id');
    }
}
