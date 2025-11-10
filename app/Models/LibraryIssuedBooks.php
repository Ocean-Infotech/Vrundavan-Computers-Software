<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LibraryIssuedBooks extends Model
{
    use SoftDeletes;

    public $table = 'library_issued_books';

    protected $fillable = [
        'book_id',
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
    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function student()
    {
        return $this->belongsTo(Admission::class, 'student_id');
    }
    public function book()
    {
        return $this->belongsTo(LibraryMasterBook::class, 'book_id');
    }
    public function issuedByUser()
    {
        return $this->belongsTo(\App\Models\User::class, 'issued_by');
    }

    public function returnedByUser()
    {
        return $this->belongsTo(\App\Models\User::class, 'return_by');
    }
}
