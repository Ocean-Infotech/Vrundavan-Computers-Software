<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SendOTP extends Model
{

    public $table = 'send_otp';

    protected $fillable = [
        'model',
        'model_id',
        'send_to',
        'verified_content',
        'expire_time',
        'sms_type',
        'sms',
        'verified_at',
        'status',
        'created_from',
    ];
}
