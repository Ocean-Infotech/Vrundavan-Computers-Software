<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeesReceipt extends Model
{
     protected $table = 'fees_receipts';

     protected $fillable = [
        'student_id',
        'semester',
        'receipt_no',
    ];  
}
