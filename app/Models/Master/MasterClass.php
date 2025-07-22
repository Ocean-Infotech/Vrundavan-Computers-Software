<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterClass extends Model
{
    use SoftDeletes;

	public $table = 'master_class';

	protected $fillable = [
        'class',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];
}
