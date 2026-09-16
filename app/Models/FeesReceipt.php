<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeesReceipt extends Model
{
    use SoftDeletes;

    protected $table = 'fees_receipts';

    protected $fillable = [
        'student_id',
        'semester',
        'receipt_no',
        'deleted_by',
    ];
}

