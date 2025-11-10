<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StudentDeviceToken extends Model
{
    use HasFactory;

    protected $table = 'student_device_tokens';

    protected $fillable = [
        'student_id',
        'device_token',
        'device_details',
        'created_by',
        'updated_by',
        'deleted_by',
    ];
}
