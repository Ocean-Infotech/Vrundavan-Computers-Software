<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceBinding extends Model
{
    protected $fillable = [
        'user_id',
        'hardware_id',
        'pc_name',
        'device_name',
        'bios_serial',
        'motherboard_serial',
        'cpu_id',
        'raw_hardware_string',
        'status',
        'latitude',
        'longitude',
        'approved_by',
        'approved_at',
        'last_login_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'last_login_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
