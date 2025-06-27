<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApiToken extends Model
{
    use SoftDeletes;

    public $table = 'api_tokens';

    protected $fillable = [
        'api_token',
        'created_by',
        'updated_by',
        'deleted_by',
    ];
}
