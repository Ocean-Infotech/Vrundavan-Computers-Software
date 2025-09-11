<?php

namespace App\Models\Master;


use Illuminate\Database\Eloquent\Model;

class MasterFeeDetail extends Model
{
    public $table = 'fee_details';

    protected $fillable = [
        'fee_name',
        'amount',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];
}
