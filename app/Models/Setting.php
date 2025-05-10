<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Setting extends Model
{
    use SoftDeletes;

    protected $table = 'settings';

    protected $fillable = [
        'key',
        'value',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function getValueAttribute($value){

        if($value){
            if(gettype(json_decode($value)) == "object"){
                return json_decode($value);
            }
        }
        return $value;
    }
}
