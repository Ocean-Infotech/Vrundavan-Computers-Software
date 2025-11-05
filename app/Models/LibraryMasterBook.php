<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LibraryMasterBook extends Model
{
    use SoftDeletes;

    public $table = 'library_book_master';

    protected $fillable = [
        'book_name',
        'library_book_no',
        'author_name',
        'publisher_name',
        'total_number_of_page',
        'purchase_date',
        'price',
        'status',
        'created_by',
        'updated_by',
        'deleted_by'
    ];
    public static $status = [
        'active' => 'Active',
        'destroy' => 'Destroy',
        'not available' => 'Not Available',

    ];
    public static function getStatusOptions()
    {
        return self::$status;
    }
    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
}
