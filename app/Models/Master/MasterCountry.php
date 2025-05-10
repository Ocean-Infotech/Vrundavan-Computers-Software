<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterCountry extends Model
{
    use SoftDeletes;

	public $table = 'master_countries';

	protected $fillable = [
        'name',
        'short_name',
        'code',
        'latitude',
        'longitude',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];
}
