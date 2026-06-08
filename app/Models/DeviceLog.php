<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceLog extends Model
{
    public $timestamps = false; // Only created_at column

    protected $fillable = [
        'user_id',
        'hardware_id',
        'ip_address',
        'latitude',
        'longitude',
        'user_agent',
        'login_status',
        'remarks',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
