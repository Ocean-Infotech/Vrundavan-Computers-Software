<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContactUsBranch extends Model
{
    use SoftDeletes;

    protected $table = 'contact_us_branches';

    protected $fillable = [
        'name',
        'address',
        'email',
        'sort_order',
        'status',
        'created_by',
        'updated_by',
    ];
}
