<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterEvent extends Model
{
    use SoftDeletes;

    protected $table = 'master_events';

    protected $fillable = [
        'name',
        'status',
        'created_by',
        'updated_by',
    ];
}
