<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContactUsNumber extends Model
{
    use SoftDeletes;

    protected $table = 'contact_us_numbers';

    protected $fillable = [
        'label',
        'mobile',
        'status',
        'sort_order',
        'created_by',
        'updated_by',
    ];
}
