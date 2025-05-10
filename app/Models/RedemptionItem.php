<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RedemptionItem extends Model
{
    use SoftDeletes;

    protected $table = 'redemption_items';

    public $appends = ['scanned_user'];

    protected $fillable = [
        'redemption_id',
        'redemption_code_label',
        'collect_points',
        'scanned_user_id',
        'scanned_date',
        'customer_id',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function parent_redeption(){
        return $this->belongsTo(Redemption::class, 'redemption_id', 'id');
    }

    public function getScannedUserAttribute()
    {
        # scanned_user
        if ($this->scanned_user_id) {
            return User::where('id', $this->scanned_user_id)->first();
        }
        return null;
    }
}
