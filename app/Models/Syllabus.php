<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Syllabus extends Model
{
     use SoftDeletes;

	public $table = 'syllabus';

	protected $fillable = [
        'title',
        'subject',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];
}
