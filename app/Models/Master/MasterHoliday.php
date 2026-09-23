<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterHoliday extends Model
{
    use SoftDeletes;

    protected $table = 'master_holidays';

    protected $fillable = [
        'name',
        'from_date',
        'to_date',
        'type',
        'description',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'from_date' => 'date',
        'to_date' => 'date',
    ];
}
