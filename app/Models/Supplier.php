<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use SoftDeletes;

    protected $table = 'suppliers';

    protected $fillable = [
        'name',
        'category',
        'city',
        'mobile',
        'created_by',
        'updated_by',
        'deleted_by',
    ];
}
