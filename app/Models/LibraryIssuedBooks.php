<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LibraryIssuedBooks extends Model
{
    use SoftDeletes;

	public $table = 'library_book_master';

	protected $fillable = [
        'book_id',
        'library_book_no',
        'book_name',
        'student_id',
        'issued_date',
        'issued_by',
        'return_date',
        'return_by',
        'status',
        'created_by',
        'updated_by',
        'deleted_by'
    ];
}
