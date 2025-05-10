<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WithdrawRequest extends Model
{
    use SoftDeletes;

    protected $table = 'withdraw_requests';
    protected $appends = ['user_name'];

    public static $folderPath = "uploads/withdraw_request/";

    protected $fillable = [
        'user_id',
        'request_points',
        'request_amount',
        'withdraw_request_date',
        'image',
        'reason',
        'withdraw_request_status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function UsserWithdrawRequests()
    {
        return $this->hasMany(User::class, 'user_id');
    }

    public function getUserNameAttribute()
    {
        # user_name
        if ($this->user_id) {
            $getUser = User::find($this->user_id);
            if ($getUser && isset($getUser->name)) {
                return $getUser->name;
            }
        }
        return "";
    }
}
